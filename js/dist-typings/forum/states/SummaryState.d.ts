import type Discussion from 'flarum/common/models/Discussion';
export default class SummaryState {
    discussion: Discussion;
    generating: boolean;
    text: string;
    expanded: boolean;
    constructor(discussion: Discussion);
    static for(discussion: Discussion): SummaryState;
    generate(): Promise<void>;
    remove(): Promise<void>;
    refresh(): Promise<import("flarum/common/Store").ApiResponseSingle<import("flarum/common/Model").default>>;
}
