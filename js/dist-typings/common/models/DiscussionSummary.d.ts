import Model from 'flarum/common/Model';
import type Discussion from 'flarum/common/models/Discussion';
export default class DiscussionSummary extends Model {
    contentHtml(): string;
    model(): string;
    createdAt(): Date;
    postCount(): number;
    newReplyCount(): number;
    canDelete(): boolean;
    discussion(): false | Discussion;
}
