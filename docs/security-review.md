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


## Attack Mitigation, Containment and Recovery Strategy

This section documents the practical response to unauthorized PHP code,
suspicious WordPress account activity, and injected SEO content discovered
during the production security investigation.

The response followed a defensive incident-handling sequence:
detection, evidence preservation, containment, investigation, remediation,
hardening, and verification.

### 1. Detection and initial investigation

Unexpected PHP files were identified inside third-party plugin vendor
directories. Their source code was inspected statically rather than
executed on the production server.

The investigation identified two unauthorized PHP files:

- `wholesale.php` inside the Google Site Kit phpseclib vendor directory.
- `UnderThesisProj.php` inside the WP Smush phpseclib vendor directory.

Both files contained logic consistent with decoding attacker-controlled
input, writing a temporary PHP payload, and executing it.

The investigation also identified a custom EasyPost endpoint with
capabilities to modify WordPress data and deploy runtime PHP code.
Its observed use and associated hidden SEO placements required separate
investigation.

### 2. Evidence preservation

Before removing public access, original suspicious files were preserved
outside the public web root.

The evidence-handling process included:

- Copying and quarantining suspicious files.
- Recording SHA-256 hashes.
- Comparing preserved copies to confirm matching content.
- Recording file metadata and original locations.
- Restricting evidence access to the root account.

Evidence was retained under:

`/root/incident-evidence/`

The original files were not included in this public repository because
they contain operationally dangerous code.

### 3. Containment of unauthorized PHP backdoors

The two suspicious vendor-directory PHP files were moved out of their
publicly accessible locations and preserved in the incident evidence
directory.

The public paths were checked after quarantine and the original public
files were confirmed absent.

This action prevented subsequent HTTP requests from reaching those
specific files through their original public paths.

It does not, by itself, establish that all other persistence mechanisms
or copies were eliminated.

### 4. Containment of the custom EasyPost endpoint

The custom endpoint:

`wp-content/easypost/easypost.php`

was preserved and quarantined outside the public web root.

The original and quarantined copies were checked against the same
SHA-256 hash:

`b148969148247340ba18c289de0cf6ba5214b28f0af85564f2100de9ff14dec8`

The public endpoint path was confirmed absent after quarantine.

Access logs showed requests involving actions such as
`place_homepage_link`, `health`, `begin_reconcile`,
`reconcile_portfolio_admin`, `finish_reconcile`, and `create_post`.

These log entries establish endpoint activity and HTTP responses.
They do not independently establish the exact effect of every request
or identify the operator.

### 5. Removal of injected SEO content

The investigation found hidden homepage placements containing
unrelated gambling SEO links and multilingual spam content.

The associated WordPress option was exported to incident evidence
before removal.

The suspicious option was subsequently deleted.

Two posts containing known spam-domain references were investigated.
Both post IDs were subsequently confirmed absent using WordPress post
lookups.

A later database search for the known spam domains returned no matches
in the searchable WordPress database content.

This is evidence of cleanup for the identified content. It is not
proof that every possible malicious database entry was removed.

### 6. Investigation of unauthorized administrator accounts

Suspicious administrator accounts were identified and compared with
HTTP access-log activity.

The investigation recorded account identifiers, usernames, email
addresses, application-password metadata, timestamps, and associated
request IP information.

Repeated requests to the quarantined backdoor paths were correlated
with suspicious account creation timestamps.

Timestamp correlation is an investigative lead, not conclusive proof
that a particular request created a particular account.

Account removal or demotion, application-password revocation, session
invalidation, and credential rotation must each be independently
verified before being reported as completed.

### 7. Recovery and security hardening

The following controls form the recovery and hardening plan:

- Review every WordPress administrator and privileged account.
- Remove or demote unauthorized accounts after preserving relevant
  evidence.
- Revoke unknown WordPress application passwords.
- Reset credentials for potentially exposed administrative accounts.
- Invalidate existing sessions where compromise is suspected.
- Review WordPress authentication, registration, REST API, and XML-RPC
  exposure.
- Audit must-use plugins, custom endpoints, plugin directories, and
  unexpected PHP files.
- Review PHP files and scheduled tasks for persistence mechanisms.
- Restrict write permissions on application code and plugin directories.
- Review web-server access controls and PHP execution permissions.
- Apply appropriate Cloudflare WAF protections and verify their effect.
- Monitor access logs, account creation, file changes, and suspicious
  requests for recurrence.
- Reassess third-party plugins and restore affected components from
  trusted sources when integrity cannot be established.

These are recovery requirements, not claims that every control has
already been implemented.

### 8. Verification methodology

Each remediation action should have a corresponding verification step.

Examples:

- Confirm quarantined public paths return no accessible PHP resource.
- Compare evidence hashes to establish that preserved originals were
  not altered.
- Query WordPress users to verify the current administrator inventory.
- Inspect application-password records to verify revocation.
- Review active sessions and authentication logs.
- Search database content for known injected domains and placement data.
- Inspect recently modified PHP files and unexpected executable files.
- Review scheduled tasks, must-use plugins, and other persistence
  locations.
- Confirm firewall rules are active and inspect subsequent request logs.
- Continue monitoring for recreated accounts, files, and endpoints.

A successful individual check supports only the specific condition
that it tested. A complete recovery assessment requires the relevant
checks to be completed together.

## Verified Remediation vs. Outstanding Security Checks

This section deliberately separates actions supported by investigation
evidence from work that still requires verification.

### Verified remediation and investigation outcomes

The following actions were completed and supported by recorded evidence:

| Item | Verified outcome |
|---|---|
| Unauthorized `wholesale.php` | Preserved, hash recorded, and quarantined outside the public web root |
| Unauthorized `UnderThesisProj.php` | Preserved, hash recorded, and quarantined outside the public web root |
| Public backdoor paths | Confirmed absent after quarantine |
| Custom EasyPost endpoint | Preserved, hash recorded, and quarantined outside the public web root |
| Hidden SEO placement option | Exported to evidence and deleted from WordPress |
| Identified spam posts | Both investigated post IDs subsequently confirmed absent |
| Known spam-domain search | No matches returned in the later searchable database-content search |
| Forensic evidence | Original suspicious files and supporting evidence preserved outside the public web root |

### Outstanding security checks

The following items must not be described as completed until their results
have been collected and reviewed:

| Check | Required verification |
|---|---|
| Remaining PHP files | Review recently modified and unexpected PHP files across the application |
| EasyPost runtime MU-plugin | Determine whether `easypost-runtime.php` or related runtime code remains necessary, legitimate, and safe |
| Administrator accounts | Verify the current administrator inventory and resolve suspicious accounts |
| Application passwords | Verify unknown or potentially compromised application passwords have been revoked |
| Credentials and sessions | Verify relevant credential rotation and session invalidation |
| Cloudflare protection | Confirm intended WAF rules are active and subsequent traffic is handled as expected |
| Persistence mechanisms | Review scheduled tasks, MU-plugins, drop-ins, custom endpoints, and other persistence locations |
| File integrity | Compare affected WordPress core and plugin files with trusted distributions |
| Recurrence monitoring | Review subsequent logs and file changes for recreated accounts, backdoors, or injected content |

### Recovery status and limitations

The investigation established and remediated several specific unauthorized
files, endpoint exposures, and identified injected content.

However, the outstanding checks above mean that a complete production
security recovery has not yet been demonstrated.

The findings should therefore be presented as a documented production
incident-response exercise with verified containment and cleanup actions,
alongside an explicit list of remaining verification work.

No claim of complete eradication, full system integrity, or absence of
persistence should be made until the corresponding checks have been
completed and documented.

### Additional containment limitation

Quarantining the identified backdoors and custom endpoint removed access
through their original public paths. It did not independently revoke
attacker credentials, invalidate existing sessions, establish that all
attacker access had ended, or prove that no additional persistence existed.

The separate `easypost-runtime.php` MU-plugin remained subject to
investigation at the time this report was prepared. Its presence must not
be interpreted as proof of malicious behavior, but its origin, purpose,
dependencies, and safety require verification.
