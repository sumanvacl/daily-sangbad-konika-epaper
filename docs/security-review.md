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


---

## Technical Incident Investigation and Forensic Methodology

### 1. Incident Discovery and Initial Indicators

The investigation began after identifying suspicious WordPress administrator
accounts, unexpected application passwords and account activity that did not
match expected administrative operations.

Instead of treating each symptom independently, I investigated the relationship
between WordPress user records, PHP files, database content and Apache access logs.

The initial investigation included:

- Reviewing administrator accounts and identifying unexpected accounts.
- Checking whether suspicious accounts appeared to be recreated.
- Inspecting application-password records and their associated metadata.
- Comparing account creation timestamps with suspicious HTTP requests.
- Examining unusual requests to PHP files inside WordPress plugin directories.
- Reviewing unexpected database content and hidden homepage placements.

This approach helped establish connections between application-level activity
and suspicious server-side files.

### 2. PHP Backdoor Discovery and Static Code Analysis

Two unauthorized PHP files were identified inside vendor-library directories
associated with installed WordPress plugins.

The files were:

- `wholesale.php`, located inside a Google Site Kit vendor-library directory.
- `UnderThesisProj.php`, located inside a WP Smush vendor-library directory.

Static inspection revealed that both files accepted externally supplied
request data, decoded that data and used temporary PHP files to execute
the resulting payload.

The investigation identified the following behavior:

- One file accepted a POST parameter named `value`.
- The other accepted a request parameter named `obj`.
- Both implemented custom decoding logic.
- Both wrote decoded content to temporary files.
- Both included the temporary files as PHP code and subsequently removed them.

These behaviors represented an arbitrary PHP code-execution capability.

The files were inspected as source code rather than executed as part of
the investigation. Their locations inside legitimate plugin vendor
directories made it important to distinguish the unauthorized files
from the legitimate third-party libraries surrounding them.

### 3. Filesystem Forensics and Evidence Preservation

Before containment, the suspicious files were preserved for later analysis.

The evidence-handling process included:

- Recording file locations and relevant filesystem metadata.
- Inspecting ownership, permissions and modification timestamps.
- Preserving original copies outside the public web directory.
- Recording SHA-256 cryptographic hashes.
- Comparing the hashes of the preserved copies.
- Restricting access to the evidence directory and files.

The preserved copies were stored under a root-controlled incident evidence
directory rather than inside the publicly accessible WordPress installation.

The SHA-256 hashes of the two preserved backdoors were verified:

- `wholesale.php`: `8430f6319cb73a9c4b3abd62f900cba48cad6587ad32d486d37737418ff39f83`
- `UnderThesisProj.php`: `704af01dd555801cb0b2731a10389fd7a49d55eba3b5a3cf3375fe2e5d3a2633`

Matching hashes between the original evidence copies confirmed that the
preserved files retained their original contents at the time of verification.

### 4. Apache Access-Log Analysis and Timeline Correlation

Apache access logs were examined to understand whether the suspicious
PHP files had received external requests.

The investigation considered:

- Request timestamps.
- HTTP methods and requested paths.
- Response status codes.
- Request frequency.
- Client IP information available through the server's proxy-aware logging.
- Correlation with WordPress account creation timestamps.

Repeated POST requests to the suspicious PHP files were found in the
access logs.

Some requests occurred at timestamps closely corresponding to the
creation of suspicious administrator accounts.

This was an important investigative lead. However, timestamp correlation
alone does not prove which individual request created an account or
identify the original attacker.

The investigation therefore treated access-log evidence, WordPress
database records and PHP source analysis as complementary evidence
rather than relying on a single indicator.

### 5. WordPress Account and Application-Password Investigation

The WordPress user database and application-password records were
examined to identify unauthorized access mechanisms.

The investigation included:

- Listing administrator accounts.
- Reviewing suspicious usernames and email addresses.
- Checking account creation timestamps.
- Inspecting application-password names and metadata.
- Comparing available last-used timestamps and IP information.
- Checking for duplicate or recreated suspicious accounts.

Application passwords were treated as separate authentication credentials
that required independent auditing and revocation where unauthorized.

The presence of suspicious accounts and application passwords demonstrated
the importance of investigating WordPress authentication records alongside
server access logs.

### 6. Database Investigation and Hidden SEO Spam

The investigation identified a custom WordPress option named
`easypost_homepage_placements`.

Its stored HTML contained multiple links to unrelated gambling websites.
The HTML used off-screen positioning and other visibility-hiding techniques
to make the links invisible to ordinary visitors while remaining present
in the page output.

The investigation traced the option to a custom PHP component that
generated and rendered homepage placements.

The response included:

- Exporting the database option as JSON evidence.
- Inspecting its stored HTML and placement metadata.
- Recording the associated update timestamps.
- Searching WordPress database content for the identified spam domains.
- Removing the identified malicious option.
- Deleting the identified spam posts.
- Repeating the known-domain search after cleanup.

The subsequent search returned no matches for the known spam-domain
indicators in the searchable WordPress database content.

This result was limited to the known indicators and searchable content;
it was not proof that every possible malicious database modification
had been discovered.

### 7. Investigation of the Custom EasyPost Endpoint

A separate custom PHP endpoint was discovered at:

`wp-content/easypost/easypost.php`

Source-code inspection identified functionality beyond ordinary homepage
placement rendering.

The code included operations involving:

- Homepage link and image placement.
- WordPress post creation.
- Application-password management.
- Runtime PHP deployment and activation.
- Custom runtime reconciliation.
- Endpoint update and release mechanisms.

Apache logs showed repeated direct requests to this endpoint, including
requests associated with placement and post-creation actions.

The endpoint's combination of remote content-management and PHP runtime
deployment capabilities presented a significant security risk.

The endpoint was preserved and then moved outside the public web directory.
The original and quarantined copies were compared using SHA-256.

The matching hash was:

`b148969148247340ba18c289de0cf6ba5214b28f0af85564f2100de9ff14dec8`

The public endpoint's original location was checked and confirmed absent
after quarantine.

### 8. Containment and Removal Procedure

The response prioritized preserving evidence before removing confirmed
malicious or exposed components.

The completed containment actions included:

1. Preserving the two unauthorized PHP backdoors.
2. Moving both backdoors out of their original public locations.
3. Restricting access to the preserved evidence.
4. Verifying the integrity of the preserved files using SHA-256.
5. Exporting the suspicious homepage-placement option.
6. Deleting the identified malicious database option.
7. Removing the identified spam posts.
8. Preserving the custom EasyPost endpoint.
9. Quarantining the endpoint outside the public web directory.
10. Verifying that the public endpoint no longer existed.

Quarantine was preferred over irreversible deletion so that the original
source remained available for further forensic examination.

### 9. Verification and Remaining Investigation

Containment of identified files and database content does not establish
that the entire production environment is secure.

The following checks remain necessary before declaring the incident fully
resolved:

- Inspect recently modified PHP files for additional deployed runtime code.
- Investigate the remaining EasyPost runtime MU-plugin and its dependencies.
- Review other persistence mechanisms and unexpected scheduled tasks.
- Complete the audit of administrator accounts and application passwords.
- Confirm revocation of unauthorized credentials and active sessions.
- Confirm rotation of potentially exposed WordPress, database and hosting
  credentials.
- Review Cloudflare protections and relevant server access controls.
- Continue monitoring authentication and PHP endpoint activity.

### 10. Technical Skills Demonstrated

This investigation required practical use of:

| Technical area | Skills applied |
|---|---|
| PHP | Static source analysis, request handling, payload decoding and file-operation tracing |
| WordPress | Administrator auditing, application passwords, options and post management |
| Linux | Filesystem inspection, ownership, permissions and evidence preservation |
| Apache | Access-log analysis, HTTP request investigation and timestamp correlation |
| Database | WP-CLI, option exports, content searches and cleanup verification |
| Forensics | SHA-256 integrity verification, evidence retention and timeline analysis |
| Security response | Backdoor containment, endpoint quarantine and verification |

### 11. Forensic Limitations and Professional Conclusion

The investigation established the presence of unauthorized PHP execution
mechanisms, suspicious endpoint activity and hidden SEO spam.

It also established that specific malicious files and database content
were removed or quarantined and that their preserved evidence copies
passed the recorded hash comparisons.

However, the available evidence does not independently establish the
initial compromise method, the identity of the responsible actor or
the complete scope of possible persistence.

This work represents practical production incident investigation,
source-code analysis, evidence preservation and containment. It should
not be represented as a completed independent forensic audit,
penetration test or certification that the production environment
is completely clean.

