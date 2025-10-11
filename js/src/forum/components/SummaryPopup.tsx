import app from 'flarum/forum/app';
import Component, { ComponentAttrs } from 'flarum/common/Component';
import Button from 'flarum/common/components/Button';
import icon from 'flarum/common/helpers/icon';
import Discussion from 'flarum/common/models/Discussion';
import type Mithril from 'mithril';

interface SummaryData {
  summary: string;
}

interface SummaryPopupAttrs extends ComponentAttrs {
  discussion: Discussion;
  onclose: () => void;
  initialPosition?: { x: number; y: number } | null;
  onPositionChange?: (x: number, y: number) => void;
}

export default class SummaryPopup extends Component<SummaryPopupAttrs> {
  loading: boolean = false;
  error: string | null = null;
  summaryData: SummaryData | null = null;
  streamingContent: string = '';
  eventSource: EventSource | null = null;
  isMinimized: boolean = false;
  isClosing: boolean = false;

  // Drag state
  isDragging: boolean = false;
  dragStartX: number = 0;
  dragStartY: number = 0;
  popupX: number = 0;
  popupY: number = 0;
  popupElement: HTMLElement | null = null;

  oninit(vnode: Mithril.Vnode<SummaryPopupAttrs>) {
    super.oninit(vnode);
    this.generateSummary();
  }

  oncreate(vnode: Mithril.VnodeDOM<SummaryPopupAttrs>) {
    super.oncreate(vnode);
    this.popupElement = vnode.dom as HTMLElement;

    // Use saved position if available, otherwise use default position
    if (this.attrs.initialPosition) {
      this.popupX = this.attrs.initialPosition.x;
      this.popupY = this.attrs.initialPosition.y;
    } else {
      const rect = this.popupElement.getBoundingClientRect();
      this.popupX = window.innerWidth - rect.width - 20;
      this.popupY = 70;
    }

    this.updatePosition();
  }

  onremove() {
    if (this.eventSource) {
      this.eventSource.close();
      this.eventSource = null;
    }
  }

  view() {
    return (
      <div
        className={`SummaryPopup ${this.isClosing ? 'is-closing' : ''} ${this.isMinimized ? 'is-minimized' : ''} ${
          this.isDragging ? 'is-dragging' : ''
        }`}
        style={`transform: translate(${this.popupX}px, ${this.popupY}px);`}
      >
        <div className="SummaryPopup-header" onmousedown={(e: MouseEvent) => this.startDrag(e)}>
          <div className="SummaryPopup-title">
            {icon('fas fa-magic')}
            <span>{app.translator.trans('datlechin-ai-summarize.forum.summary.title')}</span>
          </div>
          <div className="SummaryPopup-actions">
            <Button
              className="Button Button--icon Button--link"
              icon={this.isMinimized ? 'fas fa-chevron-down' : 'fas fa-chevron-up'}
              onclick={() => {
                this.isMinimized = !this.isMinimized;
              }}
            />
            <Button className="Button Button--icon Button--link" icon="fas fa-times" onclick={() => this.close()} />
          </div>
        </div>

        {!this.isMinimized && (
          <div className="SummaryPopup-body">
            {this.error && <div className="SummaryPopup-error">{this.error}</div>}

            {(this.summaryData && !this.error) && (
              <div className="SummaryPopup-content">
                <div className={`SummaryPopup-summary ${this.loading ? 'is-streaming' : ''}`}>
                  {this.summaryData.summary && m.trust(this.summaryData.summary)}
                </div>
              </div>
            )}
          </div>
        )}
      </div>
    );
  }

  generateSummary() {
    if (this.loading) return;

    if (this.eventSource) {
      this.eventSource.close();
      this.eventSource = null;
    }

    this.loading = true;
    this.error = null;
    this.summaryData = null;
    this.streamingContent = '';
    m.redraw();

    const url = app.forum.attribute('apiUrl') + '/ai-summarize/discussions?id=' + this.attrs.discussion.id() + '&stream=1';

    this.eventSource = new EventSource(url);

    this.eventSource.onmessage = (event) => {
      if (event.data === '[DONE]') {
        this.eventSource?.close();
        this.eventSource = null;
        this.loading = false;
        m.redraw();
        return;
      }

      try {
        const data = JSON.parse(event.data);

        if (data.type === 'start') {
          this.summaryData = { summary: '' };
        } else if (data.type === 'content') {
          this.streamingContent += data.data;
          if (!this.summaryData) {
            this.summaryData = { summary: '' };
          }
          this.summaryData.summary = this.streamingContent;
        } else if (data.type === 'complete') {
          if (!this.summaryData) {
            this.summaryData = { summary: '' };
          }
          this.summaryData.summary = data.data.html;
        }

        m.redraw();
      } catch (e) {
        console.error('Failed to parse SSE data:', e);
      }
    };

    this.eventSource.onerror = (error) => {
      console.error('EventSource error:', error);
      this.eventSource?.close();
      this.eventSource = null;

      const errorMessage = app.translator.trans('datlechin-ai-summarize.forum.summary.error');
      this.error = typeof errorMessage === 'string' ? errorMessage : String(errorMessage);
      this.loading = false;
      m.redraw();
    };
  }

  close() {
    this.isClosing = true;
    m.redraw();

    setTimeout(() => {
      if (this.eventSource) {
        this.eventSource.close();
        this.eventSource = null;
      }
      this.attrs.onclose();
    }, 300);
  }

  startDrag(e: MouseEvent) {
    const target = e.target as HTMLElement;
    if (target.closest('.Button')) {
      return;
    }

    e.preventDefault();
    this.isDragging = true;
    this.dragStartX = e.clientX - this.popupX;
    this.dragStartY = e.clientY - this.popupY;

    const handleMouseMove = (e: MouseEvent) => this.onDrag(e);
    const handleMouseUp = () => this.stopDrag(handleMouseMove);

    document.addEventListener('mousemove', handleMouseMove);
    document.addEventListener('mouseup', handleMouseUp);

    m.redraw();
  }

  onDrag(e: MouseEvent) {
    if (!this.isDragging) return;

    e.preventDefault();
    this.popupX = e.clientX - this.dragStartX;
    this.popupY = e.clientY - this.dragStartY;

    if (this.popupElement) {
      const rect = this.popupElement.getBoundingClientRect();
      const maxX = window.innerWidth - rect.width;
      const maxY = window.innerHeight - rect.height;

      this.popupX = Math.max(0, Math.min(this.popupX, maxX));
      this.popupY = Math.max(0, Math.min(this.popupY, maxY));
    }

    this.updatePosition();
    m.redraw();
  }

  stopDrag(handleMouseMove: (e: MouseEvent) => void) {
    this.isDragging = false;
    document.removeEventListener('mousemove', handleMouseMove);

    // Save position when drag ends
    if (this.attrs.onPositionChange) {
      this.attrs.onPositionChange(this.popupX, this.popupY);
    }

    m.redraw();
  }

  updatePosition() {
    if (this.popupElement) {
      this.popupElement.style.transform = `translate(${this.popupX}px, ${this.popupY}px)`;
    }
  }
}
