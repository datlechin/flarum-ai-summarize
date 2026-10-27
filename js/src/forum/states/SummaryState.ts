import app from 'flarum/forum/app';
import type Discussion from 'flarum/common/models/Discussion';

type StreamEvent = { type: 'text'; text: string } | { type: 'done' } | { type: 'error'; message: string };

const states = new WeakMap<Discussion, SummaryState>();

export default class SummaryState {
  generating = false;
  text = '';
  expanded = true;

  constructor(public discussion: Discussion) {}

  static for(discussion: Discussion): SummaryState {
    let state = states.get(discussion);

    if (!state) {
      state = new SummaryState(discussion);
      states.set(discussion, state);
    }

    return state;
  }

  async generate(): Promise<void> {
    if (this.generating) return;

    this.generating = true;
    this.expanded = true;
    this.text = '';
    m.redraw();

    let read = 0;
    let error: string | null = null;

    const receive = (xhr: XMLHttpRequest) => {
      if (xhr.status !== 200) return;

      // Anything up to the last newline is complete; whatever follows is half a line and waits for the next chunk.
      const end = xhr.responseText.lastIndexOf('\n') + 1;
      const lines = xhr.responseText.slice(read, end).split('\n');
      read = end;

      for (const line of lines) {
        if (line.trim() === '') continue;

        const event: StreamEvent = JSON.parse(line);

        if (event.type === 'text') this.text += event.text;
        if (event.type === 'error') error = event.message;
      }

      m.redraw();
    };

    try {
      await app.request({
        method: 'POST',
        url: `${app.forum.attribute('apiUrl')}/discussions/${this.discussion.id()}/summary`,
        config: (xhr: XMLHttpRequest) => {
          xhr.addEventListener('progress', () => receive(xhr));
          xhr.addEventListener('load', () => receive(xhr));
        },
        // The body is one JSON object per line, read above as it arrives. An error response is a single JSON:API document.
        modifyText: (text: string) => text.trimEnd().split('\n').pop() ?? '',
      });

      if (error) {
        app.alerts.show({ type: 'error' }, error);
      } else {
        await this.refresh();
      }
    } catch {
      // app.request has shown the error already.
    } finally {
      this.generating = false;
      this.text = '';
      m.redraw();
    }
  }

  async remove(): Promise<void> {
    const summary = this.discussion.summary();

    if (!summary || !confirm(app.translator.trans('datlechin-ai-summarize.forum.summary.remove_confirmation', {}, true))) return;

    try {
      await summary.delete();
      await this.refresh();
    } catch {
      // app.request has shown the error already.
    }

    m.redraw();
  }

  refresh() {
    return app.store.find('discussions', this.discussion.id()!, { include: 'summary' });
  }
}
