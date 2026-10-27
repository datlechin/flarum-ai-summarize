import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import Button from 'flarum/common/components/Button';
import DiscussionControls from 'flarum/forum/utils/DiscussionControls';
import type Discussion from 'flarum/common/models/Discussion';
import type ItemList from 'flarum/common/utils/ItemList';
import type Mithril from 'mithril';
import SummaryState from './states/SummaryState';

export default function addSummaryControls() {
  extend(DiscussionControls, 'userControls', function (items: ItemList<Mithril.Children>, discussion: Discussion) {
    if (!discussion.canSummarize()) return;

    items.add(
      'summarize',
      <Button icon="fas fa-wand-magic-sparkles" onclick={() => summarize(discussion)}>
        {app.translator.trans(`datlechin-ai-summarize.forum.discussion_controls.${discussion.summary() ? 'update' : 'summarize'}_button`)}
      </Button>,
      -10
    );
  });

  extend(DiscussionControls, 'moderationControls', function (items: ItemList<Mithril.Children>, discussion: Discussion) {
    const summary = discussion.summary();

    if (!summary || !summary.canDelete()) return;

    items.add(
      'removeSummary',
      <Button icon="fas fa-eraser" onclick={() => SummaryState.for(discussion).remove()}>
        {app.translator.trans('datlechin-ai-summarize.forum.discussion_controls.remove_button')}
      </Button>,
      -10
    );
  });
}

// The summary sits under the first post, which may not be loaded when the reader is far down the discussion.
function summarize(discussion: Discussion) {
  const stream = app.current.get('stream');

  stream?.goToFirst();
  SummaryState.for(discussion).generate();
}
