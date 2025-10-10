import type Discussion from 'flarum/common/models/Discussion';
import SummaryPopup from '../components/SummaryPopup';

class PopupManager {
  private container: HTMLElement | null = null;
  private isOpen: boolean = false;
  private savedPosition: { x: number; y: number } | null = null;

  init() {
    if (!this.container) {
      this.container = document.createElement('div');
      this.container.id = 'summary-popup-container';
      document.body.appendChild(this.container);
    }
  }

  show(discussion: Discussion) {
    this.init();

    if (this.isOpen) {
      this.close();
    }

    this.isOpen = true;

    m.mount(this.container!, {
      view: () => {
        if (!this.isOpen) return null;

        return m(SummaryPopup, {
          discussion,
          onclose: () => this.close(),
          initialPosition: this.savedPosition,
          onPositionChange: (x: number, y: number) => this.savePosition(x, y),
        });
      },
    });
  }

  close() {
    this.isOpen = false;
    if (this.container) {
      m.mount(this.container, null);
    }
  }

  savePosition(x: number, y: number) {
    this.savedPosition = { x, y };
  }
}

export default new PopupManager();
