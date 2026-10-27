import mq from 'mithril-query';
import extractText from 'flarum/common/utils/extractText';
import bootstrapForum from '../../bootstrap';
import { makeDiscussion } from '../../factory';
import SummaryPanel from '../../../src/forum/components/SummaryPanel';
import SummaryState from '../../../src/forum/states/SummaryState';

beforeAll(() => {
  bootstrapForum();

  (globalThis as any).s9e = { TextFormatter: { preview: (text: string, element: Element) => (element.textContent = text) } };
});

describe('SummaryPanel', () => {
  it('offers to summarize a discussion that has no summary yet', () => {
    const out = mq(SummaryPanel, { discussion: makeDiscussion({ canSummarize: true }) });

    expect(out.rootEl.querySelector('.DiscussionSummary-body .Button')?.textContent).toContain('Summarize this discussion');
    expect(out.rootEl.querySelector('.DiscussionSummary-toggle')).toBeFalsy();
  });

  it('says which model wrote the summary and from how many posts', () => {
    const out = mq(SummaryPanel, { discussion: makeDiscussion({}, {}) });
    const footer = out.rootEl.querySelector('.DiscussionSummary-footer')!;

    expect(out.rootEl.querySelector('.Post-body')?.innerHTML).toBe('<p>Caching first.</p>');
    expect(footer.textContent).toContain('AI-generated with test-model from 8 posts');
    expect(footer.textContent).not.toContain('new repl');
    expect(footer.querySelector('.Button')).toBeFalsy();
  });

  it('counts the replies a summary has not seen and offers an update to those allowed', () => {
    const out = mq(SummaryPanel, { discussion: makeDiscussion({ canSummarize: true }, { newReplyCount: 3 }) });
    const footer = out.rootEl.querySelector('.DiscussionSummary-footer')!;

    expect(footer.textContent).toContain('3 new replies since');
    expect(footer.querySelector('.Button')?.textContent).toBe('Update');
  });

  it('collapses to its heading', () => {
    const discussion = makeDiscussion({}, {});
    const out = mq(SummaryPanel, { discussion });
    const toggle = () => out.rootEl.querySelector('.DiscussionSummary-toggle')!;

    expect(toggle().getAttribute('aria-expanded')).toBe('true');

    (toggle() as HTMLElement).click();
    out.redraw();

    expect(toggle().getAttribute('aria-expanded')).toBe('false');
    expect(out.rootEl.querySelector('.DiscussionSummary-body')).toBeFalsy();
  });

  it('shows the text as it streams in and marks the region busy', () => {
    const discussion = makeDiscussion({ canSummarize: true });
    const state = SummaryState.for(discussion);
    state.generating = true;
    state.text = 'Caching came up [#2].';

    const out = mq(SummaryPanel, { discussion });
    const body = out.rootEl.querySelector('.DiscussionSummary-body')!;

    expect(body.getAttribute('aria-busy')).toBe('true');
    expect(extractText(out.rootEl.querySelector('.DiscussionSummary-preview')!.textContent!)).toBe('Caching came up #2.');
    expect(out.rootEl.querySelector('.DiscussionSummary-status')?.textContent).toContain('Writing a summary');

    state.generating = false;
    state.text = '';
  });
});
