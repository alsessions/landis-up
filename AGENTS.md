# Project Overview

This project runs Craft CMS 5.

## Environments

- `landis.ddev.site` is the only current project environment and runs locally in DDEV.
- There is no production environment at this time.
- The existing public `landisarboretum.org` site is a legacy/reference site, not production for this project.
- The project will be deployed fresh to a new LAMP stack later.
- Treat all current database, content, asset, and Craft operations as local unless explicitly told otherwise.
- Do not assume that changes need to be synchronized with or applied to the existing public site.

## Stack

- Craft CMS 5
- Twig
- Tailwind CSS
- Vanilla JavaScript
- Alpine.js where appropriate
- DDEV for local development
- A new Apache/PHP LAMP stack for the future deployment
- GitHub for source control
- Craft CMS plugins include Freeform and SEOMatic.

## Development Preferences

- Prefer simple solutions over abstractions.
- Use Twig idiomatically.
- Use Tailwind utility classes directly in templates.
- Do not introduce React, Vue, TypeScript, or build frameworks unless specifically requested.
- Prefer vanilla JavaScript before adding another dependency.
- Use Alpine.js for small interactive components.

## Craft CMS

- Use Craft 5-compatible APIs and patterns.
- Before changing Craft APIs, plugins, PHP requirements, or project config, check compatibility with the installed Craft 5 version.
- Prefer Craft element queries over custom PHP.
- Respect existing section, field, and asset handles.
- Do not rename Craft fields or handles unless explicitly requested.
- Do not edit generated project config manually unless necessary.

## Images

- The project has replaced Image Optimize with native Craft image transforms. Request transforms in Twig where the images are rendered.
- Generate only the dimensions a template actually uses. The established transforms are 1600×900 for heroes, 768×576 for general images, and 576×768 for portrait collection/map images.
- Use `mode: 'crop'`, `quality: 82`, `format: 'webp'`, and `upscale: false`; Craft will honor the asset focal point and cache the transform on demand.
- Keep full-resolution uploads. Use native WebP transforms at quality 82 with upscaling disabled for display. The CKEditor default for inserted images is `article640`. Keep original URLs available for intentional downloads.
- Add `srcset` only when the layout materially benefits from it, and provide an accurate `sizes` value.
- Render meaningful alt text, using the asset alt text with the entry title as a fallback.
- Keep source assets untouched. Treat generated transform directories as reproducible cache files.

```twig
{% set image = entry.featuredImage.one() %}
<img src="{{ image.getUrl({ width: 768, height: 576, mode: 'crop', quality: 82, format: 'webp', upscale: false }) }}" alt="{{ image.alt ?? entry.title }}">
```

## Tailwind

- Use responsive mobile-first classes.
- Keep grids compact.
- Avoid custom CSS when Tailwind utilities can accomplish the same thing.
- Preserve existing Tailwind conventions in the project.

## Git

- Do not commit or push unless explicitly asked.
- Do not rewrite Git history.
- Do not modify `.env`.
- Before destructive Git operations, explain what will be affected.

## Working Style

- Inspect existing code before implementing changes.
- Follow patterns already used elsewhere in the project.
- Keep changes narrowly scoped to the requested task.
- Avoid unrelated cleanup or refactoring.
