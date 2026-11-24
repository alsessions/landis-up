# Project Overview

This project currently runs Craft CMS 4 and is intended to upgrade to Craft CMS 5.

## Environments

- `landis.ddev.site` is the only current project environment and runs locally in DDEV.
- There is no production environment at this time.
- The existing public `landisarboretum.org` site is a legacy/reference site, not production for this project.
- The project will be deployed fresh to a new LAMP stack later.
- Treat all current database, content, asset, and Craft operations as local unless explicitly told otherwise.
- Do not assume that changes need to be synchronized with or applied to the existing public site.

## Stack

- Craft CMS 4 (current), with Craft CMS 5 as the upgrade target
- Twig
- Tailwind CSS
- Vanilla JavaScript
- Alpine.js where appropriate
- DDEV for local development
- A new Apache/PHP LAMP stack for the future deployment
- GitHub for source control
- Craft CMS plugins include, Freeform, Image Optimize and SEOMatic

## Development Preferences

- Prefer simple solutions over abstractions.
- Use Twig idiomatically.
- Use Tailwind utility classes directly in templates.
- Do not introduce React, Vue, TypeScript, or build frameworks unless specifically requested.
- Prefer vanilla JavaScript before adding another dependency.
- Use Alpine.js for small interactive components.

## Craft CMS

- Keep existing code compatible with the current Craft 4 installation.
- Prepare new code and project changes for Craft 5 where practical; avoid introducing Craft 4-only APIs or patterns.
- Before changing Craft APIs, plugins, PHP requirements, or project config, check their Craft 5 compatibility and upgrade implications.
- Treat the Craft 5 upgrade as a separate planned change; do not assume the site has already been upgraded.
- Prefer Craft element queries over custom PHP.
- Respect existing section, field, and asset handles.
- Do not rename Craft fields or handles unless explicitly requested.
- Do not edit generated project config manually unless necessary.

## Images

- Prefer native Craft transforms requested in Twig over asset-wide ImageOptimize fields.
- Generate only the dimensions a template actually uses. The established transforms are 1600×900 for heroes, 768×576 for general images, and 576×768 for portrait collection/map images.
- Use `mode: 'crop'`, `quality: 82`, `format: 'webp'`, and `upscale: false`; Craft will honor the asset focal point and cache the transform on demand.
- Use WebP directly unless a fallback is specifically required. A fallback or each additional `srcset` width creates another transform.
- Add `srcset` only when the layout materially benefits from it, and provide an accurate `sizes` value.
- Render meaningful alt text, using the asset alt text with the entry title as a fallback.
- Keep source assets untouched. Treat generated transform directories as reproducible cache files.
- Do not add an image optimization field to an entire asset volume without a demonstrated template requirement.

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
