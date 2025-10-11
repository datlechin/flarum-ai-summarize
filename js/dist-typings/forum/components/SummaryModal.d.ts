/// <reference types="flarum/@types/translator-icu-rich" />
import Modal, { IInternalModalAttrs } from 'flarum/common/components/Modal';
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
    loading: boolean;
    error: string | null;
    summaryData: SummaryData | null;
    streamingContent: string;
    eventSource: EventSource | null;
    oninit(vnode: Mithril.Vnode<SummaryModalAttrs>): void;
    onremove(): void;
    className(): string;
    title(): import("@askvortsov/rich-icu-message-formatter").NestedStringArray;
    content(): JSX.Element;
    generateSummary(): void;
    regenerateSummary(): void;
}
export {};
