# Magento_Customer module

This module serves to handle the customer data (Customer, Customer Address and Customer Group entities) both in the admin panel and the storefront.
For customer passwords, the module implements upgrading hashes.

## Installation

The Magento_Customer module is one of the base Magento 2 modules. You cannot disable or uninstall this module.
This module is dependent on the following modules:

- `Magento_Eav`
- `Magento_Directory`

The following modules depend on this module:

- `Magento_Captcha`
- `Magento_Catalog`
- `Magento_CatalogCustomerGraphQl`
- `Magento_CatalogRule`
- `Magento_CompareListGraphQl`
- `Magento_CustomerAnalytics`
- `Magento_CustomerGraphQl`
- `Magento_EncryptionKey`
- `Magento_LoginAsCustomerGraphQl`
- `Magento_NewRelicReporting`
- `Magento_ProductAlert`
- `Magento_Reports`
- `Magento_Sales`
- `Magento_Swatches`
- `Magento_Tax`
- `Magento_Wishlist`
- `Magento_WishlistGraphQl`

The Magento_Customer module creates the following tables in the database:

- `customer_entity`
- `customer_entity_datetime`
- `customer_entity_decimal`
- `customer_entity_int`
- `customer_entity_text`
- `customer_entity_varchar`
- `customer_address_entity`
- `customer_address_entity_datetime`
- `customer_address_entity_decimal`
- `customer_address_entity_int`
- `customer_address_entity_text`
- `customer_address_entity_varchar`
- `customer_group`
- `customer_eav_attribute`
- `customer_form_attribute`
- `customer_eav_attribute_website`
- `customer_visitor`
- `customer_log`

For information about a module installation in Magento 2, see [Enable or disable modules](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/tutorials/manage-modules.html).

## Extensibility

Extension developers can interact with the Magento_Customer module. For more information about the Magento extension mechanism, see [Magento plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The Magento dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_Customer module.

A lot of functionality in the module is on JavaScript, use [mixins](https://developer.adobe.com/commerce/frontend-core/javascript/mixins/) to extend it.

### Events

The module dispatches the following events:

#### Block

- `adminhtml_block_html_before` event in the `\Magento\Customer\Block\Adminhtml\Edit\Tab\Carts::_toHtml` method. Parameters:
    - `block` is a `$this` object (`Magento\Customer\Block\Adminhtml\Edit\Tab\Carts` class)

#### Controller

- `customer_register_success` event in the `\Magento\Customer\Controller\Account\CreatePost::execute` method. Parameters:
    - `account_controller` is a `$this` object (`\Magento\Customer\Controller\Account\CreatePost` class)
    - `customer` is a customer object (`\Magento\Customer\Model\Data\Customer` class)

- `customer_account_edited` event in the `\Magento\Customer\Controller\Account\EditPost::dispatchSuccessEvent` method. Parameters:
    - `email` is a customer email (`string` type)

- `adminhtml_customer_prepare_save` event in the `\Magento\Customer\Controller\Adminhtml\Index\Save::execute` method. Parameters:
    - `customer` is a customer object to be saved (`\Magento\Customer\Model\Data\Customer` class)
    - `request` is a request object with the `\Magento\Framework\App\RequestInterface` interface.

- `adminhtml_customer_save_after` event in the `\Magento\Customer\Controller\Adminhtml\Index\Save::execute` method. Parameters:
    - `customer` is a customer object (`\Magento\Customer\Model\Data\Customer` class)
    - `request` is a request object with the `\Magento\Framework\App\RequestInterface` interface.
  
#### Model

- `customer_customer_authenticated` event in the `\Magento\Customer\Model\AccountManagement::authenticate` method. Parameters:
    - `model` is a customer object (`\Magento\Customer\Model\Customer` class)
    - `password` is a customer password (`string` type)

- `customer_data_object_login` event in the `\Magento\Customer\Model\AccountManagement::authenticate` method. Parameters:
    - `customer` is a customer object (`\Magento\Customer\Model\Data\Customer` class)

- `customer_address_format` event in the `\Magento\Customer\Model\Address\AbstractAddress::format` method. Parameters:
    - `type` is a address format type (`string` type)
    - `address` is a `$this` object (`\Magento\Customer\Model\Address\AbstractAddress` class)

- `customer_customer_authenticated` event in the `\Magento\Customer\Model\Customer::authenticate` method. Parameters:
    - `model` is a customer object (`\Magento\Customer\Model\Customer` class)
    - `password` is a customer password (`string` type)

- `customer_save_after_data_object` event in the `\Magento\Customer\Model\ResourceModel\CustomerRepository::save` method. Parameters:
    - `customer_data_object` is a saved customer object (`\Magento\Customer\Model\Data\Customer` class)
    - `orig_customer_data_object` is a previous customer object (`\Magento\Customer\Model\Data\Customer` class)
    - `delegate_data` is a customer additional data (`array` type)

- `customer_session_init` event in the `\Magento\Customer\Model\Session::__construct` method. Parameters:
    - `customer_session` is a `$this` object (`\Magento\Customer\Model\Session` class)
  
- `customer_login` event in the `\Magento\Customer\Model\Session::setCustomerAsLoggedIn` method. Parameters:
    - `customer` is a `$this` object (`\Magento\Customer\Model\Customer` class)

- `customer_data_object_login` event in the `\Magento\Customer\Model\Session::setCustomerAsLoggedIn` method. Parameters:
    - `customer` is a `$this` object (`\Magento\Customer\Model\Data\Customer` class)

- `customer_login` event in the `\Magento\Customer\Model\Session::setCustomerDataAsLoggedIn` method. Parameters:
    - `customer` is a `$this` object (`\Magento\Customer\Model\Customer` class)

- `customer_data_object_login` event in the `\Magento\Customer\Model\Session::setCustomerDataAsLoggedIn` method. Parameters:
    - `customer` is a `$this` object (`\Magento\Customer\Model\Data\Customer` class)

- `customer_logout` event in the `\Magento\Customer\Model\Session::logout` method. Parameters:
    - `customer` is a `$this` object (`\Magento\Customer\Model\Customer` class)

- `visitor_init` event in the `\Magento\Customer\Model\Visitor::logout` method. Parameters:
    - `visitor` is a `$this` object (`\Magento\Customer\Model\Visitor` class)

- `visitor_activity_save` event in the `\Magento\Customer\Model\Visitor::saveByRequest` method. Parameters:
    - `visitor` is a `$this` object (`\Magento\Customer\Model\Visitor` class)

For information about an event in Magento 2, see [Events and observers](https://developer.adobe.com/commerce/php/development/components/events-and-observers/#events).

### Layouts

This module introduces the following layouts in the `view/frontend/layout` and `view/adminhtml/layout` directories:

- `view/adminhtml/layout`:
    - `customer_address_edit`
    - `customer_group_index`
    - `customer_index_cart`
    - `customer_index_carts`
    - `customer_index_edit`
    - `customer_index_index`
    - `customer_index_newsletter`
    - `customer_index_orders`
    - `customer_index_viewcart`
    - `customer_index_viewwishlist`
    - `customer_online_index`

- `view/frontend/layout`:
    - `customer_account`
    - `customer_account_confirmation`
    - `customer_account_create`
    - `customer_account_createpassword`
    - `customer_account_edit`
    - `customer_account_forgotpassword`
    - `customer_account_index`
    - `customer_account_login`
    - `customer_account_logoutsuccess`
    - `customer_address_index`
    - `default`

For more information about a layout in Magento 2, see the [Layout documentation](https://developer.adobe.com/commerce/frontend-core/guide/layouts/).

### Public APIs

#### Data

- `\Magento\Customer\Api\Data\AddressInterface`:
    - customer address data

- `\Magento\Customer\Api\Data\AddressSearchResultsInterface`:
    - customer address search result data

- `\Magento\Customer\Api\Data\AttributeMetadataInterface`:
    - customer attribute metadata

- `\Magento\Customer\Api\Data\CustomerInterface`:
    - customer data

- `\Magento\Customer\Api\Data\CustomerSearchResultsInterface`:
    - customer search result data

- `\Magento\Customer\Api\Data\GroupInterface`:
    - customer group data

- `\Magento\Customer\Api\Data\GroupSearchResultsInterface`:
    - customer group search result data

- `\Magento\Customer\Api\Data\OptionInterface`:
    - option data

- `\Magento\Customer\Api\Data\RegionInterface`:
    - customer address region data

- `\Magento\Customer\Api\Data\ValidationResultsInterface`:
    - validation results data

- `\Magento\Customer\Api\Data\ValidationRuleInterface`:
    - validation rule data

#### Metadata

- `\Magento\Customer\Api\MetadataInterface`:
    - retrieve all attributes filtered by form code
    - retrieve attribute metadata by attribute code
    - get all attribute metadata
    - get custom attributes metadata for the given data interface

- `\Magento\Customer\Api\MetadataManagementInterface`:
    - check whether attribute is searchable in admin grid and it is allowed
    - check whether attribute is filterable in admin grid and it is allowed

#### Customer address

- `\Magento\Customer\Api\AddressMetadataInterface`:
    - retrieve information about customer address attributes metadata
    - extends `Magento\Customer\MetadataInterface`

- `\Magento\Customer\Api\AddressMetadataManagementInterface`:
    - manage customer address attributes metadata
    - extends `Magento\Customer\Api\MetadataManagementInterface`

- `\Magento\Customer\Api\AddressRepositoryInterface`:
    - save customer address
    - get customer address by address ID
    - retrieve customers addresses matching the specified criteria
    - delete customer address
    - delete customer address by address ID

- `\Magento\Customer\Model\Address\AddressModelInterface`
    - get street line by number
    - create fields street1, street2, etc

- `\Magento\Customer\Model\Address\ValidatorInterface`
    - validate address instance

- `\Magento\Customer\Model\Address\CustomAttributeListInterface`
    - retrieve list of customer addresses custom attributes

#### Customer

- `\Magento\Customer\Api\AccountManagementInterface`:
    - create customer account
    - create customer account using provided hashed password
    - validate customer data
    - check if customer can be deleted
    - activate a customer account using customer EMAIL and key that was sent in a confirmation email
    - activate a customer account using customer ID and key that was sent in a confirmation email
    - authenticate a customer by username and password
    - change customer password by customer EMAIL
    - change customer password by customer ID
    - send an email to the customer with a password reset link
    - reset customer password
    - check if password reset token is valid
    - gets the account confirmation status
    - resend confirmation email
    - check if given email is associated with a customer account in given website
    - check store availability for customer given the customer ID
    - retrieve default billing address for the given customer ID
    - retrieve default shipping address for the given customer ID
    - get hashed password

- `\Magento\Customer\Api\CustomerManagementInterface`:
    - provide the number of customer count

- `\Magento\Customer\Api\CustomerMetadataInterface`:
    - retrieve information about customer attributes metadata
    - extends `Magento\Customer\MetadataInterface`

- `\Magento\Customer\Api\CustomerMetadataManagementInterface`:
    - manage customer attributes metadata
    - extends `Magento\Customer\Api\MetadataManagementInterface`

- `\Magento\Customer\Api\CustomerNameGenerationInterface`:
    - concatenate all customer name parts into full customer name

- `\Magento\Customer\Api\CustomerRepositoryInterface`:
    - create or update a customer
    - get customer by customer EMAIL
    - get customer by customer ID
    - retrieve customers which match a specified criteria
    - delete customer
    - delete customer by customer ID

- `\Magento\Customer\Model\AuthenticationInterface`:
    - process customer authentication failure by customer ID
    - unlock customer by customer ID
    - check if a customer is locked by customer ID
    - authenticate customer by customer ID and password

- `\Magento\Customer\Model\EmailNotificationInterface`:
    - send notification to customer when email and/or password changed
    - send email with new customer password
    - send email with reset password confirmation link
    - send email with new account related information

#### Customer group

- `\Magento\Customer\Api\CustomerGroupConfigInterface`:
    - set system default customer group

- `\Magento\Customer\Api\GroupManagementInterface`:
    - check if customer group can be deleted
    - get default customer group
    - get customer group representing customers not logged in
    - get all customer groups except group representing customers not logged in
    - get customer group representing all customers

- `\Magento\Customer\Api\GroupRepositoryInterface`:
    - save customer group
    - get customer group by group ID
    - retrieve customer groups which match a specified criteria
    - delete customer group
    - delete customer group by ID

- `\Magento\Customer\Model\Group\RetrieverInterface`
    - get current customer group id from session

- `\Magento\Customer\Model\Customer\Source\GroupSourceLoggedInOnlyInterface`
    - get customer group attribute source

For information about a public API in Magento 2, see [Public interfaces & APIs](https://developer.adobe.com/commerce/php/development/components/api-concepts/).

### UI components

You can extend customer and customer address updates using the configuration files located in the `view/adminhtml/ui_component` and `view/base/ui_component` directories:

- `view/adminhtml/ui_component`:
    - `customer_address_form`
    - `customer_address_listing`
    - `customer_group_listing`
    - `customer_listing`
    - `customer_online_grid`

- `view/base/ui_component`:
    - `customer_form`

For information about a UI component in Magento 2, see [Overview of UI components](https://developer.adobe.com/commerce/frontend-core/ui-components/).

## Additional information

More information can get at articles:

- [Customer Configurations](https://experienceleague.adobe.com/docs/commerce-admin/config/customers/customer-configuration.html)
- [Customer Attributes](https://experienceleague.adobe.com/docs/commerce-admin/customers/customer-accounts/attributes/attribute-properties.html)
- [Customer Address Attributes](https://experienceleague.adobe.com/docs/commerce-admin/customers/customer-accounts/attributes/address-attributes.html)
- [EAV And Extension Attributes](https://developer.adobe.com/commerce/php/development/components/attributes/)
- [2.4.x Release information](https://experienceleague.adobe.com/docs/commerce-operations/release/notes/overview.html)

### Console commands

Magento_Customer provides console commands:

- `bin/magento customer:hash:upgrade` - upgrades a customer password hash to the latest hash algorithm

### Cron options

Cron group configuration can be set at `etc/crontab.xml`:

- `visitor_clean` - clean visitor's outdated records

[Learn how to configure and run cron in Magento.](https://experienceleague.adobe.com/docs/commerce-operations/configuration-guide/cli/configure-cron-jobs.html).

### Indexers

This module introduces the following indexers:

- `customer_grid` - customer grid indexer

[Learn how to manage the indexers](https://experienceleague.adobe.com/docs/commerce-operations/configuration-guide/cli/manage-indexers.html).
