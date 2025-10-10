import app from 'flarum/admin/app';

app.initializers.add('datlechin/flarum-ai-summarize', () => {
  app.extensionData
    .for('datlechin-ai-summarize')
    .registerSetting({
      setting: 'datlechin-ai-summarize.enabled',
      label: app.translator.trans('datlechin-ai-summarize.admin.settings.enabled_label'),
      type: 'boolean',
    })
    .registerSetting({
      setting: 'datlechin-ai-summarize.min_posts',
      label: app.translator.trans('datlechin-ai-summarize.admin.settings.min_posts_label'),
      help: app.translator.trans('datlechin-ai-summarize.admin.settings.min_posts_help'),
      type: 'number',
      min: 1,
      default: 3,
    })
    .registerSetting({
      setting: 'datlechin-ai-summarize.max_posts',
      label: app.translator.trans('datlechin-ai-summarize.admin.settings.max_posts_label'),
      help: app.translator.trans('datlechin-ai-summarize.admin.settings.max_posts_help'),
      type: 'number',
      min: 10,
      default: 100,
    })
    .registerSetting({
      setting: 'datlechin-ai-summarize.system_prompt',
      label: app.translator.trans('datlechin-ai-summarize.admin.settings.system_prompt_label'),
      help: app.translator.trans('datlechin-ai-summarize.admin.settings.system_prompt_help'),
      type: 'textarea',
    })
    .registerPermission(
      {
        icon: 'fas fa-magic',
        label: app.translator.trans('datlechin-ai-summarize.admin.permissions.ai_summarize_label'),
        permission: 'discussion.aiSummarize',
      },
      'view'
    );
});
