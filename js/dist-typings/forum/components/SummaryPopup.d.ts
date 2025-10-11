import Component, { ComponentAttrs } from 'flarum/common/Component';
import Discussion from 'flarum/common/models/Discussion';
import type Mithril from 'mithril';
interface SummaryData {
    summary: string;
}
interface SummaryPopupAttrs extends ComponentAttrs {
    discussion: Discussion;
    onclose: () => void;
    initialPosition?: {
        x: number;
        y: number;
    } | null;
    onPositionChange?: (x: number, y: number) => void;
}
export default class SummaryPopup extends Component<SummaryPopupAttrs> {
    loading: boolean;
    error: string | null;
    summaryData: SummaryData | null;
    streamingContent: string;
    eventSource: EventSource | null;
    isMinimized: boolean;
    isClosing: boolean;
    isDragging: boolean;
    dragStartX: number;
    dragStartY: number;
    popupX: number;
    popupY: number;
    popupElement: HTMLElement | null;
    oninit(vnode: Mithril.Vnode<SummaryPopupAttrs>): void;
    oncreate(vnode: Mithril.VnodeDOM<SummaryPopupAttrs>): void;
    onremove(): void;
    view(): JSX.Element;
    generateSummary(): void;
    close(): void;
    startDrag(e: MouseEvent): void;
    onDrag(e: MouseEvent): void;
    stopDrag(handleMouseMove: (e: MouseEvent) => void): void;
    updatePosition(): void;
}
export {};
