import Component, { type ComponentAttrs } from 'flarum/common/Component';
import type Discussion from 'flarum/common/models/Discussion';
import type Mithril from 'mithril';
import SummaryState from '../states/SummaryState';
export interface SummaryPanelAttrs extends ComponentAttrs {
    discussion: Discussion;
}
export default class SummaryPanel extends Component<SummaryPanelAttrs> {
    view(): JSX.Element;
    content(state: SummaryState): Mithril.Children;
}
