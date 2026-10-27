import Component, { type ComponentAttrs } from 'flarum/common/Component';
import type Mithril from 'mithril';

declare const s9e: { TextFormatter: { preview(text: string, element: Element): void } };

export interface SummaryPreviewAttrs extends ComponentAttrs {
  text: string;
}

// Renders the text as it streams in with the forum's own formatter, the way the composer preview does.
export default class SummaryPreview extends Component<SummaryPreviewAttrs> {
  rendered?: string;

  view() {
    return <div className="Post-body DiscussionSummary-preview" />;
  }

  oncreate(vnode: Mithril.VnodeDOM<SummaryPreviewAttrs, this>) {
    super.oncreate(vnode);
    this.render(vnode.dom);
  }

  onupdate(vnode: Mithril.VnodeDOM<SummaryPreviewAttrs, this>) {
    super.onupdate(vnode);
    this.render(vnode.dom);
  }

  render(element: Element) {
    if (this.rendered === this.attrs.text) return;

    this.rendered = this.attrs.text;

    // The server turns [#3] into a link to the post once the summary is saved.
    s9e.TextFormatter.preview(this.attrs.text.replace(/\[\s*(#\d+(?:\s*,\s*#?\d+)*)\s*\]/g, '$1'), element);
  }
}
