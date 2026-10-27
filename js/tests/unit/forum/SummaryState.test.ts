import { jest } from '@jest/globals';
import app from 'flarum/forum/app';
import bootstrapForum from '../../bootstrap';
import { makeDiscussion } from '../../factory';
import SummaryState from '../../../src/forum/states/SummaryState';

beforeAll(() => bootstrapForum());

// Plays the body to the progress listeners in pieces, the way a slow connection delivers it.
function respondWith(chunks: string[], status = 200) {
  return jest.spyOn(app, 'request').mockImplementation(async (options: any) => {
    const listeners: Record<string, () => void> = {};
    const xhr: any = { status, responseText: '', addEventListener: (event: string, listener: () => void) => (listeners[event] = listener) };

    options.config(xhr);

    for (const chunk of chunks) {
      xhr.responseText += chunk;
      listeners.progress();
    }

    listeners.load();

    return options.modifyText(xhr.responseText);
  });
}

afterEach(() => jest.restoreAllMocks());

describe('SummaryState', () => {
  it('reads each line once it is complete, even when a chunk ends mid-line', async () => {
    const seen: string[] = [];
    const state = SummaryState.for(makeDiscussion({ canSummarize: true }));

    respondWith(['{"type":"text","text":"Cach', 'ing "}\n{"type":"text","text":"first."}\n', '{"type":"done"}\n']);
    jest.spyOn(app.store, 'find').mockImplementation(async () => {
      seen.push(state.text);
      return null as any;
    });

    await state.generate();

    expect(seen).toEqual(['Caching first.']);
    expect(state.generating).toBe(false);
    expect(state.text).toBe('');
  });

  it('shows the error line and keeps the discussion as it was', async () => {
    const state = SummaryState.for(makeDiscussion({ canSummarize: true }));
    const show = jest.spyOn(app.alerts, 'show');
    const find = jest.spyOn(app.store, 'find');

    respondWith(['{"type":"text","text":"Half"}\n', '{"type":"error","message":"The summary couldn\'t be written."}\n']);

    await state.generate();

    expect(show).toHaveBeenCalledWith({ type: 'error' }, "The summary couldn't be written.");
    expect(find).not.toHaveBeenCalled();
  });

  it('hands the last line to the request, which parses it as JSON', async () => {
    const request = respondWith(['{"type":"text","text":"a"}\n{"type":"done"}\n']);
    jest.spyOn(app.store, 'find').mockResolvedValue(null as any);

    await SummaryState.for(makeDiscussion({ canSummarize: true })).generate();

    expect(JSON.parse(await request.mock.results[0].value)).toEqual({ type: 'done' });
  });
});
