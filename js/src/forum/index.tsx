import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import Discussion from 'flarum/common/models/Discussion';
import Model from 'flarum/common/Model';
import DiscussionControls from 'flarum/forum/utils/DiscussionControls';
import Button from 'flarum/common/components/Button';
import PopupManager from './utils/PopupManager';

app.initializers.add('datlechin/flarum-ai-summarize', () => {
  Discussion.prototype.canSummarize = Model.attribute('canSummarize');

  extend(DiscussionControls, 'userControls', function (items: any, discussion: Discussion) {
    const enabled = app.forum.attribute('datlechin-ai-summarize.enabled');
    const minPosts = Number(app.forum.attribute('datlechin-ai-summarize.minPosts') || 3);

    if (!enabled || !discussion.canSummarize?.()) {
      return;
    }

    const commentCount = discussion.commentCount();

    if (!commentCount || commentCount < minPosts) {
      return;
    }

    items.add(
      'aiSummarize',
      <Button onclick={() => PopupManager.show(discussion)} icon="fas fa-magic">
        {app.translator.trans('datlechin-ai-summarize.forum.discussion_controls.summarize_button')}
      </Button>,
      -10
    );
  });
});
