import app from 'flarum/forum/app';
import Component, { type ComponentAttrs } from 'flarum/common/Component';
import Button from 'flarum/common/components/Button';
import Icon from 'flarum/common/components/Icon';
import LoadingIndicator from 'flarum/common/components/LoadingIndicator';
import humanTime from 'flarum/common/helpers/humanTime';
import classList from 'flarum/common/utils/classList';
import type Discussion from 'flarum/common/models/Discussion';
import type Mithril from 'mithril';
import SummaryState from '../states/SummaryState';
import SummaryPreview from './SummaryPreview';

export interface SummaryPanelAttrs extends ComponentAttrs {
  discussion: Discussion;
}

export default class SummaryPanel extends Component<SummaryPanelAttrs> {
  view() {
    const { discussion } = this.attrs;
    const state = SummaryState.for(discussion);
    const summary = discussion.summary();
    const id = `DiscussionSummary-${discussion.id()}`;

    return (
      <section className={classList('DiscussionSummary', !state.expanded && 'DiscussionSummary--collapsed')} aria-labelledby={`${id}-heading`}>
        <div className="DiscussionSummary-side">
          <span className="Avatar DiscussionSummary-icon" aria-hidden="true">
            <Icon name="fas fa-wand-magic-sparkles" />
          </span>
        </div>
        <div className="DiscussionSummary-main">
          <header className="DiscussionSummary-header">
            <h3 id={`${id}-heading`} className="DiscussionSummary-title">
              {app.translator.trans('datlechin-ai-summarize.forum.summary.heading')}
            </h3>
            {summary && !state.generating && (
              <Button
                className="Button Button--icon Button--link DiscussionSummary-toggle"
                icon={state.expanded ? 'fas fa-chevron-up' : 'fas fa-chevron-down'}
                aria-expanded={state.expanded ? 'true' : 'false'}
                aria-controls={`${id}-body`}
                aria-label={app.translator.trans(`datlechin-ai-summarize.forum.summary.${state.expanded ? 'collapse' : 'expand'}_button`, {}, true)}
                onclick={() => (state.expanded = !state.expanded)}
              />
            )}
          </header>
          {state.expanded && (
            <div id={`${id}-body`} className="DiscussionSummary-body" aria-live="polite" aria-busy={state.generating ? 'true' : 'false'}>
              {this.content(state)}
            </div>
          )}
        </div>
      </section>
    );
  }

  content(state: SummaryState): Mithril.Children {
    const { discussion } = this.attrs;
    const summary = discussion.summary();

    if (state.generating) {
      return (
        <>
          {state.text && <SummaryPreview text={state.text} />}
          <p className="DiscussionSummary-status">
            <LoadingIndicator display="inline" size="small" />
            {app.translator.trans('datlechin-ai-summarize.forum.summary.generating_text')}
          </p>
        </>
      );
    }

    if (!summary) {
      return (
        <Button className="Button" icon="fas fa-wand-magic-sparkles" onclick={() => state.generate()}>
          {app.translator.trans('datlechin-ai-summarize.forum.summary.generate_button')}
        </Button>
      );
    }

    const newReplies = summary.newReplyCount();

    return (
      <>
        <div className="Post-body">{m.trust(summary.contentHtml())}</div>
        <footer className="DiscussionSummary-footer">
          <span>
            {app.translator.trans('datlechin-ai-summarize.forum.summary.generated_text', {
              model: summary.model(),
              count: summary.postCount(),
              ago: humanTime(summary.createdAt()),
            })}
          </span>
          {newReplies > 0 && <span>{app.translator.trans('datlechin-ai-summarize.forum.summary.new_replies_text', { count: newReplies })}</span>}
          {discussion.canSummarize() && (
            <Button className="Button Button--link" onclick={() => state.generate()}>
              {app.translator.trans('datlechin-ai-summarize.forum.summary.update_button')}
            </Button>
          )}
        </footer>
      </>
    );
  }
}
