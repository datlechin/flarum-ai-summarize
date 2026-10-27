import app from 'flarum/forum/app';
import addSummaryControls from './addSummaryControls';
import addSummaryPanel from './addSummaryPanel';

export { default as extend } from './extend';

app.initializers.add('datlechin-ai-summarize', () => {
  addSummaryPanel();
  addSummaryControls();
});
