# Dependency Inventory

## Scope

This document records the JavaScript, CSS and interface asset
dependencies identified during inspection of the legacy E-Paper
application and its production directory.

The inventory does not imply permission to redistribute third-party
software or assets.

## JavaScript dependencies

| Dependency reference | Source file | Inspection result |
|---|---|---|
| jquery.1.5.1.min.js | epaper.php, pop_up.php | Referenced, but not found at the referenced `js/` path |
| jquery.colorbox-00.js | epaper.php, pop_up.php | Referenced, but not found at the referenced `js/` path |
| jquery00.js | print_news.php | Referenced, but not found at the referenced `js/` path |

### Other JavaScript files found

The production directory contains a `jsepaper/` directory with
historical JavaScript files, including:

- jquery-1.4.3.min.js
- jquery00.js
- jquery.paginate.js
- jquery.preloader.js
- jquery-ui-1.7.2.custom.min.js
- jqFancyTransitions.1.8.js
- main.js
- osx.js

The inspected `jquery00.js` identifies itself as jQuery Tools 1.1.2.
It should not be treated as a replacement for jQuery core.

The exact relationship between these files and the deployed
application dependencies has not been established.

## CSS dependencies

The PHP source references:

- `css/colorbox.css`
- `css/round.css`

Both files were found in the production directory during inspection.

The ColorBox-related CSS image assets were also found.

## Interface image assets

The following referenced interface images were found in the
production directory:

- `images/small-social-i.png`
- `images/dis_sprevious.png`
- `images/sprevious.png`
- `images/printer.png`
- `images/dis_snext.png`
- `images/snext.png`

Their ownership and redistribution permissions have not been
independently established. They are therefore not included in
this portfolio repository.

## Compatibility and licensing notes

- The original application uses legacy JavaScript dependencies.
- Exact deployed versions of the referenced jQuery and ColorBox
  files could not be confirmed.
- No third-party dependency has been copied into this repository
  as part of this inventory.
- Third-party licence terms and redistribution permissions must
  be checked before any dependency or asset is published.
- The original production environment has not been modified.

## Status

Dependency inventory documented from source references and
production-directory inspection.

A fresh installation and full runtime compatibility test have
not been completed.
