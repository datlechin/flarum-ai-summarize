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
2. Set up an LLM provider (OpenAI, Anthropic, Google Gemini, etc.)
3. Configure settings in **Admin Panel → Extensions → AI Summarize**
4. Set permissions in **Admin Panel → Permissions**

## Usage

### Accessing the Feature

The AI Summarize button appears in the discussion controls for discussions that meet the minimum post requirement.

![Summarize Button Location](https://github.com/user-attachments/assets/78c0806e-848d-4745-8d37-229be5d89025)

**To generate a summary:**

1. Open any discussion with enough posts (default: 3 or more)
2. Click the discussion controls dropdown (•••)
3. Select **"Summarize with AI"**

### The Summary Popup

A draggable popup appears at the top-right showing the summary in real-time.

https://github.com/user-attachments/assets/6ce00683-e6c2-4c93-bfad-6db9ddc30035

**Features:**

- Real-time streaming with blinking cursor
- HTML formatted text (bold, italic, lists)
- Drag to move anywhere on screen
- Position is remembered
- Minimize/maximize button
- Close button

https://github.com/user-attachments/assets/acd91cf8-b0f1-4ddf-8bd4-30746238adf7

## Customization

### Custom System Prompts

You can customize the AI prompt in admin settings to influence summary style and content.

![Custom Prompt](https://github.com/user-attachments/assets/b61c36bd-1ddd-44ec-89a2-ffc0da4a453f)

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
- [Flarum Community](https://discuss.flarum.org/d/FLARUM_DISCUSS_ID)
- [Report Issues](https://github.com/datlechin/flarum-ai-summarize/issues)

## Sponsor

If you find this extension helpful, you can support ongoing development through [GitHub Sponsors](https://github.com/sponsors/datlechin).
