# AGENTS.md

Instructions for AI agents working in this repository.

## Project overview

**soboldev** is a WordPress theme starter kit, used here for a real-estate catalog/shop. Blocks are built on ACF Pro and front-end assets are built with Vite.

- PHP 8.5 in Docker (composer requires >= 8.0), WordPress 7.1.4 — both set in `.env` (`PHP_VERSION`, `WP_VERSION`)
- Node `^22.22.2 || >=24.15.0`; Vite 8 (Rolldown), Sass, ESLint 10 (flat config `eslint.config.js`), Stylelint 18 (`stylelint-config-standard-scss`)
- Composer installs ACF Pro and Safe SVG into `wp-content/mu-plugins/` (loaded by `mu-init.php`) and puts the vendor dir in `wp-content/themes/soboldev/vendor/`
- ACF Pro license key lives in `.env` as `ACF_PRO_KEY`; never commit it. It feeds both Composer auth (`composer acf-auth` → gitignored `auth.json`) and the `ACF_PRO_LICENSE` constant in `wp-config.php`
- In Docker `wp` is a wrapper around `wp-cli.phar` that sends PHP notices to stderr, so `wp ... --format=json` output stays parseable

## Structure

```
_docker/                     # nginx, php-fpm, mariadb, smtp configs
scripts/                     # WP-CLI scripts (check-unused-uploads-files.php)
vite.config.js               # build, aliases, entry points
wp-content/mu-plugins/mu-init.php
wp-content/themes/soboldev/
  functions.php              # entry point, requires hooks/setup.php
  hooks/                     # all theme logic: one file = one feature
    setup.php                # theme supports, enqueue, CPTs, requires other hooks
    vite.php                 # intercepts enqueue → Vite dev server / dist manifest
    register-blocks.php      # auto-registers blocks from blocks/*
  classes/                   # PHP classes (namespace SobolDev\...)
  blocks/<name>/             # ACF blocks (see below)
  template-parts/            # template partials (get_template_part)
  acf-json/                  # ACF field groups / options pages (Local JSON)
  assets/css/                # global styles, _constants.scss, _mixins.scss
  assets/js/                 # scripts.js (core), global.js, helpers.js, modules/
  dist/                      # build output (gitignored)
```

## Block anatomy

Every folder in `blocks/` is registered automatically (see `hooks/register-blocks.php`):

| File | Purpose |
|---|---|
| `block.json` | metadata; `name: "theme/<slug>"`, `acf.renderTemplate: "template.php"` |
| `template.php` | PHP render template |
| `functions.php` | block server logic (AJAX handlers, queries), auto-included |
| `group_*.json` | the block's ACF field group (ACF saves it next to the block) |
| `style.scss` / `styles.scss` / `style-editor.scss` | front-end and editor styles |
| `script.js` / `script-editor.js` (+ `*.asset.php`) | front-end and editor JS |

JS pattern: a class extends `SobolDevBlockBase` and is registered with `SobolDevTheme.constructor.schedule({ class, params: { name }, prio })`. `name` is imported from `./block.json`.

## Commands

```bash
cp .env.sample .env         # then set ACF_PRO_KEY
composer acf-auth           # writes ACF Pro credentials to auth.json
composer install && npm i
docker compose up -d        # back (php-fpm), front (nginx), db, phpmyadmin, smtp (Compose v2, no `docker-compose`)
npm run dev                 # Vite dev server (HMR, https)
npm run build               # production build into dist/
npm run lint:js             # ESLint
npm run lint:styles         # Stylelint
npm run docker:cli          # bash in the back container (has wp-cli)
```

## Code style (from configs)

- **Tabs** for indentation (`.editorconfig`), LF line endings, final newline
- JS: double quotes, **no semicolons**, braces always required on `if`, space before function parens
- SCSS: `stylelint-config-standard`; use `toRem()` for sizes, `breakpoint(sm|md|lg|xl)` for media queries, `--theme-*` CSS variables from `_constants.scss`
- Import aliases: `~css`, `~js`, `~blocks`, `~fonts`, `~assets`
- Text domain: `soboldev`

## Rules

<!-- Rules from the project owner. Added as they come in. -->
