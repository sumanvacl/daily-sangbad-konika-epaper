# Forensic Samples — Production Security Incident

> **WARNING: SECURITY INCIDENT EVIDENCE**
>
> This directory documents unauthorized PHP code discovered during the
> investigation of the Daily Sangbad Konika production WordPress platform.
>
> The files in this directory are sanitized forensic documentation.
> They are not application components, plugins, or installation files.

## Specimen

`wholesale.php.txt` is a non-executable forensic reconstruction based on
the observed behavior of the original unauthorized PHP file.

The original file was found inside a third-party plugin's phpseclib
directory. It was quarantined and preserved outside the public web root.

## Why the original is not included

The original file accepted attacker-controlled input, decoded a payload,
wrote it to a temporary PHP file, and executed it.

Publishing that operational file would distribute a working backdoor.
The original evidence remains privately preserved for forensic integrity.

## Evidence identification

Original filename: `wholesale.php`

Original SHA-256:

`8430f6319cb73a9c4b3abd62f900cba48cad6587ad32d486d37737418ff39f83`

Original location:

`wp-content/plugins/google-site-kit/third-party/phpseclib/phpseclib/phpseclib/Crypt/DSA/Formats/Signature/wholesale.php`

The path is recorded for investigation context. It does not imply that
the legitimate third-party plugin itself was responsible.

## Safety

- Do not rename the specimen to `.php`.
- Do not upload original incident evidence to a public web directory.
- Do not execute suspicious PHP files to test them on a production server.
- Preserve original evidence and hashes before remediation.
