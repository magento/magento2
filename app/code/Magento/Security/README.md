# Security

**Security** management module
_Main features:_

1. Added support for simultaneous admin user logins with ability to enable/disable the feature, review and disconnect the list of current logged in sessions
2. Added password complexity configuration
3. Enhanced security to prevent account takeover for sessions opened on public computers and similar:
    * Password confirmation for all critical flows (like password, email change)
    * Lockout of the account after a configurable amount of incorrect login/password entries
    * Password Change functionality is enhanced by email and/or ip address by frequency, number and requests per hour limitation
    * Change password link becomes invalid after the first use or after a configurable amount of time
    * Password/email change notifications are sent to both old and new email addresses
4. Fixed: the password is not being reset until the new password is submitted via the form available by a one time link sent to the email address
