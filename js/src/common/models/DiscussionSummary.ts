import Model from 'flarum/common/Model';
import type Discussion from 'flarum/common/models/Discussion';

export default class DiscussionSummary extends Model {
  contentHtml() {
    return Model.attribute<string>('contentHtml').call(this);
  }

  model() {
    return Model.attribute<string>('model').call(this);
  }

  createdAt() {
    return Model.attribute<Date, string>('createdAt', Model.transformDate).call(this);
  }

  postCount() {
    return Model.attribute<number>('postCount').call(this);
  }

  newReplyCount() {
    return Model.attribute<number>('newReplyCount').call(this);
  }

  canDelete() {
    return Model.attribute<boolean>('canDelete').call(this);
  }

  discussion() {
    return Model.hasOne<Discussion>('discussion').call(this);
  }
}
