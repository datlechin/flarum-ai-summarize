# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-01-10

### Added

- Initial release of AI Summarize extension
- AI-powered discussion summarization using LLM providers
- Integration with `datlechin/flarum-ai` extension
- Support for multiple AI providers (OpenAI, Gemini, etc.)
- Real-time summary generation without database storage
- Permission system for controlling access to summarization
- Customizable system prompts for AI
- Configurable minimum posts threshold
- Configurable maximum posts to include
- Beautiful modal UI for displaying summaries
- Summary metadata display (provider, model, post count)
- Admin settings page for configuration
- Complete localization support (English included)
- Responsive design for mobile devices

### Features

- **Lightweight**: No database migrations or caching needed
- **Real-time**: Generate summaries on-demand
- **Permission Control**: Fine-grained permissions for who can summarize
- **Multiple Providers**: Works with any LLM provider supported by flarum-ai
- **Quality Prompts**: Pre-configured prompts optimized for forum discussions
- **Cost Management**: Configurable limits to control API usage
- **User Experience**: Clean, intuitive interface matching Flarum design

### API

- `POST /api/discussions/{id}/summarize` - Generate discussion summary
- Returns JSON response with summary data

### Dependencies

- Requires `datlechin/flarum-ai` extension
- Requires Flarum 1.2+
- Requires PHP 8.1+
