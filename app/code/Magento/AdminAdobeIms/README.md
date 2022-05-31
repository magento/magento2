# Magento_Admin_Adobe_Ims module
The Magento_Admin_Adobe_Ims module contains integration with Adobe IMS for backend authentication.

For information about module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

# CLI command usage:
## bin/magento admin:adobe-ims:enable
Enables the AdminAdobeIMS Module. \
Required values are `Organization ID`, `Client ID`, `Client Secret` and `2FA enabled`

### Argument Validation
On enabling the AdminAdobeIMS Module, the input arguments will be validated. \
The pattern for the validation are configured in the di.xml

```xml
<type name="Magento\AdminAdobeIms\Service\ImsCommandValidationService">
    <arguments>
        <argument name="organizationIdRegex" xsi:type="string"><![CDATA[/^([A-Z0-9]{24})(@AdobeOrg)?$/i]]></argument>
        <argument name="clientIdRegex" xsi:type="string"><![CDATA[/[^a-z_\-0-9]/i]]></argument>
        <argument name="clientSecretRegex" xsi:type="string"><![CDATA[/[^a-z_\-0-9]/i]]></argument>
        <argument name="twoFactorAuthRegex" xsi:type="string"><![CDATA[/^y/i]]></argument>
    </arguments>
</type>
```

We check if the arguments are not empty, as they are all required. 

For the Organization ID, Client ID and Client Secret, we check if they contain only alphanumeric characters. \
Additionally for the Organization ID, we check if it matches 24 characters and optional has the suffix `@AdobeOrg`. But we only store the ID and ignore the suffix.
Also make sure 2FA is enabled for the Organization in Adobe Admin Console.

## bin/magento admin:adobe-ims:disable
Disables the AdminAdobeIMS Module.
When disabling, the `Organization ID`, `Client ID` and `Client Secret` values will be deleted from the config.

## bin/magento admin:adobe-ims:status
Shows if the AdminAdobeIMS Module is enabled or disabled

## bin/magento admin:adobe-ims:info
Example of getting data if Admin Adobe Ims module is enabled:\
Client ID: 1234567890a \
Organization ID: 1234567890@org \
Client Secret configured

If Admin Adobe Ims module is disabled, cli command will show message "Module is disabled"

# Admin Login design
The admin login design changes when the AdminAdobeIms module is enabled and configured correctly via the CLI command.
We have added the customer layout handle `adobe_ims_login` to deal with all the design changes.
This handle is added via `\Magento\AdminAdobeIms\Plugin\AddAdobeImsLayoutHandlePlugin::afterAddDefaultHandle`.

The layout file `view/adminhtml/layout/adobe_ims_login.xml` adds:
* The bundled [Adobe Spectrum CSS](https://opensource.adobe.com/spectrum-css/).
* New classes to current Magento html items,
* Our new "Login with Adobe ID" button template,
* A custom error message wrapper,

We have included the minified css and the used svgs from Spectrum CSS with our module, but you can also use npm to install the latest versions.
To rebuild the minified css run the command `./node_modules/.bin/postcss -o dist/index.min.css index.css` after npm install from inside the web directory.

# AdminAdobeIMS Callback
For the AdobeIMS Login we provide a redirect_uri on the request. After a successful Login in AdobeIMS, we get redirected to provided redirect_uri.

In the ImsCallback Controller we get the access_token and then the user profile.
We then check if the assigned organization is valid and if the user does exist in the Magento database, before we complete the user login in Magento.

If there went something wrong during the authorization, the user gets redirected to the admin login page and an error message is shown.

# Organization ID Validation
During the authorization we check if the configured `Organization ID` provided on the enabling CLI command is assigned to the user.

In the profile response from Adobe IMS must be a `roles` array. There we have all assigned organizations to the user.

We compare if the configured organization ID does exist in this array and also the structure of the organization ID is valid.

# Admin Backend Login
Login with the help Adobe IMS Service is implemented. The redirect to Adobe IMS Service is performed-
The redirect from Adobe IMS is done to \Magento\AdminAdobeIms\Controller\Adminhtml\OAuth\ImsCallback controller.

The access code comes from Adobe, the token response is got on the basis of the access code,
client id (api key) and client secret (private key). 
The token response access token is used for getting user profile information. 
If this is successful, the admin user will be logged in and the access tokens is added to session as well as token_last_check_time value.

# ACCESS_TOKEN saving in session and validation
When AdminAdobeIms module is enabled, we check each 10 minutes if ACCESS_TOKEN is still valid.
For this when admin user login and when session is started, we add 2 extra variables to the session:
token_last_check_time is current time
adobe_access_token is ACCESS_TOKEN that we receive during authorization

There is a plugin \Magento\AdminAdobeIms\Plugin\BackendAuthSessionPlugin where we check if token_last_check_time was updated 10 min ago.
If yes, then we make call to IMS to validate access_token.
If token is valid, value token_last_check_time will be updated to current time and session prolong.
If token is not valid, session will be destroyed.

# Admin Backend Logout
The logout from Adobe IMS Service is performed when Magento Admin User is logged out.
It's triggered by the event `controller_action_predispatch_adminhtml_auth_logout`

We do external LogOut by call to IMS. Session revoke is standard Magento behavior

# Admin Created Email
We created an Observer for the `admin_user_save_after` event. \
There we check if the customer object is newly created or not. \
When a new admin user got created in Magento, he will then receive an email with further information on how to login.

We use the `admin_emails_new_user_created_template` Template for the content, and also created a new header and footer template for the Admin Adobe IMS module templates.
They are called `admin_adobe_ims_email_header_template` and `admin_adobe_ims_email_footer_template`.

The notification mail will be sent inside our `AdminNotificationService` where we can add and modify the template variables.

# Error Handling
For the AdminAdobeIms Module we have two specific error messages and one general error message which are shown on the Admin Login page when an error occured.

### AdobeImsTokenAuthorizationException
Will be thrown when there was an error during the authorization. \
e. g. a call to AdobeIMS fails or there was no matching admin found in the Magento database.

### AdobeImsOrganizationAuthorizationException
Will be thrown when the admin user who wants to log in does not have the configured organization ID assigned to his AdobeIMS Profile.

### Error logging
Whenever an exception is thrown during the Adobe IMS Login, we will log the specific exception message but show a general error message on the admin login form.

Errors are logged into the `/var/log/admin_adobe_ims.log` file. 

Logging can be enabled or disabled in the config on changing the value for `adobe_ims\integration\logging_enabled` or in the Magento Admin Configuration under `Advanced > Developer > Debug`. \
There you can switch the toggle for `Enable Logging for Admin Adobe IMS Module`

# Password usage in Admin UI
When the AdobeAdminIMS Module is enabled, we do not need any password fields in the Magento admin backend anymore.

So we removed the "Password" and "Password Confirmation" fields of the user forms.
This is done by the plugin `\Magento\AdminAdobeIms\Plugin\RemovePasswordAndUserConfirmationFormFieldsPlugin`.
Here we remove the password and password confirmation field. 
As the verification field is just hidden, we set a random password to bypass the input filters of the Save and Delete user Classes.
The `\Magento\AdminAdobeIms\Plugin\RemoveUserValidationRulesPlugin` plugin is required to remove the password fields from the form validation.
We update the "Current User Identity Verification" fieldset to add "Verify Identity with Adobe IMS" button instead "Your Password" field.
This is done by the plugins: `Magento\AdminAdobeIms\Plugin\Block\Adminhtml\User\Edit\Tab\AddReAuthVerification`, `Magento\AdminAdobeIms\Plugin\Block\Adminhtml\System\Account\Edit\AddReAuthVerification`, `Magento\AdminAdobeIms\Plugin\Block\Adminhtml\User\Role\Tab\AddReAuthVerification` and `Magento\AdminAdobeIms\Plugin\Block\Adminhtml\Integration\Edit\Tab\AddReAuthVerification`.

As we update the current user verification field, we have the `\Magento\AdminAdobeIms\Plugin\ReplaceVerifyIdentityWithImsPlugin` plugin to verify the `AdobeReAuthToken` of the current admin user in AdobeIMS and only proceed when it is valid.

For the newly created user will be a random password generated, as we did not modify the admin_user table, where the password field can not be null. 
This is done in the `\Magento\AdminAdobeIms\Plugin\UserSavePlugin`.

We also disabled the "Change password in 30 days" functionally, as we don't need the Magento admin user password for the login.
This can be found in the `\Magento\AdminAdobeIms\Plugin\DisableForcedPasswordChangePlugin` and `\Magento\AdminAdobeIms\Plugin\DisablePasswordResetPlugin` Plugins.

When the AdminAdobeIMS Module is disabled, the user can not be log in when using an empty password.
Instead, the forgot password function must be used to reset the password.

# WEB API authentication using IMS ACCESS_TOKEN
When Admin Adobe IMS is enabled, Adobe Commerce admin users will stop having credentials (username and password).
These admin user credentials are needed for getting token that can be used to make requests to admin web APIs.
It means that will be not possible to create token because admin doesn't have credentials. In these case we have to use IMS access token.

`\Magento\AdminAdobeIms\Model\Authorization\AdobeImsTokenUserContext` new implementation for `\Magento\Authorization\Model\UserContextInterface` was created.
In the implementation IMS access token is validated and read to get created_at and expires_in data. 
If access_token_hash already exists in admin_adobe_ims_webapi table, then we can get admin_user_id.
If access_token_hash does not exist in admin_adobe_ims_webapi table, then we have to make request to IMS service to get Adobe user profile, that contain email.
Using email from Adobe user profile we can check if admin user with these email exists in Magento. If so, we save relevant data into admin_adobe_ims_webapi table.
If admin user with the email is not found, authentication will fail.

Web Api Token validation via IMS request.
Each new token (access_token_hash is not exist in admin_adobe_ims_webapi) is validated by using Adobe IMS endpoint validate_token.
For already existing access_token_hash in admin_adobe_ims_webapi table, validation happens only if last validation was more than 10 min ago.
Last time validation is saved as last_check_time in admin_adobe_ims_webapi table.

Check if token has expired.
Access token itself has expires_in value (by default is 24h, but can be adjusted in Adobe side settings).
Magento has setting: Stores > Settings > Configuration > Services > OAuth > Access Token Expiration (default is 4h).
Both of values are checked in function isTokenExpired \Magento\AdminAdobeIms\Model\TokenReader.
it means that with default values is not possible to use tokens that older than 4h.

### IMS access token verification.
To verify token a public key is required. For more info https://wiki.corp.adobe.com/display/ims/IMS+public+key+retrieval 
In Admin Adobe Ims module was defined path where certificate has to be downloaded from.
By default, in config.xml, these value for production.
For testing reasons, developers can override this value, for example in env.php file like this:
```
'system' => [
        'default' => [
            'adobe_ims' => [
                'integration' => [
                    'certificate_path' => 'https://static.adobelogin.com/keys/nonprod/',
                ]
            ]
        ]
    ]
```
Certificate value is cached.

This authentication mechanism enabled for REST and SOAP web API areas.

Examples, how developers can test functionality:
curl -X GET "{domain}/rest/V1/customers/2" -H "Authorization: Bearer AddAdobeImsAccessToken"
curl -X GET "{domain}/rest/V1/products/24-MB01" -H "Authorization: Bearer AddAdobeImsAccessToken"

### Two-factor authentication.
During CLI enablement of the module, the admin user is asked, whether 2FA is enabled for Organization in Adobe Admin Console.
If the answer is yes, Magento TFA module (if it's present in the code base), should be disable.

For this purpose the additional config value was added, this config value is read by Magento_TwoFactorAuth module.
If the config value is not there, the Magento_TwoFactorAuth functionality works by default.

# Updated Current User Identity Verification
The AdobeAdminIms Module updates the handling of the current user identity verification.

Instead of providing the current user password, the user needs to call the AdobeIms reAuth function.
We replaced the password field with a "verify identity" button.

By clicking on this button a popup opens with the AdobeIms Login, where the current user must enter his adobe ims password again to verify his identity.
After successfully validate his identity, we are redirecting to the `Magento/AdminAdobeIms/Controller/Adminhtml/OAuth/ImsReauthCallback.php` Controller and update the `ims_verified` field.

When the form will be submitted, we verify the identity with the `Magento/AdminAdobeIms/Plugin/ReplaceVerifyIdentityWithImsPlugin.php` Plugin.
Here the existens of the `AdobeAccessToken` and `AdobeReAuthToken` will be checked.
The reauth_token will be used to call the AdobeIms validateToken Endpoint.

When this call is successful, the form will be submitted, otherwise we update the Message of the thrown `AuthenticationException` to return a matching error message, done by the `Magento/AdminAdobeIms/Plugin/PerformIdentityCheckMessagePlugin.php` Plugin.
