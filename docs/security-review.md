# Security Review

## Scope

This is a preliminary, source-code-based review of the legacy PHP
E-Paper application. It is not a penetration test or a confirmation
that the production system was exploited.

The review covers:
- application/epaper.php
- application/headline.php
- application/pop_up.php
- application/print_news.php

## Findings

### 1. Unvalidated request parameters

**Files:** `epaper.php`, `pop_up.php`, `print_news.php`

Request parameters such as `date`, `page_num`, and `img_name`
are used in filesystem paths without clear strict allowlist validation.

**Potential impact:** Path traversal or unintended file access.

**Recommended remediation:**
- Validate dates against an exact expected format.
- Accept only numeric page identifiers within an expected range.
- Restrict image filenames to an approved filename pattern.
- Resolve paths and verify that they remain inside the intended data directory.
- Do not rely on string replacement as path validation.

### 2. Generated executable PHP from XML data

**File:** `headline.php`

XML values are written directly into a generated PHP file (`title.php`),
which is subsequently included by the main application.

**Potential impact:** Malformed generated code and possible code injection
if untrusted XML data can reach this process.

**Recommended remediation:**
- Avoid generating executable PHP from XML values.
- Store parsed metadata in arrays or another non-executable data format.
- Validate XML structure and values before processing.

### 3. Insufficient output escaping

**Files:** `epaper.php`, `pop_up.php`, `print_news.php`

Values derived from XML or request parameters are inserted into HTML
attributes and markup without consistent context-aware escaping.

**Potential impact:** HTML injection or cross-site scripting if an
attacker can control the relevant values.

**Recommended remediation:**
- Use `htmlspecialchars()` with UTF-8 and quote escaping for HTML text
  and attribute values.
- Validate URL and filename values separately.
- Avoid constructing inline JavaScript from untrusted values.

### 4. Weak file and image validation

**Files:** `pop_up.php`, `print_news.php`

Image paths are passed to `getimagesize()` without clear prior
validation of the expected directory and filename.

**Potential impact:** Unintended file access and avoidable processing
of unexpected files.

**Recommended remediation:**
- Validate the requested image against the edition metadata.
- Restrict access to approved image directories.
- Handle missing or invalid images explicitly.

### 5. Incomplete error handling

**Files:** `epaper.php`, `pop_up.php`, `headline.php`, `print_news.php`

Some file and XML operations do not show consistent error handling.
Error suppression is also used in places.

**Potential impact:** Difficult troubleshooting and unpredictable
behaviour when files are missing or malformed.

**Recommended remediation:**
- Check file-operation return values.
- Handle XML parsing failures explicitly.
- Log technical errors without exposing filesystem paths or
  sensitive information to visitors.

## Additional observations

- `pop_up.php` appears to use `$img_name` before assigning it from
  the request parameter. This should be checked during runtime testing.
- The historical application uses legacy PHP and JavaScript patterns.
  Compatibility with current PHP versions has not been established.
- The review does not establish that any vulnerability was exploited.
- No live penetration test or production modification was performed.

## Review status

Preliminary static source review only.

The application should not be described as security-audited,
production-hardened, or fully secure on the basis of this review.
