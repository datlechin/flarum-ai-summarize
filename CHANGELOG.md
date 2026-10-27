# Changelog

All notable changes to this extension are listed here. It follows [Semantic Versioning](https://semver.org) and the [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) format.

## [Unreleased]

## [1.0.0] - 2026-10-27

Rewritten for Flarum 2, on top of [AI](https://github.com/datlechin/flarum-ai) 1.0. Run `php flarum migrate` after updating.

### Added

- Summaries are saved, one per discussion, and shown under the first post to everyone with the new **View AI summaries** permission.
- Each point links to the posts it comes from, and participants are named with @mentions.
- The summary says how many replies it hasn't seen, and **Update** writes a new one.
- A summary is deleted when a post it covers is hidden, edited or deleted.
- Moderators who can edit posts can remove a summary.
- **Automatic summaries per hour**: `php flarum summaries:backfill` summarizes recently active discussions through the scheduler. Off by default.
- Long discussions are read from their first and last posts plus the most liked ones in between.
- A limit of six summaries per five minutes per person, and one at a time per discussion.

### Changed

- **Breaking:** requires Flarum 2.0, PHP 8.3 and AI 1.0.
- **Breaking:** the custom system prompt is now **Extra instructions**, added to the built-in prompt instead of replacing it. The migration moves it.
- The summary streams in place under the first post instead of in a draggable popup.
- **Write AI summaries** starts out granted to Moderators.

### Removed

- **Breaking:** the **Enable AI Summarization** setting. The permissions decide who sees and writes summaries.
- **Breaking:** `GET /api/ai-summarize/discussions`. Use `POST /api/discussions/{id}/summary`.
- The draggable popup and its remembered position.

### Fixed

- The streamed summary was inserted as HTML, so a post could make the model write a script that ran in the reader's browser.
- Discussions longer than **Maximum Posts to Include** were summarized from their first posts only.
- Summaries could still come back in English on forums in other languages. The prompt now names the forum's default language instead of giving a locale code.

## [0.1.2] - 2026-03-21

### Fixed

- Summaries now follow the forum's language.

## [0.1.1] - 2025-10-12

### Fixed

- The summarize button showed only to admins.

### Changed

- Lowered the Flarum requirement from 1.8 to 1.2.

## [0.1.0] - 2025-10-11

First release, for Flarum 1.

[Unreleased]: https://github.com/datlechin/flarum-ai-summarize/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/datlechin/flarum-ai-summarize/compare/v0.1.2...v1.0.0
[0.1.2]: https://github.com/datlechin/flarum-ai-summarize/compare/v0.1.1...v0.1.2
[0.1.1]: https://github.com/datlechin/flarum-ai-summarize/compare/v0.1.0...v0.1.1
[0.1.0]: https://github.com/datlechin/flarum-ai-summarize/releases/tag/v0.1.0
