import app from 'flarum/forum/app';
import Modal, { IInternalModalAttrs } from 'flarum/common/components/Modal';
import Button from 'flarum/common/components/Button';
import LoadingIndicator from 'flarum/common/components/LoadingIndicator';
import icon from 'flarum/common/helpers/icon';
import Discussion from 'flarum/common/models/Discussion';
import type Mithril from 'mithril';

interface SummaryData {
  summary: string;
  provider: string;
  model: string;
  postsCount: number;
}

interface SummaryModalAttrs extends IInternalModalAttrs {
  discussion: Discussion;
}

export default class SummaryModal extends Modal<SummaryModalAttrs> {
  loading: boolean = false;
  error: string | null = null;
  summaryData: SummaryData | null = null;
  streamingContent: string = '';
  eventSource: EventSource | null = null;

  oninit(vnode: Mithril.Vnode<SummaryModalAttrs>) {
    super.oninit(vnode);

    this.generateSummary();
  }

  onremove() {
    if (this.eventSource) {
      this.eventSource.close();
      this.eventSource = null;
    }
  }

  className() {
    return 'SummaryModal Modal--large';
  }

  title() {
    return app.translator.trans('datlechin-ai-summarize.forum.summary.title');
  }

  content() {
    return (
      <div className="Modal-body">
        {this.loading && (
          <div className="SummaryModal-loading">
            <LoadingIndicator />
            <p>{app.translator.trans('datlechin-ai-summarize.forum.summary.loading')}</p>
          </div>
        )}

        {this.error && (
          <div className="SummaryModal-error Alert Alert--error">
            {icon('fas fa-exclamation-circle')}
            <span>{this.error}</span>
          </div>
        )}

        {this.summaryData && (
          <div className="SummaryModal-content">
            <div className={`SummaryModal-summary ${this.loading ? 'is-streaming' : ''}`}>{m.trust(this.summaryData.summary)}</div>

            <div className="SummaryModal-meta">
              <div className="SummaryModal-meta-item">
                {icon('fas fa-robot')}
                <span>
                  {app.translator.trans('datlechin-ai-summarize.forum.summary.generated_with', {
                    provider: this.summaryData.provider,
                    model: this.summaryData.model,
                  })}
                </span>
              </div>
              <div className="SummaryModal-meta-item">
                {icon('fas fa-comment')}
                <span>
                  {app.translator.trans('datlechin-ai-summarize.forum.summary.posts_summarized', {
                    count: this.summaryData.postsCount,
                  })}
                </span>
              </div>
            </div>

            <div className="SummaryModal-actions">
              {Button.component(
                {
                  className: 'Button Button--primary',
                  onclick: () => this.regenerateSummary(),
                  loading: this.loading,
                },
                app.translator.trans('datlechin-ai-summarize.forum.discussion_controls.regenerate_button')
              )}

              {Button.component(
                {
                  className: 'Button',
                  onclick: () => app.modal.close(),
                },
                app.translator.trans('datlechin-ai-summarize.forum.summary.close')
              )}
            </div>
          </div>
        )}
      </div>
    );
  }

  generateSummary() {
    if (this.loading) return;

    if (this.eventSource) {
      this.eventSource.close();
      this.eventSource = null;
    }

    this.loading = true;
    this.error = null;
    this.summaryData = null;
    this.streamingContent = '';
    m.redraw();

    const url = app.forum.attribute('apiUrl') + '/ai-summarize/discussions?id=' + this.attrs.discussion.id() + '&stream=1';

    this.eventSource = new EventSource(url);

    this.eventSource.onmessage = (event) => {
      if (event.data === '[DONE]') {
        this.eventSource?.close();
        this.eventSource = null;
        this.loading = false;
        m.redraw();
        return;
      }

      try {
        const data = JSON.parse(event.data);

        if (data.type === 'metadata') {
          this.summaryData = {
            summary: '',
            provider: data.data.provider,
            model: data.data.model,
            postsCount: data.data.postsCount,
          };
        } else if (data.type === 'content') {
          this.streamingContent += data.data;
          if (this.summaryData) {
            this.summaryData.summary = this.streamingContent;
          }
        } else if (data.type === 'complete') {
          if (this.summaryData) {
            this.summaryData.summary = data.data.html;
          }
        }

        m.redraw();
      } catch (e) {
        console.error('Failed to parse SSE data:', e);
      }
    };

    this.eventSource.onerror = (error) => {
      console.error('EventSource error:', error);
      this.eventSource?.close();
      this.eventSource = null;

      const errorMessage = app.translator.trans('datlechin-ai-summarize.forum.summary.error');
      this.error = typeof errorMessage === 'string' ? errorMessage : String(errorMessage);
      this.loading = false;
      m.redraw();
    };
  }

  regenerateSummary() {
    const confirmMessage = app.translator.trans('datlechin-ai-summarize.forum.summary.regenerate_confirm');
    if (!confirm(typeof confirmMessage === 'string' ? confirmMessage : String(confirmMessage))) {
      return;
    }

    this.generateSummary();
  }
}
