import Component, { type ComponentAttrs } from 'flarum/common/Component';
import type Mithril from 'mithril';
export interface SummaryPreviewAttrs extends ComponentAttrs {
    text: string;
}
export default class SummaryPreview extends Component<SummaryPreviewAttrs> {
    rendered?: string;
    view(): JSX.Element;
    oncreate(vnode: Mithril.VnodeDOM<SummaryPreviewAttrs, this>): void;
    onupdate(vnode: Mithril.VnodeDOM<SummaryPreviewAttrs, this>): void;
    render(element: Element): void;
}
