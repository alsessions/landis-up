I audited the filesystem, current Craft database relations, embedded asset references, templates, and transform index. No project files or content were changed.

## What `/web/assets` contains

| Area | Originals | Generated/cache | Size |
|---|---:|---:|---:|
| `images` | 2,532 | 708 | ~7.12 GB |
| `newsletters` | 277 | 762 | ~378 MB |
| `sponsors` | 29 | 252 | ~4.3 MB |
| `audio` | 22 | 0 | ~34.8 MB |
| Demo bundles | 12 files | — | negligible |
| **Total** |  | **4,594 files** | **~7.36 GB** |

The directory is overwhelmingly source JPEGs:

- 2,414 `.jpg`: ~4.29 GB
- 1,083 `.jpeg`: ~2.37 GB
- 351 `.png`: ~301 MB
- 175 PDFs: ~355 MB
- 23 MP3s: ~35.8 MB
- 513 WebPs: only ~8.8 MB

Generated transform directories contain 1,722 files totaling about 68 MB. The storage problem, if that is where this is heading, is the originals—not the current transform cache.

## Assets associated with the live frontend

Combining current live Craft relations with asset references embedded in live entry content gives:

| Volume | In Craft | Frontend-associated | Not currently associated |
|---|---:|---:|---:|
| Images | 2,532 | 1,815 | 717 |
| Newsletters | 276 | 245 | 31 |
| Sponsors | 28 | 20 | 8 |
| Audio | 22 | 22 | 0 |

“Frontend-associated” means referenced by a live element field or embedded into live entry content. It is a substantially stronger signal than merely existing in Craft, although it does not prove that every asset is downloaded on every visit.

The primary use breaks down like this:

- News:
  - 656 distinct gallery/page images
  - 295 featured images
  - 286 distinct images embedded directly in article bodies
- General pages:
  - 364 gallery/page images
  - 18 featured images
  - 10 embedded body images
- Events:
  - 175 featured images
  - 163 embedded body images
  - 13 sponsor images from the main Images volume
- Newsletters:
  - 127 PDFs
  - 122 PDF-cover images
- Galleries:
  - 24 gallery images
- Native plant/audio tour:
  - 18 featured images
  - 17 MP3s
- Sponsors page:
  - 20 sponsor logos
- Bird blog:
  - 36 embedded images
  - 5 featured images
- Audio section:
  - 5 MP3s
- Collections:
  - 7 gallery images
  - 5 featured images
- Maps:
  - 3 featured images plus directly linked PDFs

All 22 audio files are accounted for: 17 native-plant tour tracks and 5 general audio entries.

## What frontend transforms are actually requested

The current templates use these variants:

| Variant | Where |
|---|---|
| `1600×900` WebP | Homepage slides and section/article heroes |
| `768×576` WebP | Support and rentals backgrounds |
| `576×768` WebP | Collection and map portraits |
| `640×480` WebP | Event detail images |
| `500×500` WebP | Bird-blog cards and newsletter fallbacks |
| `square500` | News, event, map, collection and gallery cards |
| `article640` or 640px-wide WebP | Article bodies, newsletter covers and news galleries |
| 400px-wide WebP | Sponsor logos |
| `1920×1569` WebP | Homepage newsletter background |

Examples are visible in [index.twig](/home/al/Sites/landis/templates/index.twig:86), [pages/_entry.twig](/home/al/Sites/landis/templates/pages/_entry.twig:12), [events/_entry.twig](/home/al/Sites/landis/templates/events/_entry.twig:54), and [collections/_entry.twig](/home/al/Sites/landis/templates/collections/_entry.twig:13).

The transform index currently has these notable cached counts:

- `article640`: 180
- `square500`: 95
- `maxWidth1200`: 34
- `article400Wide`: 30
- `square300`: 26
- 400px WebP: 20
- `1600×900` WebP: 18
- 500px-square WebP: 6
- 640px-wide WebP: 6
- `768×576` WebP: 2
- `1920×1569` WebP: 1
- `640×480` WebP: 1

The `maxWidth1200`, `article400Wide`, `square300`, and `articleCropped` variants are primarily legacy embedded-content transforms rather than current structural template choices.

## Mobile variants

There are no separate mobile image files in the current templates.

Specifically:

- No template uses `srcset`.
- No template uses `<picture>`.
- Live entry content contains no stored `srcset` or `<picture>` markup.
- Desktop and mobile receive the same image URL.
- Tailwind only changes how that same file is displayed.

For example, listing cards use `w-32 md:w-52`, but both sizes load the same 500×500 transformed file. Galleries likewise load `square500` thumbnails on mobile and desktop, then link to the full original for the lightbox. See [news/index.twig](/home/al/Sites/landis/templates/news/index.twig:31) and [pages/_entry.twig](/home/al/Sites/landis/templates/pages/_entry.twig:51).

So there is no hidden mobile-variant collection that needs to be separately preserved. The tradeoff is that phones often download a larger image than their rendered size requires.

## Other generated files that are not normal page imagery

A lot of the small transform directories are not frontend variants:

- `20–240px` transforms are mostly Craft control-panel thumbnails.
- `1200×630`, `800×418`, and `800×800` sets are SEOMatic/social-preview imagery. They may appear in page metadata but not visibly in the page body.
- Thousands of old Image Optimize URLs remain in asset metadata, but the current Twig templates do not output Image Optimize fields.
- A URL appearing inside Image Optimize’s stored JSON does not mean the corresponding file is being used on the frontend.

This distinction matters: counting every URL stored in asset metadata produced 9,473 supposed variants, but 9,244 of those came from Image Optimize records rather than rendered entry content.

## Directly hardcoded frontend files

These local files are explicitly referenced:

- `images/IMG_0432.JPG` — fallback hero
- `images/landis-big-view.jpg` — homepage background
- `images/app-store.jpg` — homepage badge
- `images/play-store.jpg` — homepage badge
- `newsletters/vector-pdf.png` — newsletter PDF fallback

The header does **not** use the local `images/landis-new-logo-edit.png`. It loads the logo from the legacy public domain instead:

[templates/_partials/_header.twig](/home/al/Sites/landis/templates/_partials/_header.twig:14)

That makes the local logo copy currently unused by the frontend and leaves the local site dependent on the old public site for its header logo.

## Demo and legacy bundles

- `web/assets/freeform-demo` is referenced by the Freeform demo templates.
- `web/assets/calendar-demo` is not referenced anywhere in current code.
- `web/assets/events` contains the same old calendar-demo CSS/JS/images and is also not referenced.

Those last two are clearly separate from Craft’s Events image content.

## Confirmed broken raw frontend paths

Twelve live entries contain raw `/assets/...` URLs rather than Craft asset reference tags. Six referenced files are currently missing:

- `/news/2017-annual-report`
- `/news/2019-annual-report`
- `/news/2020-annual-report`
- `/news/2022-annual-report`
- Two Bluebird Trail news entries using the missing `Landis-Bluebird-Trail.jpeg`

The other raw paths, including both map pages and several newsletter files, are present.

These are genuine frontend-content problems because Craft cannot relocate or resolve a raw URL the way it can resolve `{asset:…}` references.

## Bottom line

The frontend-relevant core is approximately:

- 1,815 image assets
- 245 newsletter assets
- 20 sponsor logos
- All 22 audio files
- A relatively small set of current template transforms
- Five hardcoded local frontend files

There are roughly 756 Craft assets with no current live relation or embedded asset reference, plus the unreferenced calendar demo bundles. Those are candidates for a deeper unused-assets review, but not automatic deletion candidates: archival files, direct raw URLs, downloadable documents, and coherent event/photo collections still need to be accounted for individually.

Also, despite the supplied project overview saying Craft 4, the installed checkout is currently Craft CMS **5.12.4** according to `composer.lock`.
