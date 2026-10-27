import { extend } from 'flarum/common/extend';
import type ItemList from 'flarum/common/utils/ItemList';
import type PostStream from 'flarum/forum/components/PostStream';
import type Mithril from 'mithril';
import SummaryPanel from './components/SummaryPanel';
import SummaryState from './states/SummaryState';

export default function addSummaryPanel() {
  // PostStream is a lazy chunk, so it is extended by module path.
  extend<PostStream, 'afterFirstPostItems'>(
    'flarum/forum/components/PostStream',
    'afterFirstPostItems',
    function (this: PostStream, items: ItemList<Mithril.Children>) {
      const discussion = (this as any).discussion;

      if (discussion.summary() || discussion.canSummarize() || SummaryState.for(discussion).generating) {
        items.add('summary', <SummaryPanel discussion={discussion} />);
      }
    }
  );
}
