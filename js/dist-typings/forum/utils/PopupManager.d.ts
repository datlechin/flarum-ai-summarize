import type Discussion from 'flarum/common/models/Discussion';
declare class PopupManager {
    private container;
    private isOpen;
    private savedPosition;
    init(): void;
    show(discussion: Discussion): void;
    close(): void;
    savePosition(x: number, y: number): void;
}
declare const _default: PopupManager;
export default _default;
