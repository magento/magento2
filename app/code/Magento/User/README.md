# Magento_User module

The Magento_User module enables admin users to manage and assign roles to administrators and other non-customer users, reset user passwords, and invalidate access tokens.

Different roles can be assigned to different users to define their permissions.

For admin passwords, it enables setting lifetimes and locking them when expired or when a specified number of failures have occurred. It allows preventing password brute force attacks for system backend.

## Installation details

Before installing this module, note that the Magento_User is dependent on the following modules:

- Magento_Authorization
- Magento_Backend
- Magento_Config
- Magento_Email
- Magento_Integration
- Magento_Security
- Magento_Store
- Magento_Ui

Before disabling or uninstalling this module,note the following dependencies:

- Magento_AsynchronousOperations
- Magento_EncryptionKey
- Magento_Integration
- Magento_ReleaseNotification
- Magento_Shipping
- Magento_Tax

For information about enabling or disabling a module, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Structure

- `Observer/` - directory that contains model for Authentication, ForceAdminPasswordChange, and TrackAdminNewPassword observer.
- `Setup/` - directory that contains patch data file to upgrade password hashes and serialized fields.

For information about a typical file structure of a module, see [Module file structure](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/build/module-file-structure.html#module-file-structure).

### Layouts

This module introduces the following layouts and layout handles in the directories:

- `view/adminhtml/layout`:
    - `adminhtml_auth_forgotpassword`
    - `adminhtml_auth_login`
    - `adminhtml_auth_resetpassword`
    - `adminhtml_locks_block`
    - `adminhtml_locks_grid`
    - `adminhtml_locks_index`
    - `adminhtml_user_edit`
    - `adminhtml_user_grid_block`
    - `adminhtml_user_index`
    - `adminhtml_user_role_editrole`
    - `adminhtml_user_role_editrolegrid`
    - `adminhtml_user_role_grid_block`
    - `adminhtml_user_role_index`
    - `adminhtml_user_role_rolegrid`
    - `adminhtml_user_rolegrid`
    - `adminhtml_user_rolesgrid`

For more information about a layout, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).
