# Technical Forensic Analysis: `wholesale.php`

## 1. Discovery

An unauthorized PHP file named `wholesale.php` was discovered inside a
third-party plugin vendor-library directory:

`wp-content/plugins/google-site-kit/third-party/phpseclib/phpseclib/phpseclib/Crypt/DSA/Formats/Signature/wholesale.php`

The file was not identified as a legitimate phpseclib library component.

## 2. Observed technical behavior

Static examination of the preserved original identified the following
execution chain:

1. Read attacker-supplied POST parameter `value`.
2. Convert the supplied hexadecimal representation into bytes.
3. Apply an XOR transformation using key `2`.
4. Write the transformed content to a temporary `.data` file.
5. Include the temporary file, causing its PHP content to execute.
6. Unlink the temporary file and terminate execution.

## 3. Security impact

This behavior is consistent with a PHP payload loader and can enable
remote code execution through HTTP requests to the backdoor.

Potential consequences include:

- Execution of arbitrary PHP payloads.
- Creation of unauthorized WordPress administrator accounts.
- Modification of WordPress data or files.
- Installation of additional persistence mechanisms.
- Unauthorized access to application data and credentials.

These are potential capabilities. The presence of this file alone does
not establish that every listed consequence occurred.

## 4. Incident evidence

Original SHA-256:

`8430f6319cb73a9c4b3abd62f900cba48cad6587ad32d486d37737418ff39f83`

The original file was preserved outside the public web root and its
hash was recorded. The public copy was quarantined.

HTTP access logs also recorded POST requests to the original endpoint.
Those records establish requests to the path, but do not independently
prove the exact payload executed or the identity of the operator.

## 5. Forensic methodology

The investigation approach included:

- Identifying unexpected PHP files in plugin vendor directories.
- Examining source code without executing the suspicious file.
- Recording file metadata and SHA-256 hashes.
- Correlating HTTP access-log timestamps with suspicious account activity.
- Preserving original evidence outside the public web root.
- Removing public access by quarantining the file.
- Verifying that the original public path no longer existed.

## 6. Limitations

Timestamp correlation is not proof of causation or attribution.

The exact payloads supplied in historical requests were not established
by the access-log entries alone. The incident investigation should not
claim a specific attacker identity or a complete compromise timeline
without additional evidence.

## 7. Lessons

- Vendor-library directories must not be assumed immune to compromise.
- Unexpected PHP files require investigation.
- Temporary-file deletion does not neutralize code that has already run.
- Hashes and preserved originals support reproducible forensic analysis.
- HTTP access logs should be correlated with application and database
  events, while keeping correlation distinct from proof.
