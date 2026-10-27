import app from 'flarum/admin/app';
import Extend from 'flarum/common/extenders';
import Link from 'flarum/common/components/Link';
import commonExtend from '../common/extend';

export default [
  ...commonExtend,

  new Extend.Admin()
    .setting(() => () => (
      <p className="helpText">
        {app.translator.trans('datlechin-ai-summarize.admin.settings.provider_text', {
          a: <Link href={app.route('extension', { id: 'datlechin-ai' })} />,
        })}
      </p>
    ))
    .setting(() => ({
      setting: 'datlechin-ai-summarize.min_posts',
      type: 'number',
      min: 1,
      label: app.translator.trans('datlechin-ai-summarize.admin.settings.min_posts_label'),
      help: app.translator.trans('datlechin-ai-summarize.admin.settings.min_posts_help'),
    }))
    .setting(() => ({
      setting: 'datlechin-ai-summarize.max_posts',
      type: 'number',
      min: 10,
      label: app.translator.trans('datlechin-ai-summarize.admin.settings.max_posts_label'),
      help: app.translator.trans('datlechin-ai-summarize.admin.settings.max_posts_help'),
    }))
    .setting(() => ({
      setting: 'datlechin-ai-summarize.auto_per_hour',
      type: 'number',
      min: 0,
      label: app.translator.trans('datlechin-ai-summarize.admin.settings.auto_per_hour_label'),
      help: app.translator.trans('datlechin-ai-summarize.admin.settings.auto_per_hour_help'),
    }))
    .setting(() => ({
      setting: 'datlechin-ai-summarize.instructions',
      type: 'textarea',
      label: app.translator.trans('datlechin-ai-summarize.admin.settings.instructions_label'),
      help: app.translator.trans('datlechin-ai-summarize.admin.settings.instructions_help'),
    }))
    .permission(
      () => ({
        icon: 'fas fa-wand-magic-sparkles',
        label: app.translator.trans('datlechin-ai-summarize.admin.permissions.view_label'),
        permission: 'discussion.viewAiSummary',
        allowGuest: true,
      }),
      'view'
    )
    .permission(
      () => ({
        icon: 'fas fa-wand-magic-sparkles',
        label: app.translator.trans('datlechin-ai-summarize.admin.permissions.summarize_label'),
        permission: 'discussion.aiSummarize',
      }),
      'reply'
    ),
];
