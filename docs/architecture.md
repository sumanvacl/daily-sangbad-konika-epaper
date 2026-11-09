# E-Paper Application Architecture

## Overview

The application displayed newspaper page images and used XML metadata
to define clickable areas corresponding to individual news items.

The page-cutting software was a separate third-party component.
It generated the page images, news fragments, thumbnails, and XML
metadata consumed by the PHP application.

## Processing Workflow

1. A newspaper page was designed and prepared for publication.
2. The page was exported as an image for the online edition.
3. The separate cutting software generated image fragments and XML.
4. The PHP application loaded the page image and its XML metadata.
5. PHP SimpleXML parsed the coordinates and image references.
6. The application generated transparent HTML hotspot elements.
7. A reader could select a hotspot to open the corresponding news
   image fragment.
8. Navigation supported stories represented by multiple fragments.

## Main Application Files

| File | Documented responsibility |
|---|---|
| `application/epaper.php` | Displays a page image and generates clickable news areas from page XML. |
| `application/headline.php` | Reads edition-level XML and generates `title.php` metadata. |
| `application/pop_up.php` | Displays a selected news image and its navigation options. |
| `application/print_news.php` | Provides the news-image printing interface. |

These descriptions reflect the source review and known application
workflow. They are not a claim that every historical dependency or
production feature is included in this repository.

## XML Metadata

The page-level XML uses `<high>` as its root element and `<light>`
elements to describe individual clickable regions.

Each `<light>` element contains:

- `image`: image-fragment identifier
- `x`: horizontal coordinate
- `y`: vertical coordinate
- `w`: region width
- `h`: region height

See [`../examples/page01.xml`](../examples/page01.xml).

The edition-level XML contains news entries with fields such as
`pub_id`, `cat_id`, `menu_id`, `news_id`, `title`, `image01`,
and `image02`.

The image fields identify the first and last fragments of a story.
For a single-fragment story, the last-fragment field may be empty.

See [`../examples/edition.xml`](../examples/edition.xml).

All values in these examples are synthetic. They are not copied from
a published newspaper edition.

## Historical Deployment Model

The application used date-based publication directories following
the pattern:

`data/YYYY/MM/DD/`

Page images and page-level XML files were stored under the relevant
edition's `pages/` directory.

Production publication files are deliberately excluded from this
portfolio repository.

## Known Limitations and Verification Status

- The historical application was primarily designed for desktop and
  laptop use, not as a responsive mobile-first interface.
- Large page images and news fragments could take time to load.
- Dedicated image compression, resizing, and caching optimisation
  was not implemented as part of the documented workflow.
- Historical JavaScript and CSS dependencies are not all present
  in this repository.
- A complete standalone installation has not yet been validated.
- No production credentials, analytics identifiers, advertising
  identifiers, or newspaper content are included.
