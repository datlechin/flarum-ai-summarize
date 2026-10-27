import type DiscussionSummary from '../common/models/DiscussionSummary';

declare module 'flarum/common/models/Discussion' {
  export default interface Discussion {
    summary(): DiscussionSummary | false | undefined;
    canSummarize(): boolean | undefined;
  }
}
