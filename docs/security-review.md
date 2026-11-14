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

---

## Production Security Incident Investigation and Response

In November 2026, I conducted a practical security investigation involving
the production WordPress environment associated with the newspaper platform.

### Investigation findings

- Identified two unauthorized PHP files inside third-party plugin vendor
  directories. Static analysis revealed mechanisms capable of executing
  externally supplied PHP payloads.
- Audited suspicious WordPress administrator accounts and application
  passwords, comparing account activity with Apache access logs.
- Investigated hidden SEO spam stored in a custom WordPress database option.
- Traced the database option to a custom PHP endpoint that supported
  homepage placement management and other remote operations.
- Examined endpoint capabilities involving post creation, application
  passwords, runtime PHP deployment and update operations.

### Containment and evidence preservation

- Preserved the original suspicious PHP files outside the public web root.
- Recorded SHA-256 hashes to verify the integrity of preserved evidence.
- Removed the identified malicious database option and identified spam posts.
- Quarantined the custom PHP endpoint outside the public web directory.
- Searched the database for the known spam-domain indicators.

### Problem-solving skills demonstrated

- PHP source-code analysis and request-handler tracing.
- WordPress administrator and application-password auditing.
- Apache access-log analysis and timestamp correlation.
- Database investigation using WP-CLI.
- Linux filesystem inspection, file permissions and ownership management.
- Evidence preservation, hash verification and incident containment.

### Investigation limitations

The observed timing correlations do not independently prove the exact
initial access method or identify the responsible actor. The investigation
does not constitute a complete forensic audit or certify that all
persistence mechanisms have been eliminated. Additional verification
and credential security measures remain necessary.

This incident response work demonstrates practical troubleshooting,
analytical reasoning and production server administration. It is
documented separately from the E-Paper application's historical
source-code review.


---

## Remediation and Verification Status

**Status recorded:** 14 November 2026

### Completed and verified

- Two unauthorized PHP backdoor files were removed from their original
  public locations and preserved in a protected evidence directory.
- SHA-256 checksums confirmed the integrity of the preserved backdoor copies.
- The identified malicious homepage-placement database option was deleted.
- The two identified spam post IDs were confirmed absent after deletion.
- Searches for the known spam-domain indicators returned no matches in
  the searchable WordPress database content.
- The custom EasyPost PHP endpoint was removed from its public location
  and quarantined.
- SHA-256 checksums confirmed the integrity of the preserved endpoint copies.

### Outstanding verification

The following areas require further investigation or confirmation:

- Inspect recently modified PHP files and determine whether additional
  runtime files were deployed by the custom endpoint.
- Review the remaining EasyPost runtime MU-plugin and its dependencies.
- Audit all administrator accounts and application passwords, including
  suspicious accounts and any recreated accounts.
- Confirm that unauthorized application passwords and login sessions
  have been revoked.
- Confirm rotation of WordPress, database, hosting and other potentially
  exposed credentials.
- Review Cloudflare security rules and verify that appropriate protections
  are active.
- Perform a broader persistence review before declaring the environment clean.

### Security conclusion

The actions above represent verified containment of specific identified
malicious files, database content and a custom PHP endpoint.

They do not establish the complete initial access method, prove that all
unauthorized access has stopped, or certify that the production server
is free of additional persistence mechanisms.

The incident remains under investigation until the outstanding checks
have been completed and verified.

