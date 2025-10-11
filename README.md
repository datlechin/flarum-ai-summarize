# AI Summarize for Flarum

![License](https://img.shields.io/badge/license-MIT-blue.svg) [![Latest Stable Version](https://img.shields.io/packagist/v/datlechin/flarum-ai-summarize.svg)](https://packagist.org/packages/datlechin/flarum-ai-summarize) [![Total Downloads](https://img.shields.io/packagist/dt/datlechin/flarum-ai-summarize.svg)](https://packagist.org/packages/datlechin/flarum-ai-summarize)

A [Flarum](https://flarum.org) extension that provides AI-powered discussion summarization with real-time streaming.

## Features

- 🤖 AI-powered discussion summaries
- ⚡ Real-time streaming display
- 🎨 HTML formatted output
- 📌 Draggable popup with position memory
- � Multiple LLM providers (OpenAI, Gemini, etc.)
- ⚙️ Customizable prompts and settings

## Requirements

- Flarum 1.2+
- PHP 8.1+
- [datlechin/flarum-ai](https://github.com/datlechin/flarum-ai) extension (required dependency)
- An AI provider configured (OpenAI, Gemini, etc.)

## Installation

Install with composer:

```bash
composer require datlechin/flarum-ai-summarize
```

## Configuration

1. Install and configure `datlechin/flarum-ai` extension
2. Set up an LLM provider (OpenAI, Gemini, etc.)
3. Configure settings in **Admin Panel → Extensions → AI Summarize**
4. Set permissions in **Admin Panel → Permissions**

## Usage

### Accessing the Feature

The AI Summarize button appears in the discussion controls for discussions that meet the minimum post requirement.

![Summarize Button Location](https://via.placeholder.com/800x400/4A90E2/ffffff?text=Screenshot:+Summarize+Button+in+Discussion+Controls)

**To generate a summary:**

1. Open any discussion with enough posts (default: 3 or more)
2. Click the discussion controls dropdown (•••)
3. Select **"Summarize with AI"**

### The Summary Popup

A draggable popup appears at the top-right showing the summary in real-time.

![Summary Popup](https://via.placeholder.com/800x600/5CB85C/ffffff?text=Screenshot:+Summary+Popup)

**Features:**
- Real-time streaming with blinking cursor
- HTML formatted text (bold, italic, lists)
- Drag to move anywhere on screen
- Position is remembered
- Minimize/maximize button
- Close button

![Draggable Popup](https://via.placeholder.com/800x450/F0AD4E/ffffff?text=Screenshot:+Drag+and+Drop)

## Customization

### Custom System Prompts

You can customize the AI prompt in admin settings to influence summary style and content.

**Example custom prompt:**

```text
You are an AI assistant summarizing forum discussions.
Create a concise summary (100-150 words) that:
- Highlights the main topic and key arguments
- Mentions important conclusions or consensus
- Uses clear, neutral language
- Focuses on content, not participants
Avoid greetings and meta-commentary.
```

**Prompt Tips:**
- Define desired length and style
- Specify tone (neutral, formal, casual)
- Mention what to focus on (key points, debates, conclusions)
- Set boundaries (what to exclude)

## Links

- [Packagist](https://packagist.org/packages/datlechin/flarum-ai-summarize)
- [GitHub Repository](https://github.com/datlechin/flarum-ai-summarize)
- [Flarum Community](https://discuss.flarum.org)
- [Report Issues](https://github.com/datlechin/flarum-ai-summarize/issues)

## Sponsor

If you find this extension helpful, you can support ongoing development through [GitHub Sponsors](https://github.com/sponsors/datlechin).

## License

MIT License - see LICENSE file for details
