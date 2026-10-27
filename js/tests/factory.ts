import app from 'flarum/forum/app';
import type Discussion from 'flarum/common/models/Discussion';

let nextId = 100;

export function makeDiscussion(attributes: Record<string, unknown> = {}, summary?: Record<string, unknown>): Discussion {
  const id = String(nextId++);
  const summaryId = String(nextId++);

  return app.store.pushPayload<Discussion>({
    data: {
      type: 'discussions',
      id,
      attributes: { title: 'Keeping the forum fast', commentCount: 8, ...attributes },
      relationships: { summary: { data: summary ? { type: 'discussion-summaries', id: summaryId } : null } },
    },
    included: summary
      ? [
          {
            type: 'discussion-summaries',
            id: summaryId,
            attributes: {
              contentHtml: '<p>Caching first.</p>',
              model: 'test-model',
              createdAt: new Date().toISOString(),
              postCount: 8,
              newReplyCount: 0,
              canDelete: false,
              ...summary,
            },
          },
        ]
      : [],
  } as any) as unknown as Discussion;
}
