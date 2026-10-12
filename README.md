# Pavel Silinskii Social Share

![WordPress](https://img.shields.io/badge/WordPress-5.9%2B-21759B?logo=wordpress&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php&logoColor=white)
![License](https://img.shields.io/badge/License-GPL%20v2-green)
![Version](https://img.shields.io/badge/Version-1.0.0-blue)

Lightweight social share buttons for WordPress: Facebook, Twitter/X, LinkedIn, Pinterest, WhatsApp, plus a Copy Link button. No external scripts, no jQuery, no tracking, no cookies — inline SVG icons and vanilla JavaScript.

---

## Table of Contents

- [Features](#features)
- [How It Works](#how-it-works)
- [Installation](#installation)
- [Usage](#usage)
- [Configuration](#configuration)
- [Settings Reference](#settings-reference)
- [Share URLs](#share-urls)
- [HTML Output and Styling](#html-output-and-styling)
- [Security](#security)
- [Project Structure](#project-structure)
- [Requirements](#requirements)
- [Changelog](#changelog)

---

## Features

- **5 networks** — Facebook, Twitter/X, LinkedIn, Pinterest, WhatsApp
- **Copy Link button** — Clipboard API with an `execCommand` fallback
- **3 button styles** — Icon + Text, Icon only, Text only
- **3 shapes** — Rounded, Square, Circle
- **3 sizes** — Small, Medium, Large
- **Placement** — before content, after content, both, or manual via the `[psss_share]` shortcode
- **Any post type** — choose which public post types show the buttons
- **Popup or new tab** — share windows open in a 600×450 popup or a new tab
- **Lightweight** — inline SVG icons, one small CSS file and one small JS file, no jQuery
- **Private** — no external requests on page load, no cookies, no data collected

---

## How It Works

1. `Frontend::filter_content()` hooks into `the_content` (priority 20). On singular views in the main query, for enabled post types, it adds the buttons before and/or after the content.
2. `ShareUrl::get()` builds each network's share link from the post's permalink, title and featured image, all `rawurlencode()`d.
3. `psss-frontend.js` opens share links in a popup (when **Open in popup** is selected) and handles the Copy Link button. If the browser blocks the popup, the link opens in a new tab instead.

---

## Installation

### Manual

1. Download or clone this repository.
2. Upload the `pavel-silinskii-social-share` folder to `/wp-content/plugins/`.
3. Activate the plugin in **WordPress Admin → Plugins**.
4. Configure it in **Settings → Social Share**.

### Via Git

```bash
cd wp-content/plugins
git clone https://github.com/pavelsilinskiiwork/pavel-silinskii-social-share.git
```

Then activate in **WordPress Admin → Plugins**.

---

## Usage

With **Position** set to Before, After or Both, the buttons appear automatically on the selected post types.

To place them manually, set **Position** to *None* and use the shortcode:

```
[psss_share]
[psss_share post_id="42"]
```

`post_id` defaults to the current post.

In a theme template:

```php
<?php echo do_shortcode( '[psss_share]' ); ?>
```

---

## Configuration

**Settings → Social Share** (WordPress Settings API, saved via `options.php`):

| Section | Options |
|---|---|
| **Networks** | Share buttons (Facebook, Twitter, LinkedIn, WhatsApp, Pinterest) |
| **Placement** | Position (before / after / both / none), Open links (popup / new tab) |
| **Post Types** | Show on (any public post type) |
| **Appearance** | Button style, Shape, Size, Label |

---

## Settings Reference

Stored as a single option, `psss_settings`. Defaults are saved on activation.

| Key | Type | Default | Allowed values |
|---|---|---|---|
| `networks` | array | all five | `facebook`, `twitter`, `linkedin`, `whatsapp`, `pinterest` |
| `position` | string | `after` | `before`, `after`, `both`, `none` |
| `post_types` | array | `['post']` | public post type slugs |
| `button_style` | string | `icon_text` | `icon_text`, `icon_only`, `text_only` |
| `shape` | string | `rounded` | `rounded`, `square`, `circle` |
| `size` | string | `medium` | `small`, `medium`, `large` |
| `label` | string | `Share this:` | any text, empty hides the heading |
| `open_in` | string | `popup` | `popup`, `new_tab` |

Invalid values fall back to the default.

---

## Share URLs

| Network | URL pattern |
|---|---|
| Facebook | `https://www.facebook.com/sharer/sharer.php?u={url}` |
| Twitter / X | `https://twitter.com/intent/tweet?url={url}&text={title}` |
| LinkedIn | `https://www.linkedin.com/sharing/share-offsite/?url={url}` |
| Pinterest | `https://pinterest.com/pin/create/button/?url={url}&description={title}&media={image}` |
| WhatsApp | `https://api.whatsapp.com/send?text={title}%20{url}` |
| Copy Link | JavaScript `navigator.clipboard`, no external URL |

`{image}` is the featured image (`large` size), or empty if the post has none.

---

## HTML Output and Styling

```html
<div class="psss-share psss-style-icon_text psss-shape-rounded psss-size-medium psss-open-popup">
  <span class="psss-label">Share this:</span>
  <div class="psss-buttons">
    <a class="psss-btn psss-facebook" href="…" target="_blank" rel="noopener noreferrer" aria-label="Share on Facebook">
      <span class="psss-icon"><svg …></svg></span>
      <span class="psss-text">Facebook</span>
    </a>
    …
    <button type="button" class="psss-btn psss-copy" data-url="…" aria-label="Copy link">
      <span class="psss-icon"><svg …></svg></span>
      <span class="psss-text">Copy link</span>
    </button>
  </div>
</div>
```

Button colors are CSS custom properties, so a theme can override them:

```css
:root {
  --psss-facebook: #1877f2;
  --psss-twitter: #000000;
  --psss-linkedin: #0a66c2;
  --psss-pinterest: #e60023;
  --psss-whatsapp: #25d366;
  --psss-copy: #6b7280;
}
```

---

## Security

| Measure | Where |
|---|---|
| `current_user_can( 'manage_options' )` | settings page |
| Settings API nonce (`settings_fields()`) | settings form |
| `sanitize_callback` with allow-lists, `sanitize_key()`, `sanitize_text_field()` | `Admin::sanitize_settings()` |
| `rawurlencode()` on every share URL parameter | `ShareUrl::get()` |
| `esc_html()` / `esc_attr()` / `esc_url()` | all output |
| Hardcoded SVG icons, no user input | `ShareUrl::get_svg()` |
| `ABSPATH` guard | every PHP file |

---

## Project Structure

```
pavel-silinskii-social-share/
├── pavel-silinskii-social-share.php   # Main file: header, constants, bootstrap, activation
├── readme.txt                         # WordPress.org readme
├── README.md                          # This file
├── uninstall.php                      # Deletes psss_settings
├── includes/
│   ├── class-psss-frontend.php        # Content filter, shortcode, assets, button HTML
│   └── class-psss-share-url.php       # Share URLs, labels, SVG icons
├── admin/
│   └── class-psss-admin.php           # Settings page, fields, sanitization
├── assets/
│   ├── css/
│   │   ├── psss-frontend.css
│   │   └── psss-admin.css
│   └── js/
│       └── psss-frontend.js           # Vanilla JS: popup opener, copy link
└── languages/
```

All classes live in the `PavelSilinskii\SocialShare` namespace.

---

## Requirements

- WordPress 5.9+
- PHP 8.0+

---

## Changelog

### 1.0.0

- Initial release.

---

## About the Developer

Built by **Pavel Silinskii** — Full-Stack PHP Developer.

- GitHub: [github.com/pavelsilinskiiwork](https://github.com/pavelsilinskiiwork)
- WordPress.org: [profiles.wordpress.org/pavelsilinskii](https://profiles.wordpress.org/pavelsilinskii/)

---

## License

Licensed under the [GPL v2 or later](https://www.gnu.org/licenses/gpl-2.0.html).
