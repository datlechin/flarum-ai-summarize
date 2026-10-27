import Extend from 'flarum/common/extenders';
import Discussion from 'flarum/common/models/Discussion';
import DiscussionSummary from './models/DiscussionSummary';

export default [
  new Extend.Store().add('discussion-summaries', DiscussionSummary),

  new Extend.Model(Discussion).hasOne<DiscussionSummary>('summary').attribute<boolean>('canSummarize'),
];
