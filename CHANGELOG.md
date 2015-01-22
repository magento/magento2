0.42.0-beta4
=============
* Various improvements:
    * Updated Copyright Notice and provided reference to the license file
    * Updated test framework to support stores other than default
    * Removed version information from theme.xml files leaving it only in composer.json files
* Fixed bugs:
    * Fixed an issue where coupon code was reported to be invalid if it has been removed from reorder in backend and then re-applied
    * Fixed an issue where the 'Guide to Using Sample Data' link was incorrect in the web setup UI
    * Fixed an issue where the link to System Requirements in bootstrap.php was incorrect
    * Fixed an issue where Compiler could not verify case sensitive dependency
    * Fixed an issue where the Recently Compared Products and Recently Viewed Products widgets were not displayed in sidebars
    * Fixed an issue where the Orders and Returns widget type contained unnecessary tab
    * Fixed an issue where an image added to a CMS page using the WYSIWYG editor was displayed as a broken link after turning off the allow_url_fopen parameter in php.ini
    * Fixed an issue where it was impossible to log in to the backend from the first attempt after changing Base URL
    * Fixed an issue where it was impossible to set back the default English (United States) interface locale for the admin user after changing it so an other value
    * Fixed an issue where it was possible to execute malicious JavaScript code in the context of website via the Sender Email parameter
    * Fixed an issue where the Product Stock Alert email was sent to a customer from a store view different than a customer account was created in
    * Fixed an issue where the "Server cannot understand Accept HTTP header media type" error message was not informative enough
    * Fixed an issue where unit tests did not work as expected after installing Magento 2
    * Fixed an issue where the password change email notification was sent after saving admin account settings even if password was not changed
    * Fixed an issue where static tests failed as a result of adding  API functional tests
    * Fixed API functional tests after merging pull request [#927](https://github.com/magento/magento2/pull/927)
    * Fixed an issue where the Edit button was present for invoiced orders
    * Fixed an issue where function _underscore did not work with keys like SKeyName ('s_key_name')
    * Fixed an issue where a fatal error occurred when browsing categories if web server did not have write permissions for media/catalog/product
* Github requests:
    * [#792](https://github.com/magento/magento2/issues/792) -- Failed to set ini option "session.save_path" to value
    * [#796](https://github.com/magento/magento2/issues/796) -- install.log cannot be created with open_basedir restriction
    * [#823](https://github.com/magento/magento2/issues/823) -- Installation bug
    * [#920](https://github.com/magento/magento2/issues/920) -- "web setup wizard is not accessible" error message but the setup wizard is actually accessible
    * [#829](https://github.com/magento/magento2/issues/829) -- [API] OAuth1.0 request token request failing / Consumer key has expired
    * [#658](https://github.com/magento/magento2/issues/658) -- Inline translate malfunctioning
    * [#950](https://github.com/magento/magento2/pull/950) -- Fix for the missed trailing end of line in indexer.php usage help text
    * [#932](https://github.com/magento/magento2/pull/932) -- Migration tool - not all input has comments
    * [#959](https://github.com/magento/magento2/pull/959) -- Replace UTF8 'en dash' with minus in error message
    * [#911](https://github.com/magento/magento2/pull/911) -- Fix test assertion and slight cleanup refactoring
    * [#936](https://github.com/magento/magento2/pull/936) -- Bugfix for regions with single quote in name
    * [#902](https://github.com/magento/magento2/pull/902) -- Add integration test for View\Page\Config\Reader\Html
    * [#925](https://github.com/magento/magento2/pull/925) -- Failed test due to Class not following the naming conventions
    * [#943](https://github.com/magento/magento2/pull/943) -- magento2-925 Failed Test due to Class not following the naming conventions
    * [#968](https://github.com/magento/magento2/pull/968) -- Apply pattern matching datasource config files
    * [#949](https://github.com/magento/magento2/pull/949) -- Added 'status' command for cache cli script / Also improved readability
* PHP 5.6 in composer.json:
    * Added PHP 5.6.0 to the list of required PHP versions in all composer.json files
    * Updated Travis CI configuration to include PHP 5.6 builds
* Framework improvements:
    * Removed TODOs in the Integration and Authorization modules
    * Removed leading backslash from the 'use' statement throughout the code base

0.42.0-beta3
=============
* Fixed bugs:
    * Fixed an issue where malicious JavaScript could be executed when adding new User Roles in the backend
    * Fixed an issue where incorrect output format was returned when invoking the Customer service
    * Fixed an issue where it was impossible to activate an integration after editing the URLs
    * Fixed an issue where incorrect class path was used in the ObjectManager calls
    * Fixed an issue where inconsistent Reflection classes were used for WebApi applications
    * Fixed an issue where the parent element was removed from theme.xml by mistake
* API functional tests changes:
    * Moved API functional tests to CE repository
* Various improvements:
    * Removed include-path from composer.json
* GitHub requests:
    * [#876](https://github.com/magento/magento2/pull/876) -- [BUGFIX] Fixed german translation "Warenkorbrn"
    * [#880](https://github.com/magento/magento2/pull/880) -- Naming fix in DI compiler.php - rename binary to igbinary to stay consistent
    * [#913](https://github.com/magento/magento2/pull/913) -- Specify date fixture and fix expectations
    * [#874](https://github.com/magento/magento2/pull/874) -- Prevent special characters finding their way into layout handle due to SKU being used
    * [#903](https://github.com/magento/magento2/pull/903) -- Small cleanup refactoring
    * [#905](https://github.com/magento/magento2/pull/905), [#907](https://github.com/magento/magento2/pull/907), [#908](https://github.com/magento/magento2/pull/908) -- Change interpret() return value to conform with Layout\ReaderInterface
    * [#913](https://github.com/magento/magento2/pull/913) -- Specify date fixture and fix expectations

0.42.0-beta2
=============
* Framework improvements:
    * Added composer.lock to the repository
* Various improvements:
    * Magento PSR-3 compliance
    * Updated file iterators to work with symlinks
    * Replaced end-to-end test for advanced search with injectable test
    * Replaced end-to-end test for quick search with injectable test
* Fixed bugs:
    * Fixed an issue where an exception occurred when adding configurable products to cart from the wishlist
    * Modify .gitignore CE according to new repos structure
    * Fixed an issue where the 'Not %Username%?' link was displayed for a logged in user while pages were loaded
    * Fixed an issue where Shopping Cart Price Rules based on product attributes were not applied to configurable products
    * Fixed an issue where the Tax Class drop-down field on New Customer Group page contained the 'none' value when a tax class already existed
    * Fixed an issue where the 'Credit Memo' button was absent on the Invoice page for payments
    * Fixed an issue where incorrect totals were shown in the Coupon Usage report
    * Fixed an issue where an error occurred and the "Append Comments" checkbox was cleared when submitting an order in the backend
    * Fixed an issue where the Transactions tab appeared in the backend for orders where offline payment methods were used
    * Fixed an issue with the extra empty line appearing in the Customer Address template
* Github requests:
    * [#853](https://github.com/magento/magento2/pull/853) -- Fix spelling error in Customer module xml
    * [#858](https://github.com/magento/magento2/pull/858) -- Clicking CMS page in backend takes you to the dashboard
    * [#858](https://github.com/magento/magento2/issues/816) -- Clicking CMS page takes you to the dashboard
    * [#859](https://github.com/magento/magento2/pull/859) -- Fix email template creation date not being persisted
    * [#860](https://github.com/magento/magento2/pull/860) -- Fix currency and price renderer

0.42.0-beta1
=============
* Fixed bugs:
    * Fixed an issue with incorrect price index rounding on bundle product
    * Fixed an issue with product price not being updated when clicking the downloadable link on the downloadable product page
    * Fixed an issue with exception appearing when clicking the Compare button for selected products
    * Added backend UI improvements
    * Fixed an issue with the Compare Products block appearing on mobile devices
    * Fixed an issue with inability to add conditions to the Catalog Products List widget
    * Fixed an issue with a customer redirected to page 404 when trying to unsubscribe from a newsletter
    * Fixed an issue with showing a warning when customer tried to change billing address during multiple address checkout
    * Fixed an issue with redirecting a customer to the Admin panel when clicking the Reset customer password link
    * Fixed an issue with inability of a newly registered customer to select product quantity and shipping addresses during multiple checkout
    * Fixed an issue with showing Zend_Date_Exception and Zend_Locale_Exception exceptions after a customer placed an order
    * Fixed an issue with inability to rename a subcategory on a store view level
    * Fixed an issue with not saving the changed parameters in the Admin section of the backend configuration
    * Fixed an issue with fatal error appearing when trying to enter a new address on multi-address checkout
    * Fixed an issue with inability to delete a product in the customer’s wishlist in the Admin panel
    * Fixed an issue with inability to change product configuration in the customer’s wishlist in the Admin panel
    * Fixed an issue with showing errors when customer with no addresses tried to checkout a product via Check out With Multiple Addresses
    * Fixed an issue with fatal errors appearing in the Recently Viewed Products frontend widget block
    * Fixed an issue with the ability of an authenticated RSS admin user to access all RSS feeds
    * Fixed an issue with widgets losing their options and part of their layout references if more than11 layout references are added and saved
    * Fixed an issue with the Privacy Policy link missing in the frontend
    * Fixed an issue with inability to place an order during multiple checkout
    * Fixed an issue with store views switching in the frontend
    * Fixed an issue with incorrect work of the CSS minificator
    * Fixed an issue with inability to open the edit page for a CMS page after filtering in the grid
    * Fixed an issue with inability to expand customer menu if it doesn't contain the categories, if responsive
    * Fixed an issue with the absence of JS validation for the Zip/Postal code field
    * Fixed an issue with a 1 cent difference in the tax summary and detail on an invoice and a credit memo for a partial invoice when a discount and fixed product tax are applied
    * Fixed an issue with throwing validation error for the State field when saving a product with FPT
    * Fixed an issue with throwing an error when trying to save a timezone
    * Fixed an issue with Exploited Session ID in second browser leading to Error
    * Fixed an issue with session loss on page 404 when using the Varnish caching
    * Fixed an issue with integration test not resetting static properties to correct default values after each test suite
    * Fixed an issue with PDO exception during an installation when MySQL does not meet minimum version requirement
    * Removed hardcoded PHP version requirement in the setup module. Validation of PHP version during installation now uses the Composer information
    * Fixed an issue with not redirecting to the setup page when Magento is not installed
    * Fixed an issue with missing of some languages in the dropdown list on the Customize Your Store page of the Web installation
    * Merged and updated data and SQL install scripts to 2.0.0
    * Merged user reported patch to fix fetching headers for APIs when PHP is run as fast CGI
    * Removed the @deprecated methods from the code base
    * Fixed an issue with the fatal error when enabling Website Restrictions in the frontend
    * Fixed an issue with showing incorrect message for view files population tool when the application is not installed
    * Fixed certain customer APIs to be accessed anonymously
    * Fixed integration tests to avoid sending emails
    * Fixed an issue with the Continue button losing its style after returning to the Shipping Information step during one-page checkout in Luma, IE11, FF
    * Fixed an issue with incorrect spaces removal
    * Fixed an issue with broken responsive design of the Compare Products functionality in the Blank Theme
    * Fixed an issue with showing the “No such entity with cartId' message error appearing during creating a new order for a new customer on non-default website
    * Fixed an issue with inability to reselect the File Permission on the Readiness Check step during the installation
    * Fixed an issue with inability to find by name simple and virtual products in the customer wishlist grid
    * Fixed integration test fail after DbStatusValidatorTest modifies schema version of the Core module
    * Fixed an issue with inability to install Magento without the ConfigurableProduct module
    * Fixed an issue with fatal error appearing on the grouped product page if the GroupedProduct module is disabled
    * Fixed an issue with no validation for assigning an attribute to an attribute group (API)
    * Fixed an issue with inability to place an order with the registration method and different billing and shipping address
    * Fixed an issue with broken footer layout on some Admin panel pages (product creation, order creation, catalog etc.) in IE11
    * Fixed an issue with countries previously selected in the Ship to specific countries field not visible when the parameter is changed to showing all allowed countries and set back again to specific countries in the flat rate shipping method IE11
    * Fixed an issue with not showing admin tax and cache warning notifications in IE11
    * Fixed an issue with product alerts not working
    * Fixed an issue with incorrect URL rewrite for category with two stores after renaming category for one store
    * Fixed an issue with inability to save a bundle product with a re-created bundle option
    * Fixed an issue with inability to add conditions to the Catalog Products List widget
    * Fixed an issue with export not available if modules for Products Import/Export are removed
    * Fixed an issue with the Use Layered Navigation for custom product attributes leading to an error on an anchor category page in the frontend
    * Fixed an issue with the broken export product file on environment SampleData
    * Fixed an issue with cache not invalidating after categories are moved in tree
    * Fixed an issue with last five orders showing 0 items quantity after invoices are created
    * Fixed an issue with an exception appearing on a category page if installing Magento without LayeredNavigation module
    * Fixed an issue with tax rate not being saved if all states were chosen for any non-default country
    * Fixed an issue with multi-select fail on the Customer add/edit form
    * Added exception handling for required fields for REST APIs
    * Fixed an issue with success message missing after the signup for price alert
    * Fixed an issue with inability to create a return order from the Admin panel
    * Fixed an issue with incorrect work of the Default Value for Disable Automatic Group Changes Based on VAT ID setting
    * Fixed an issue with fatal error on the I18n tools launch due to incorrect bootstrap/autoload
    * Stabilized functional tests for products in the Catalog module
    * Stabilized functional tests for product attribute in the Catalog module
    * Created installation test
    * Updated functional tests for the new customer form
    * Updated Magento to follow the new tagging mechanism
    * Removed incomplete in functional tests for fixed bugs
    * Fixed an issue with missing theme preview images
    * Fixed broken SOAP tests
    * Fixed an issue with invalid online status on the Edit Product page in the Admin panel
    * Fixed an issue with incorrect location of an error message "Incorrect CAPTCHA" in the frontend
    * Fixed an issue with showing  endless JS loader on the View Configurable Product page in the frontend page, IE, Google Chrome
    * Fixed a JavaScript error that occurred on the Create Admin Account step during Magento web installation
    * Fixed an issue where a product remained in stock after saving it with the ‘Out of Stock’ inventory value
    * Fixed an issue where the JS loader was not disappearing on the View Product page on the frontend if a customer closed the gallery
    * Fixed an issue where the JS loader was absent while CAPTCHA was being reloaded
    * Fixed an incorrect alignment of fields on the Create Packages popup
    * Fixed an issue where Google Content Experiments was not available for CMS pages
    * Fixed the broken design of the New Product Attribute popup
    * Fixed an issue where product page was not found if an incorrect image URL was inserted through using the WYSISYG editor
    * Fixed an issue where the Search Term Report and Search Term list in backend did not work
    * Fixed an issue where downloadable links and samples were not saved because of the JavaScript error
    * Fixed an issue where Magento Installation Guide was not accessible via the  'Getting Started' link if installing Magento through using web installer with custom locale and custom encryption key
    * Fixed an issue with the code style
    * Fixed an issue where changes made in tax configuration did not appear in the backend on the Create New Order page
    * Fixed an issue where it was impossible to update options of bundle products from the mini shopping cart
    * Fixed an issue where layered navigation worked incorrectly with the Automatic (equalize product counts) setting
    * Fixed an issue with the incorrect error message appearing when running 'php -f setup/index.php help’
    * Fixed an issue where URLs for subcategories were incorrect after editing URL of a subcategory
    * Fixed an issue where attribute labels were loaded from cache after updating product attributes
    * Fixed an issue where form data was not preserved when product form did not pass server side validation
    * Fixed an issue with static files missing in the Production mode
    * Fixed issues with errors appearing after View Files Population Tool was run
* Processed GitHub requests:
    * [#683](https://github.com/magento/magento2/pull/683) -- CMS Router not routing correctly
    * [#786](https://github.com/magento/magento2/pull/786) -- Fix Travis CI builds
* Various improvements:
    * Improved error message when DB schema or data was not up-to-date
    * Added nginx configuration to code base
    * Removed online payment methods for the Dev Beta release
* Sample Data:
    * Implemented Luma Sample Data
* Framework improvements:
    * Updated ZF1 dependency to 1.12.9-patch1
* Documentation update:
    * Covered the Sales module with API documentation

0.1.0-alpha108
=============
* Service Contracts:
    * Implemented Bundle Product API
    * Replaced Address Converted model with Address Mapper
    * Refactored Customer web service routes and API functional tests to use latest service layer
    * Implemented Configurable Product Module API
    * Removed obsolete namespace Magento\Catalog\Service
* Price calculation logic:
    * Removed complex JS price calculation on the frontend
* Fixed bugs:
    * Fixed an issue where the path parameter routes were incorrectly matched in the REST web services
    * Fixed an issue where $0.00 was shown as a configurable product price if variation did not add anything to product price
    * Fixed an issue where the fatal error occurred when a user with read-only permissions for cart price rules attempted to open an existing cart price rule
    * Fixed an issue where the 'An order with subscription items was registered.' message was displayed in an order if it has been placed using an online payment method
    * Fixed an issue where the 'Warning: Division by zero' message was displayed when an invoice was opened for an order placed using an online payment method
    * Fixed an issue where creating simple product through using API service led to an exception on the frontend
    * Fixed an issue where it was impossible to perform advanced search for price range of 0 to 0
    * Fixed an issue with the broken Search Terms Report page
    * Fixed an issue with the broken Search Terms page
    * Fixed an issue with a notice appearing in the Advanced Search when searching by a custom multiselect attribute
    * Fixed an issue where Search did not work if word-request contained a hyphen
    * Fixed an issue where searching by a title of bundle option returned empty result
    * Fixed an issue where Maximum Query Length was not applied to Quick Search
    * Fixed an issue where searching by product name did not return suggested search terms
    * Fixed an issue with an incorrect dependency of the MySQL search adapter on CatalogSearch
    * Fixed an issue with incorrect dependency of the Search library on the MySQL adapter
    * Fixed an issue where Advanced Search always returned empty result for multiselect product attributes
    * Fixed an issue where an admin user was redirected to the 404 page after deleting search terms through using mass action
    * Fixed an issue where a product page was frozen when a configurable attribute was added to a current product template during saving a configurable product
    * Fixed an issue where it was impossible to place an order with downloadable product that contained a link
    * Fixed an issue where only parent category was displayed in layered navigation on the Search results page
    * Fixed an issue where the Price attribute was incorrectly displayed in layered navigation if searching by this attribute
    * Fixed an issue where importing configurable products set them out of stock
    * Fixed an issue where drop-down lists were closing by themselves in Internet Explorer 9
    * Fixed an issue where it was impossible to place an order using PayPal Payment Pro and 3D Secure
    * Fixed an issue where bundle items were always marked as 'User Defined'
    * Fixed an issue where view management selectors did not work in categories on the frontend
    * Fixed an issue where the 'Base' image label was not applied to a first product image uploaded
    * Fixed an issue where editing a product led to data loss and broken media links
    * Fixed an issue where attributes could not be deleted from the Google Content Attributes page
    * Fixed an issue where a product was unassigned from a category after it was edited by an admin user with read/edit permissions for product price only
    * Fixed an issue where the fatal error occurred on the RSS page for new products and special products
    * Fixed an issue where the fatal error occurred when adding a new Customer Address Attribute
    * Fixed an issue where it was impossible to install Magento when specific time zones were set
    * Fixed an issue where compiler.php failed not handle inheritance from virtual classes
    * Fixed an issue where some locales were absent in the 'Interface Locales' drop-down in the backend
    * Fixed an issue where the Offloader header did not work in the backend
    * Fixed an issue where autoloader failed to load custom classes
    * Fixed an issue where products did not disappear from the shopping cart after checkout
    * Fixed an issue where changing quantity of a product in the shopping cart removed product from cart
    * Fixed an issue where the Persistent Shopping Cart functionality was not available if Luma theme was applied
    * Fixed an issue where the category tree was broken if editing a category name in specific cases
    * Fixed an issue where 'Price as Configured' value was not updated for a bundle product after changing the value of the 'Price View' field
    * Fixed an issue where the final product price was displayed incorrectly in a category and a product page if price display setting was set to exclude FPT, FPT description, and final price
    * Fixed an issue where product price range was displayed incorrectly for bundle products
    * Fixed an issue where the HTTP 500 error occurred on the Share Wishlist page
    * Fixed an issue with the incorrect order of dispatching event adminhtml_cms_page_edit_tab_content_prepare_form and setting form values in the backend
    * Fixed an issue where breadcrumbs were not displaying the fullpath
    * Fixed an issue where only two of four widgets added to a CMS page were displayed
    * Fixed an issue where it was impossible to save locale for an admin account after changing it
    * Fixed an issue where icons were not loaded on a non-secure pages if secure URLs were used in the frontend
    * Fixed an issue where overriding layouts did not work after renaming a theme
    * Fixed an issue where the Permissions tree was not displayed when activating an integration
    * Fixed an issue with duplicated and corrupted page layouts
    * Fixed an issue where the 'Number of Products per Page' option did not work for widgets of the 'List' type
    * Fixed an issue where HTTP and HTTPS pages shared cache content
    * Fixed an issue where the 'Use Billing Address' checkbox did not affect did not affect the checkout experience
    * Fixed an issue where it was impossible to create shipping labels
    * Fixed an issue where the 'Payment Method' section was empty in billing agreements in the frontend if a billing agreement was created during the checkout
    * Fixed an issue with Catalog Rule Product indexer invalidating the price index
    * Fixed an issue where one of the price range fields was corrupted in the Advanced Search page
    * Fixed an issue where a base product image that was smaller than the gallery image container was scaled up to fill the container on the View Product page in the frontend
    * Fixed the layout issue on the Contact Us page
    * Fixed an issue where search queries were not submitted when a search suggestion was clicked
    * Fixed an issue where page footer overlapped products in categories in Internet Explorer 11
    * Fixed UI issues in the Luma theme
    * Fixed an issue when the fatal error occurred if a category was moved to another category that already contained category with the same URL key
    * Fixed an issue where incorrect products were displayed on the Reviews tab for a configurable product
    * Fixed an issue where fatal errors occurred when calling id() on a null store object
    * Fixed an issue where navigation through the tabs on the Dashboard did not work properly
    * Fixed an issue where prices for bundle products were incorrect on the category view and search view pages
    * Fixed an issue where custom Customer attributes and Customer Address attributes were not displayed on the 'Create/Edit Customer' page in thebackend
    * Fixed an issue where there were no validation for whether an option of a bundle product was created through the API
    * Fixed an issue where bundle products created through using the API were not appearing in the frontend
    * Fixed an issue where entity ID was missing for product thumbnail labels values
    * Fixed an issue with the bad return from the Indexer launch() method
    * Fixed an issue where an attempt to select product SKU in a shopping cart price rule redirected to the Dashboard
    * Fixed an issue where the Search Terms Reports and Search Terms list did not work
    * Fixed an issue where an error occurred when configuring Google API
    * Fixed an issue where it was impossible to add a configurable product variation to an order in the backend
    * Fixed an issue where there were no confirmation on deleting CMS pages/Blocks
    * Fixed an issue with incorrect behavior of validation in the Quick Search field in the frontend
    * Fixed an issue where it was impossible to select a row in the grid of CMS pages and CMS Blocks
    * Fixed an issue where validation for minimum and maximum field value length was not performed for Customer attributes and Customer Address attributes when creating or editing a customer in the backend
    * Fixed an issue with broken 'validate-digits-range' validation
    * Fixed an issue where it was impossible to delete product templates
    * Fixed an issue where products were not shown on a second website
    * Fixed an issue where customer group was empty when adding group price during creating a product
    * Fixed an issue with incorrect interval in LN for small values
    * Fixed an issue where product attribute of the Price type was not displayed in layered navigation
    * Fixed an issue with testCreateCustomer failing in parallel run
    * Fixed an issue with the value of the 'Bill to Name' field always displayed instead of the value of the 'Ship to Name' in all order-related grids
    * Fixed an issue where an error occurred when submitting an order int he backend when shipping and billing addresses were different
    * Fixed an issue where the navigation menu was absent on product pages with Varnish used
    * Fixed an issue where the underscore character was incorrectly handled when used with digits
    * Fixed an issue where it was impossible to localize comments in the 'Max Emails Allowed to be Sent' and 'Email Text Length Limit' fields in the Wishlist configuration
    * Fixed an issue where there were a logical error in joining the same table two times with different aliases
* Sample data:
    * Created Luma Sample Data script
* GitHub requests:
    * [#775](https://github.com/magento/magento2/issues/775) -- Can't save changes in configuration in Configuration->Advanced->System
    * [#716](https://github.com/magento/magento2/issues/716) -- Wrong mimetype returned by getMimeType from Magento library
    * [#681](https://github.com/magento/magento2/issues/681) -- Magento\Framework\Xml\Parser class issues
    * [#758](https://github.com/magento/magento2/issues/758) -- Coding standards: arrays
    * [#169](https://github.com/magento/magento2/issues/169) -- DDL cache should be tagged
    * [#738](https://github.com/magento/magento2/issues/738) -- pub/setup missing in 0.1.0-alpha103
* Various improvements:
    * Removed obsolete code from the Tax and Weee modules
    * Merged the AdminNotification, Integration, Authorization, and WebAPI SQL scripts
    * Removed the Customer Converter model and Address Converter model
    * Created AJAX Authentication Endpoint for the frontend
    * Removed Customer\Service\V1 service implementation in favor of the Customer\Api service implementation
    * Removed the Recurring Billing functionality
    * Added the 'suggest' node to composer.json files to mark modules that are optional
    * Consolidated SQL install and data scripts for the rest of the modules
    * Added static test verifying that README.md file exist in modules
    * Removed obsolete code
    * Removed license notices in files
    * Eliminated invalid dependencies of the CatalogRule module
    * Removed @deprecated methods from the code base
    * Added test enforcing @covers annotation refers to only existing classes and methods
    * Added the PHP Coding Standards Fixer configuration file to the project root
    * Added Git hook to automatically correct coding style before actual push
    * Added the ability to enforce no error log messages during tests execution
    * Removed API interfaces from the Cms module
    * Updated jQuery used to version 1.11
    * Added wildcard prefix for all search words in search requests for Match query
    * Renamed frontend properties for some of the product attributes
    * Fixed the Magento\Centinel\CreateOrderTest integration test
    * Improved invoking for functional tests
    * Refactored StoreManagerInterface to avoid violating the modularity principle
    * Improved the logic in the isSubtotal method in Magento\Reports\Model\Resource\Report\Collection\AbstractCollection
* Framework improvements:
    * Added a copy of dependencies for Magento components to the root composer.json file
* Setup Tool improvements:
    * Moved dependencies from setup/composer.json to the root composer.json and removed the former one
    * Removed dependencies on unnecessary ZF2 libraries
    * Removed dependency on exec() calls
    * Removed tool dev/shell/run_data_fixtures.php in favor of Setup Toolphp setup/index.php install-data
    * Removed tool dev/shell/user_config_data.php in favor of Setup Tool php setup/index.php install-user-configuration
    * Added validation of the required information on each installation step in the Setup tool:
        * Web UI:
            * Removed the 'Test Connection' button in web setup UI; checking connection to the database server is now performed when the 'Next' button is clicked
            * Added validation of URL format
            * Added automatic adding of the trailing slash to the base URL field if a user did not provide one
            * Added validation of admin user password
            * Added validation of HTTPS configuration
        * CLI:
            * Added validation of CLI to display missing/extra parameters and missing/unnecessary parameter values

0.1.0-alpha107
=============
* Various improvements:
    * Removed deprecated code from the Sales and SalesRule modules
    * Stabilized functional tests for the following modules:
        * Centinel
        * Core
        * RecurringPayment
        * Sales
        * Multishipping
        * Newsletter
        * Widget
* Fixed bugs:
    * Fixed an issued where a product could not be found in customer wishlist when searched by name
    * Fixed the invalid email template for Product Price Alert
    * Fixed an issue where customer group did not change when invalid VAT number was specified
    * Fixed integration tests coverage
    * Fixed an issue where a customer was not redirected to the configurable product page after clicking Add to Card on the My Wish list page for a product which required configuration
    * Fixed an issue where an error message was displayed when a customer tried to use checkout using PayPal Express Checkout
    * Fixed an issue where it was impossible to place an order using Authorize Direct Post
    * Fixed an issue where the page cache in Varnish mode didn’t perform caching as required the cache
    * Fixed an issue where it was impossible to specify layout container when creating or editing a widget
    * Fixed an issue where a widget set to be displayed on certain type of product page was not displayed
    * Fixed an issue where it was impossible to create a widget to be displayed in a sidebar
    * Fixed an issue where a fatal error was thrown when trying to open a not existing page after disabling the 404 Not Found CMS page
    * Fixed an issue where it was impossible to refresh CAPTCHA in the Admin panel
    * Fixed an issue where two CAPTCHAs were displayed during guest Checkout
    * Fixed an issued where clicking the Preview button on revision preview page did not open the Preview page
    * Fixed an issue where the Magento\Framework\View\Element\AbstractBlockTest::testFormatTime failed randomly
    * Fixed logic duplication and the conflicting implementation of the title API in admin
    * Fixed an issue where JavaScript validation did not recognize the fields filled by automatic tests in the Create Customer form in the Admin panel
    * Fixed an issue where a fatal error was thrown after mass update of the Stock Availability product attribute
    * Fixed an issue where the Magento\SalesRule\Model\Resource\Report\CollectionTest::testPeriod CollectionTest::testPeriod integration test failed randomly
    * Fixed issues with expandable frontend elements
    * Fixed Blank & Luma themes UI bugs
    * Fixed an issue where the Packages pop-up displayed incorrect information
    * Fixed an issue where admin path became hidden when store address was too long
    * Fixed the styling of variations without base image
    * Fixed an issue where the Back link on a customer edit page led to the home page
    * Fixed an issue where it was impossible to save system config from Advanced->System
    * Fixed an issue where it was impossible to save a Return in the Admin panel
    * Fixed a JavaScript issue where it was impossible to expand nested categories if responsive
    * Fixed an issue where it was impossible to place an order using Authorize.net Direct Post in the Admin panel
* Framework improvements:
    * Declaration of components in composer.json
    * Added compiler for single-tenant mode
    * Both ZF1 and ZF2 libraries are declared as Composer dependencies as "1.12.9" and "2.3.1" respectively
    * ZF1 library is represented by 'magento/zendframework1', which is based on original "1.12.9" version and includes fixes for compatibility with Magento 2 application
* Layout improvements:
    * Refactored layout building
* Performance improvements:
    * Load product/category instances via repositories
    * Mobile and Desktop CSS styles stored in separate files
* Service Contracts:
    * Refactored the following modules to use new Customer service interfaces:
        * Checkout
        * Sales
        * Multishipping
        * GoogleShopping
        * Persistent
        * SalesRule
        * Paypal
        * Invitation
        * Tax
        * Newsletter
    * Code review changes for Service Contracts for the CatalogInventory module
    * Stabilized code after refactoring the Sales module to use new Customer service
    * Stabilized code after refactoring the Checkout module to use new Customer service
    * Deleted old CustomerAccount service tests
    * Fixed base service object class to populate custom attributes correctly
    * Fixed processing of array parameters in service interface for consolidated builder
    * Fixed trace information for service exceptions in dev mode
    * Implemented Bundle product API
* Accessibility improvements:
    * Heading2-Heading6 hierarchy of content structure
* UI improvements:
    * Style independent Error page in pub/errors styles
    * Updated the content of certain default CMS Pages
* GitHub requests:
    * [#691](https://github.com/magento/magento2/issues/691) -- Readonly inputs and after element html in the backend
    * [#694](https://github.com/magento/magento2/issues/694) -- missing git tags in repo

0.1.0-alpha106
=============
* Various improvements:
    * Refactored Service Layer of the Magento_Tax Module
    * Stabilized functional tests for the Backend module
    * Stabilized functional tests for the CatalogRule module
    * Stabilized functional tests for the Checkout module
    * Stabilized functional tests for the CurrencySymbol module
    * Stabilized functional tests for the Shipping module
    * Stabilized functional tests for the Tax module
    * Stabilized functional tests for the User module
* Added Readme.md files to the following modules:
    * Magento\RequireJs
    * Magento\Ui
* Fixed bugs:
    * Fixed an issue where product image assignment to a store view was not considered when displaying a product
    * Fixed shipping address area blinking when billing address is filled during checkout with a virtual product
    * Fixed an issue where filter_store.html was not found
    * Fixed an issue where the customer account access menu did not expand on the storefront
    * Fixed an issue where CMS blocks did not open when clicking from a grid
    * Fixed an issue where the Create Product page was completely blocked after closing the New Attribute pop-up
    * Fixed an issue where Stock Status was disabled for Bundle and Grouped products
    * Fixed an issue where a product could not be saved without filling a not required bundle option
    * Fixed broken "per page" selectors on the Customer's account pages
    * Fixed the wrong behavior of JS loaders on the storefront pages
    * Fixed Shopping cart price rule form validation
    * Fixed an issue where the 'Please wait' spinner persisted when creating a customer custom attribute with existing code
    * Fixed a Google Chrome specific issue where subcategories were not displayed correctly on the first hover for category item
    * Fixed an issue where the 'Please wait' spinner did not disappear when creating customer with invalid email
    * Fixed an issue where the Username field auto-focus on admin login page revealed password in case of fast typing
    * Fixed an issue where Bundle Product original Price was not displayed in case of discount
    * Fixed wrong discount calculation for bundle options
    * Fixed an issue where wrong discount and total amounts were displayed on the order creation page when reordering an order with a bundle product in the Admin panel
    * Fixed an issue where admin tax notifications did not appear/disappear unless cache was flushed or disabled
    * Fixed an issue where catalog price and shopping cart price did not match when display currency was different from the base currency
    * Fixed an issue where Tax classes did not allow 'None' as a valid 'product tax class'
    * Fixed an issue where token-based authentication did not work if compilation was enabled
    * Fixed the sample code in index.php illustrating multi websites set up
    * Fixed commands in Setup CLI to match the ones displayed in help
    * Fixed an issue where searching by a part of a product name in Advanced Search did not give correct results
    * Fixed an issue where 404 page is displayed after Search Term mass deletion
    * Fixed an issue where Popular Search Terms were not displayed on the storefront
    * Fixed an issue where it was impossible to add Gift Message during one page checkout
    * Fixed an issue where the optional Postal code setting did not work correctly
    * Fixed an issue where product price details were missing in summary block in the shopping cart when the Back to shopping cart link was clicked on multishipping page
    * Fixed an issue where the 404 error page was displayed instead of the Index Management page after saving mass update
    * Fixed an issue where the "Out of Stock" message was not displayed for a bundle product when there was not enough of one of the associated products in stock
    * Fixed an issue with the Newsletters Report page in the Admin panel
    * Fixed an issue where Catalog price rule was not applying correct rates on specific products
    * Fixed an issue where a fatal error was thrown after clicking a link to a downloadable product
    * Fixed an issue a warning page for Grouped product with enabled MAP
    * Fixed an issue where a configurable product was not displayed in catalog product grid after updating with "Add configurable attributes to the new set based on current"
    * Fixed the inconsistent behavior in the integration tests for the Indexer functionality
    * Fixed an issue where the What's this? information tip link was not presented on product page with configured Minimum Advertised Price (MAP)
* Processed GitHub requests:
    * [#742](https://github.com/magento/magento2/issues/742) -- Admin notifications count overflow
    * [#720](https://github.com/magento/magento2/issues/720) -- https filedriver is not working
    * [#686](https://github.com/magento/magento2/issues/686) -- Product save validation errors in the admin don't hide the overlay
    * [#702](https://github.com/magento/magento2/issues/702) -- Base table or view not found
    * [#652](https://github.com/magento/magento2/issues/652) -- Multishipping checkout not to change the Billing address js issue
    * [#648](https://github.com/magento/magento2/issues/648) -- An equal (=) sign in the hash of the product page to to break the tabs functionality
* Service Contracts:
    * Refactored usage of new API of the Customer module
    * Implemented Service Contracts for the Sales module
    * Refactored Service Contracts for the Catalog module
    * Refactored Service Contracts for the Grouped module
* UI Improvements:
    * Implemented the Form component in Magento UI Library
    * Removed extra JS loaders for category saving
    * Improved the behavior of Categories management in the Admin panel
    * Implemented the keyboard navigation through HTML elements
    * Improved the HTML structure and UI of the Catalog Category Link, Catalog Product Link and CMS Static Block widgets
    * Added UI Library documentation
    * Fixed Blank & Luma themes UI bugs
    * Fixed footer alignment
    * Published the Luma theme and removed the Plushe theme
* Framework Improvements:
    * Added the ability to configure the list of loaded modules before installation
    * Merged SQL and Data Upgrades
    * Moved \Magento\TestFramework\Utility\Files to Magento Framework
* Setup tool improvements:
    * Removed duplication with Framework
    * Deployment configuration is refactored from XML format in local.xml to associated array in config.php
    * Improved performance
* Search improvements:
    * Integrated the Full Text Search library into the Layered Navigation functionality

0.1.0-alpha105
=============
* Various improvements:
    * Merged SQL and Data Upgrades for the Tax, Weee, Customer, CustomerImportExport, ProductAlert, Sendfriend and Wishlist modules
    * Added 'Interface' suffix to all interface names
    * Stabilized functional tests for the following modules:
        * CheckoutAgreements
        * Customer
        * GiftMessage
        * Integration
        * Msrp
        * Reports
* Added the following functional tests:
    * Create product attribute from product page
* Fixed bugs:
    * Fixed an issue where bundle product price doubled during backend order creation
    * Fixed an issue where an error was thrown during Tax Rate creation, deletion and update
    * Fixed an issue where FPT was doubled when creating a refund if two FPTs were applied, and as a result the refund could not be created
    * Fixed an issue where the subtotal including tax field was not refreshed after removing downloadable product from cart
    * Fixed an issue where a downloadable link tax was not added to a product price on the product page if price was displayed including tax
    * Fixed an issue with incorrect product prices for bundle products in shopping cart
    * Fixed an issue where bundle product price was calculated incorrectly on the product page
    * Fixed an issue where configurable product options were not updated after changing currency
    * Fixed an issue where a standalone simple product and the same product as part of the grouped, were not recognized as one product in the shopping cart.
    * Fixed an issue where the incorrect tier pricing information was displayed in shopping cart
    * Fixed an issue where no notice was displayed in the shopping cart for products with MAP enabled
    * Fixed an issue where it was impossible to place an order from customer page in Admin
    * Fixed an issue where it was impossible to add address for a customer in Admin
    * Fixed an issue with broken redirect URL after deleting a product from the My Wishlist widget
    * Fixed an issue where it was impossible to assign an admin user to a user role
* Service Contracts:
    * Implemented Service Contracts for the CatalogInventory Module
* Framework Improvements:
    * Added the ability to configure the list of loaded modules before installation
    * Added the ability to use the Composer autoloader instead of the Magento custom autoloaders for tests
    * Introduced a repository for storing a quote entity
* Performance improvements:
    * Split Magento\Customer\Helper\Data
* Processed GitHub requests:
    * [#731](https://github.com/magento/magento2/issues/731) -- Filter grid is absent on CMS Pages in Backend

0.1.0-alpha104
=============
* Various improvements:
    * Merge SQL and Data Upgrades for the Sales and SalesRule modules
    * Add getDefaultBilling and getDefaultShipping to Customer Interface
    * Stabilized the Bundle module
    * Stabilized the CatalogSearch module
    * Stabilized the Cms module
    * Stabilized the SalesRule module
* Performance improvements:
    * Introduced CatalogRule indexers based on Mview
    * Significantly decreased the amount of unused objects, mostly in category and product view scenarios:
        * Got rid of non-shared indexer instances all over the code introducing Magento\Indexer\Model\IndexerRegistry
        * Magento\Catalog\Pricing\Price\BasePrice being created on demand only, instead of unconditioned creation in constructor
        * Created proxies for unused objects with big amount of dependencies
        * Fixed \Magento\Review\Block\Product\Review block which injected backend block context by mistake
        * A customer model in \Magento\Customer\Model\Layout\DepersonalizePlugin being created on demand only, instead of constructor
    * Introduced caching for product attribute metadata loading procedure
    * Improved SavePayment Checkout step to save only payment related data
    * Speed up all Checkout steps of the One Page Checkout
    * Updated the benchmark.jmx jmeter script in the performance toolkit
* Fixed bugs:
    * Fixed an issue where performance toolkit generator created Products/Categories without URL rewrites due to install area elimination
    * Fixed an issue where the Custom Options fieldset on Product Information page was collapsible
    * Fixed an issue where the Base URL was added to target path for Custom UrlRewrite
    * Fixed an issue where an invalid Cross-sells amount was displayed in the Shopping Cart
    * Fixed an issue where the Mage_Catalog_Model_Product_Type_AbstractTest::testBeforeSave integration test failed when Mage_Downloadable module was not available
    * Fixed an issue where the custom URL rewrite redirected to sub-folder when Request Path contained slash
    * Fixed an issue where it was impossible to place an order if registering during checkout
    * Fixed an issue where there was no possibility to save default billing and shipping addresses for customer on the store front
    * Fixed an issue where a widget of Catalog Category Link type was not displayed on the store front
    * Fixed an issue where the Versions tab was absent on the CMS page with version control
    * Fixed an issue where it was impossible to insert Widgets and Images to a CMS page
* Added the following functional tests:
    * Create widget
    * Print order from guest on frontend
* Framework Improvements:
    * Removed duplicated logic from API Builders and Builder generators. Added support for populating builders from the objects, implementing data interface
* Processed GitHub requests:
    * [#674](https://github.com/magento/magento2/issues/674) -- Widgets in content pages

0.1.0-alpha103
=============
* Fixed bugs:
    * Fixed an issue where an error message was displayed after successful product mass actions
    * Fixed an issue where it is impossible to create a tax rate for all states (“*” in the State field)
    * Fixed an issue where FPT was not shown on the storefront if a customer state did not match the default state from configuration
    * Fixed the benchmark scenario
    * Fixed an issue where the expand arrow next to Advanced Settings tab label was not clickable
    * Fixed an issue where the Category menu disappeared when resizing a browser window
    * Fixed an issue where the order additional info was not available for a guest customer
    * Fixed an issue where a fatal error was thrown when trying to get a coupon report with Shopping Cart Price Rule set to Specified
    * Fixed an issue where the URL of an attribute set for attribute mapping changed after resetting filter for the grid on the Google Contents Attributes page
    * Fixed the implementation of the wishlist RSS-feed
    * Fixed the incorrect name escaping in wishlist RSS
    * Fixed an issue where a RSS feed for shared wishlist was not accessible
    * Fixed an issue caused by REST POST/PUT requests with empty body
    * Fixed an issues where postal code was still mandatory for non-US addresses that do not use it, even if set to be optional
    * Fixed an issue where it was impossible to find a wishlist by using Wishlist Search
    * Fixed an issue where no password validation was requested during customer registration on the storefront
* Updated setup tools:
    * Added the install script in the CatalogInventory module
    * Removed old installation: Web and CLI, the Magento_Install module, install theme, install configuration scope
    * Added usage of the new setup installation in all tests
    * Added the ability to insert custom directory paths in the setup tools
    * Added the uninstall tool: php -f setup/index.php uninstall
    * Removed dependency on intl PHP extension until translations are re-introduced in the setup tool
    * Made notification about unnecessarily writable directories after installation more specific
* UI improvements:
    * Improved UI for the Order by SKU, Invitation and Recurring Payments pages
    * Implemented usage of Microdata and Schema vocabulary for product content
    * Implemented UI for Catalog New Products List, Recently Compared Products, Recently Viewed Products widgets
    * Implemented a new focus indicator
    * Implemented the &lt;label&gt; element for form inputs
    * Put in order the usage of the &lt;fieldset&gt; and &lt;legend&gt; tags
    * Implemented the ability to skip to main content
* Added the following functional tests:
    * Add products to order from recently viewed products section
    * Update configurable product
* Various improvements:
    * Stabilize URL rewrite module
    * Moved getAdditional request into the basic one in OnePageCheckout
    * Created a cron job in the Customer module for cleaning the customer_visitor table
* Framework improvements:
    * Refactored data builders auto-generation
    * Implemented the Customer module interfaces
    * Ported existing integration tests from Customer services
    * Removed quote saving on GET requests (checkout/cart, checkout/onepage)

0.1.0-alpha102
=============
* Fixed bugs:
    * Fixed an issue where the categories tree was not displayed when adding a new category during product creation
    * Fixed an issue where the Template field on the New Email Template page was labeled as required
    * Fixed minor UI issues in Multiple Addresses Checkout for a desktop
    * Fixed minor UI issues with Widgets on the storefront
    * Fixed minor UI issues with pages printing view on the storefront
    * Fixed minor UI issues in items Gift message on the Order View frontend page
    * Fixed an issue in the Admin panel where no message was displayed after adding a product to cart with quantity exceeding the available quantity)
* Framework improvements:
    * To enhance the readability of tables for screen readers, added the <caption> tag and the “scope” attribute for tables
    * Added customer module interfaces
    * Created the ability to generate API documentation
* Added the following functional tests:
    * Create gift message in the Admin panel
    * Delete term
    * Product type switching when editing
    * Re-authorize tokens for the Integration
    * Revoke all access tokens for admin without tokens
    * Update custom order status
    * Update a product from a mini shopping cart
* WebApi Framework improvements:
    * Added Web API support to add/override matching identifier parameter in the body from URL
* Documentation:
    * Added README files with module description for the following modules:
        * Authorizenet
        * Centinel
        * Customer
        * CustomerImportExport
        * Dhl
        * Fedex
        * OfflinePayments
        * OfflineShipping
        * Ogone
        * PayPalRecurringPayment
        * Payment
        * Paypal
        * ProductAlert
        * RecurringPayment
        * Sendfriend
        * Shipping
        * Ups
        * Usps
        * Wishlist
* Container-Based Page Layout:
    * Distributed the responsibility of View\Layout between three classes (PageLayout, PageConfig, GenericLayout)
    * Refactored controller actions to use ResultInterface objects:
        * Catalog
        * Backend

0.1.0-alpha101
=============
 * Framework improvements:
  * Updated the Service infrastructure to support Module Service Contract based approach
  * Added new base classes in the Service infrastructure lib to support extensible Data Interfaces
  * Updated the WebApi framework serialization (for SOAP and REST) to process requests based on Data Interfaces and removed dependency on Data Objects
  * Added base class for Data Interface based builders and implemented a code generator for the same
 * File system improvements:
   * List of available application directories is complete now and defined in the \Magento\Framework\Filesystem\DirectoryList and the \Magento\Framework\App\Filesystem\DirectoryList classes. There is no ability to extend the list in configuration
   * Directory paths  can be changed using environment/bootstrap
   * Information about necessary permissions (writable, readable) belongs to Setup Application, Magento Application does not possess this info and does not verify. Setup Application performs permissions validation
   * Unnecessary writable permissions are validated by Setup Application after installation and corresponding message is displayed to the user
 * Functional tests:
  * Configure a product in a customer wishlist  in the Admin panel
  * Configure a product in a customer wishlist on the storefront
  * Create terms and conditions
  * Manage products stock
  * Move a product from a shopping card to a wishlist
  * Un-assign custom order status
  * Update terms and conditions
  * Update URL rewrites after moving/deleting a category
  * Update URL rewrites after changing category assignment for a product
  * View customer wishlist  in the Admin panel
  * Tax calculation test
  * Cross border trade setting
 * Documentation:
  * Code documentation:
    * Added codeblock for the Checkout module
  * Added README files with module description for the following modules:
    * Backend
    * Backup
    * Cron
    * Log
    * PageCache
    * Store
    * Checkout
    * GiftMessage
    * Eav
    * Multishipping
    * CheckoutAgreement
    * AdminNotification
    * Authz
    * Connect
    * CurrencySymbol
    * Directory
    * Email
    * Integration
    * Service
    * User
    * Webapi
    * Sales
    * Tax
    * Weee
  * Added README files with component description for the following framework components:
    * Magento\Framework\App\Cache
    * Magento\Framework\Archive
    * Magento\Framework\Backup
    * Magento\Framework\Convert
    * Magento\Framework\Encryption
    * Magento\Framework\File
    * Magento\Framework\Filesystem
    * Magento\Framework\Flag
    * Magento\Framework\Image
    * Magento\Framework\Math
    * Magento\Framework\Option
    * Magento\Framework\Profiler
    * Magento\Framework\Shell
    * Magento\Framework\Stdlib
    * Magento\Framework\Validator
 * Performance improvements:
  * Reduced checkout response time by loading only current checkout step
  * Reduced the number of AJAX calls on checkout steps
  * Improved performance on the billing and shipping checkout steps
  * Improved performance in certain areas by loading translation data from cache
  * Removed transactions from visitors logging
  * Fixed classmap generator to consider namespaces
  * Eliminated a redundant query for category tree rendering
  * Optimized StoreManager and Storage performance
  * Optimized Object Manager
 * Fixed bugs:
  * Fixed an issue where partial invoices and partial credit memos contained incorrect customer's tax details
  * Fixed an issue where a PHP fatal error occurred when logging in during checkout to order a product with FPT
  * Fixed an issue where FPT was not calculated in reorders
  * Fixed an issue where there was a duplicated Administrator role after installation
  * Fixed an issue where the Try Again button was disabled after entering the incorrect data during installation
  * Fixed an issue where the "Application is not installed yet" error was thrown instead of redirecting to the Installation Wizard in the developer mode
  * Fixed an issue where an error was thrown during installation with db_prefix option
  * Fixed an issue where the SQL query was not optimized for product search ('catalogsearch_query')
  * Fixed an issue where the wrong message was displayed after changing customer password on the storefront
  * Fixed an issue where Newsletter preview led to an empty page
  * Fixed an issue where a new search term was not displayed in suggested results
  * Fixed an issue where no results were found for the Products Viewed report
  * Fixed an issue where no results were found for Coupons reports
  * Fixed an issue with incremental Qty setting
  * Fixed an issue with allowing importing of negative weight values
  * Fixed an issue with Inventory - Only X left Treshold being not dependent on Qty for Item's Status to Become Out of Stock
  * Fixed an issue where the "Catalog Search Index index was rebuilt." message was displayed when reindexing the Catalog Search index
 * Search module:
  * Integrated the Search library to the advanced search functionality
    * Substituted the old logic of the EAV attributes search by Advanced Search
    * Introduced mappers for MySQL adapter
    * Restored  the currency calculation functionality
    * Fixed sorting by relevance in quick search and advanced search
  * Integrated the Search library into the search widget functionality
    * Removed the dependency on the catalogsearch_result table
    * Substituted the old logic of EAV attributes by Quick search APIs
  * Search modularity:
    * Removed circular dependency between Catalog and  Catalog Search
    * Removed exceeded dependencies of the Search module

0.1.0-alpha100
=============
 * Added the following functional tests:
   * Add related products
   * Assign custom order status
   * Change customer password
   * Create credit memo for offline payment methods
   * Product type switching on creation
   * Sales invoice report
   * Sales refund report
   * Update newsletter template

0.1.0-alpha99
=============
 * Released Performance Toolkit
 * GitHub requests:
   * [#665](https://github.com/magento/magento2/issues/665) -- Main menu event in wrong area
   * [#666](https://github.com/magento/magento2/pull/666) -- Update di.xml
   * [#602](https://github.com/magento/magento2/issues/602) -- Magento\Sales\Model\Order::getFullTaxInfo() incorrectly combines percentages
 * Functional tests:
   * Updated API-functional test for Customer and Address metadata service
   * Add cross sell
   * Add a product to wishlist
   * Add up sell
   * Checkout with gift messages
   * Create an order from a customer
   * Create a shipment for offline payment methods
   * Delete a product from mini shopping cart
   * Reorder
   * Sales order report
   * Updating URL rewrites from a category page
 * Layout updates:
   * Moved layout files to the page_layout directory
   * Moved layout validation files to framework
 * Theme updates:
   * Blank Theme layouts & templates were unified
 * Search Library:
   * Added ability to aggregate queries for MySQL adapter
   * Implemented automatic range aggregation for MySQL adapter
 * Search module:
   * Introduced the Search module
   * Moved autocomplete to the Search module
   * Added base UI to the Search module
 * Documentation:
   * Added basic description of modules in the README.md files
 * Modularity:
   * Created API and script to get module and dependency information
 * Framework Improvements:
   * Decomposed heavy objects basing on profiling results
   * Refactored the getCustomAttributesCodes method in ProductService
   * Refactored Customer Model to use Group Model instead of Group Service
   * Updated Travis configuration to run "composer install"
 * Performance improvements:
   * Removed unnecessary "save order" call during order submission step
 * Fixed missing installation features of the new setup:
   * Added missing installation parameters: admin_no_form_key, order_increment_prefix, cleanup_database
   * Fixed the link to the license agreement in web installer
   * Fixed the web installation wizard which was stuck at 96%
 * Fixed bugs:
   * Fixed fatal error during installation
   * Fixed an issue where newly created attribute was always added to the Product Details tab
   * Fixed an issue where it was impossible to change the Stock Availability status of a product from the Advanced Inventory tab
   * Fixed an issue where the Stock Status value changed from In Stock to Out of Stock if quantity was not specified
   * Fixed an issue where performance toolkit failed in case of unknown argument
   * Fixed an issue where 404 error page was displayed instead of the URL Rewrite Information page
   * Fixed an issue where the Click for price link was not working if a product name contained quote mark
   * Fixed an issue where the Compare products link disappeared after switching to other page
   * Fixed an issue where the custom logo was not displayed on the category page
   * Fixed an XSS vulnerability in category name
   * Fixed an issue where a success save message was not displayed after saving a Search term
   * Fixed an issue with Google Analytics where it was impossible to add the code to the pages
   * Fixed an issue where import custom options grid was not displayed on the product creation page
   * Fixed an issue where it was impossible to retrieve a product collection from category in the "adminhtml" area
   * Fixed an issue where product attributes were absent on product creation form after switching to another product template
   * Fixed an issue where the 'URL key for specified store already exists.' error message was displayed when saving a configurable product with variations which have the same name
   * Fixed an issue where search in the Search Terms Report grid did not work
   * Fixed an issue where the unnecessary tab "General" was displayed on the Category page in the Admin panel
   * Fixed an issue where the Stock Status value changed from In Stock to Out of Stock if quantity was not specified for a configurable product when saving to a new template
   * Fixed an issue where product Stock Status was always set to 'In Stock' if product quantity was specified
   * Fixed an IE specific issue where for bundle products the Manage Stock option was reset to Yes
   * Fixed an issue where backorder messages were not displayed
   * Fixed an issue where the Price field was always required during Bundle product update using ProductService
   * Fixed an issue where product name was missing in the error message
   * Fixed an issue where configurable product did not contain a message to select options while adding product from wishlist to shopping cart
   * Fixed an issue where the Validate VAT Number button did not work during order creation in the Admin panel
   * Fixed an issue where Item qty in Wishlist got reset after update without changes
   * Fixed an issue where invoice amount was incorrect when items with discount were partially invoiced
   * Fixed product thumbnails alignment in the storefront
   * Fixed an issue where inactive Categories were not greyed out in the tree in the Admin panel
   * Fixed an issue where it was impossible to disable debug mode
   * Fixed the code sample in the index.php file
   * Removed language selector in the setup UI
   * Fixed an issue where setup was broken if db_prefix was used
   * Implemented usage of Symfony's PHPExecutableFinder for executing CLI tools
   * Fixed an issue with the Import/Export functionality
   * Fixed an issue with catalog product/category and category/product indexers invalidation after import
   * Fixed an issue with entering invalid date in the Product Views Report
   * Fixed an issue where it was impossible to view orders for customers from a deleted customer group
   * Fixed an issue where a duplicate customer record was created after adding an order from the Admin panel
   * Fixed an issue where it was impossible to log in to the Admin panel from the first attempt

0.1.0-alpha98
=============
 * GitHub requests:
   * [#678] (https://github.com/magento/magento2/issues/678) -- Fixed Travis CI builds
 * Functional tests:
   * Create Sales Order Backend
   * Delete Products from Wishlist
   * Download Products Report
   * Mass Orders Update
   * Sales Tax Report
 * Fixed bugs:
   * Fixed an issue where success message was not displayed after product review submit
   * Fixed an issue where it was impossible to start checkout process using PayPal from the JavaScript pop-up window when the Display Actual Price option was set to On Gesture
   * Fixed an issue where a fatal error was thrown after shipping method selection in PayPal Express Checkout
   * Fixed an issue with parameters exceptions in SOAP response
   * Fixed an issue where testGetRequestTokenOauthTimestampRefused unit test failed in certain cases
   * Fixed an issue where TestCreateCustomer test thrown fatal error when making a SOAP request
   * Fixed an issue with required parameters in WSDL
   * Fixed an issue where Customer Account Service returned void response in the resetPassword method
   * Fixed an issue where REST API failed during bundle product creation

0.1.0-alpha97
=============
 * Various improvements:
   * Implemented a general way of using RSS module
   * Created a cron job in the Customer module for cleaning the customer_visitor table
   * Added a warning message to the Use HTTP Only option in the Admin panel
   * Implemented the Grid component in the Magento UI Library
   * Reimplemented the URL Rewrites functionality in the new UrlRedirect module
 * Framework improvements:
   * Added the ability to install Magento 2 using CLI
   * Aggregated Magento installation and upgrade into one tool
   * Refactored CustomerService REST WebApi to be more RESTful
   * Increased unit and integration test coverage
   * Moved page asset management to page configuration API, and eliminated the \Magento\Theme\Block\Html\Head block
   * Eliminated the Root, Html and Title blocks
 * Themes update:
   * Removed widgets from the default Magento installation
 * Fixed bugs:
   * Fixed an issue with wishlist creation for non-registered customer
   * Fixed an issue with Google Mapping where Condition did not show correct value
   * Fixed an issue  where there were too many notifications for admin user by default
   * Fixed a Daylight Savings Time calculation error
   * Fixed an issue where default cookie path and lifetime were not validated prior to saving
   * Fixed an issue where current admin password was not required for resetting admin password
   * Fixed an issue where custom customer attribute or customer address attribute was not accessible when ‘custom_attribute’ is used as the attribute code
   * Fixed an issue where integration entity could not be deleted after being searched in grid
   * Fixed an issue where invalid parameter value was shown in SOAP
   * Fixed an issue where exception was thrown for Array to String conversion in SOAP
   * Fixed an issue where exception was thrown due to invalid argument supplied for foreach() statement in REST
   * Fixed an issue where admin tax notifications did not appear correctly in the System Messages dialog box
   * Fixed an issue where tax details were missing when viewing order in the Admin panel
   * Fixed an issue where styles for the storefront store selector were absent
   * Fixed an issue where customer got 404 page when switching store views on the product page of a product with different URL keys in different store views
   * Fixed an issue where the Add To Cart button in the MAP pop-up did not work for configurable and bundle products
   * Fixed an issue where for specifying options for configurable product was absent after adding a product from the MAP pop-up
   * Fixed an issue where a fatal error was thrown after selecting shipping method on PayPal Express Checkout
   * Fixed an issue with sending invoice email
   * Fixed an issue where integration tests failed with a fatal error
   * Fixed an issue where credit memo entry was not created after performing a refund for an order
   * Fixed an issue where categories layout for widgets did not work
   * Fixed an issue where opening a page restricted by ACL lead to blank page instead of the Access Denied page
   * Fixed an issue where a blank page was displayed instead of the using the Advanced Search result
   * Fixed an issue where the "Please wait" spinner was absent on Ajax requests for order creation in the Admin panel
   * Fixed an issue with the main navigation menu location on the page
 * Modularity:
   * Implemented the automatic applying of the MAP policy
 * Indexers:
   * Eliminated the old Magento_Index module
 * Search library
   * Added wildcards filter
   * Eliminated unused queries and filters
   * Added IN to Term filter
   * Moved the "value" attribute from <match> to <query> for the Match query
   * Refactored the usage of negation
   * Implemented Request Builder
 * CatalogSearch adapter
   * Pluginized adding attribute to search index
   * Merged base declaration with searchable attributes
 * Added the following “Setup CLI tools” in the setup folder
   * Deployment Configuration Tool
   * Schema Setup and Update Tool
   * DB Data Update Tool
   * Admin User Setup Tool
   * User Configuration Tool
   * Installation Tool
   * Update Tool
 * GitHub requests:
   * [#615] (https://github.com/magento/magento2/issues/615) -- Use info as object in checkout_cart_update_items_before
   * [#659] (https://github.com/magento/magento2/issues/659) -- Recently viewed products sidebar issue
   * [#660] (https://github.com/magento/magento2/issues/660) -- RSS global setting
   * [#663] (https://github.com/magento/magento2/issues/663) -- session.save_path not valid
   * [#445] (https://github.com/magento/magento2/issues/445) -- use of registry in Magento\Tax\Helper\Data
   * [#646] (https://github.com/magento/magento2/issues/646) -- Fixed flat category indexer bug
   * [#643] (https://github.com/magento/magento2/issues/643) -- Configurable Products Performance
   * [#640] (https://github.com/magento/magento2/issues/640) -- [Insight] Files should not be executable
   * [#667] (https://github.com/magento/magento2/pull/667) -- Tiny improvement on render() method in Column/Renderer/Concat
   * [#288] (https://github.com/magento/magento2/issues/288) -- Add Cell Phone to Customer Address Form
   * [#607] (https://github.com/magento/magento2/issues/607) -- sitemap.xml filename is not variable
   * [#633] (https://github.com/magento/magento2/pull/633) -- Fixed Typo ($_attribite -> $_attribute)
   * [#634] (https://github.com/magento/magento2/issues/634) -- README.md contains broken link to X.commerce Agreement
   * [#569] (https://github.com/magento/magento2/issues/569) -- ObjectManager's Factory should be replaceable depending on service
   * [#654] (https://github.com/magento/magento2/issues/654) -- Demo notice overlapping
 * Functional tests:
   * Abandoned carts report
   * Adding products from wishlist to cart
   * Create invoice for offline payment methods
   * Delete products from shopping cart
   * Delete widget
   * Global search
   * Order count report
   * Order total report

0.1.0-alpha96
=============
 * Framework improvements:
   * Increased unit tests code coverage for Magento_Persistent, Magento_GiftMessage, Magento_Checkout modules
 * Modularity:
   * Removed module dependency on the Weee module
 * Fixed bugs:
   * Fixed an issue in composer installation where Magento/Framework marshaling did not work
   * Fixed an issue where shipping tax was included twice in tax details
   * Renamed the getDistinct method in Tax Model
   * Fixed an issue where it was impossible to reorder and create a new order in the Admin panel if some fields of the order were specified incorrectly and the page was reloaded
   * Fixed an issue where the Configure link was not displayed in the Product Requiring Attention section
   * Fixed an issue where Magento could only be installed in the host root directory
   * Fixed an issue where no proper error message was displayed if vendor directory did not exist in the setup tool
   * Fixed an issue where a fatal error was thrown during checkout with multiple addresses
   * Fixed an issue where integration tests failed if prefixes for tables were used
 * Checkout API:
   * Created Customer Shopping Cart Service
 * Price template refactoring
   * Introduced a single interface for price and tax calculation logic
 * Functional tests:
   * Add products to shopping cart
   * Bestseller products report
   * Cancel created order
   * Delete customer address
   * Hold created order
   * Ordered products report
   * Sales coupon report
 * GitHub requests:
   * [#662] (https://github.com/magento/magento2/issues/662) -- Composer Installation

0.1.0-alpha95
=============
 * Modularity
   * Log module became switchable
   * New switchable module TaxImportExport was created
 * Sales module improvement: 
   * Performance was improved
   * Complexity of the order persistence logic was reduced
 * Unit tests coverage for modules was increased:
   * Magento\Rule
   * Magento\Contact
 * Framework:
   * Composite and bundle save/load processors were added
   * Support for the complex custom attributes were added
   * Generic abstract data objects, that is simple and extensible (supports custom attributes), were created  
 * Search Library:
   * Approach of matching the fields to table names was implemented
   * MySQL Adapter Library for Match and Filtered query types was added
   * Ability to filter queries was added
   * Response handler for MySQL adapter was added
   * XML declarations for full-text search were added
 * Functional tests:
   * Add Products to Order from Last Ordered Products Section
   * Add Products to Order from Products in Comparison List Section
   * Add Products to Order from Recently Compared Products Section
   * Create Configurable Product
   * Create Store
   * Create Website
   * Delete Product From Customer Wishlist On Backend
   * Delete Store
   * Delete Website
   * Viewed Products Report
   * Products In Cart Report
   * Manage Product Review from Customer Page
   * Mass Assign Customer Group
   * New Account Report
   * Update Product Review From Product Page
   * Update Store
   * Manage Product Review From Customer Page
 * Other:
   * Session.name ini set
   * Calls to setPublicCookie became more secured
   * Generating the session ID for sensitive data was added
 *  Fixed bugs:
   * Placing the order from backend
   * Redirecting the customer to empty shopping cart instead of displaying credit card iFrame on checkout with for PayPal Payflow Link
   * Showing the  message for multiple shipping address checkout in Authorize partial approval flow
   * Mess detector failure
   * flv_player security vulnerability
   * Calling the inexistent method in cart with shopping cart price rules
   * Overriding a non-empty custom attribute value with empty value in store view scope
   * Editing  in 'WYSIWYG editor' by clicking "Use Default" checkbox when switched to store view scope
   * RSS list page vulnerability
   * Applying the store View title on frontend for configurable attributes
   * Viewing the uploaded sample in downloadable product
   * Google Shopping: Problem with publishing products if change value for option 'Update Google Shopping Item when Product is Updated'
   * Configuration scope of items' InStock status on order cancellation
   * Creating the new customer in admin
 * GitHub requests:
   * [#621] (https://github.com/magento/magento2/issues/621) -- Parse error: syntax error, unexpected T_OBJECT_OPERATOR
   * [#651] (https://github.com/magento/magento2/issues/651) -- Multishipping checkout add/edit address page issue

0.1.0-alpha94
=============
 * Implemented API services:
   * Sales transactions
 * Added the following functional tests:
   * Create Store Group
   * Customer Review Report
   * Delete Store Group
   * Update Store Group
 * Improved error reporting when ini_set fails
 * Increased unit test coverage for the following modules:
   * SalesRule
   * Payment
 * Checkout API:
   * Create Shopping Cart Gift Message service
   * Create Shopping Cart Totals service
 * Fixed bugs:
   * Fixed an issue where selecting a shipping method in PayPal Express Checkout resulted in a fatal error
   * Fixed an issue where the information displayed on the Payment Information step of Zero Subtotal Checkout was confusing
   * Fixed a JavaScript error in shipping label
   * Fixed an issue with wrong layout of the storefront pages
   * Fixed an issue where the price including tax value was incorrect on catalog pages when customer tax rate is different from store tax rate
   * Fixed an issue where fixed product tax (FPT) was not included in the Grand total when 'Include FPT in Subtotal' was set to Yes
   * Fixed an issue where Shipping Incl. Tax amount was not updated when changing shipping method
   * Fixed an issue where the store tax configuration was ignored during backend order creation
   * Fixed an issue where taxes were not applied in the shopping cart after registering customer on the storefront
   * Fixed an issue where the wrong html markup was generated on My order pages for the WEEE tax
   * Fixed an issue where the built-in caching did not work on product pages
   * Removed the stream resource usage to avoid errors when the allow_url_fopen PHP option is set to Off
   * Fixed the New Return page layout on the backend
   * Fixed an issue where it was impossible to apply a specific coupon code when the Apply to Shipping Amount option of the Shopping Cart Rule was set to Yes
   * Removed file paths/content from test case names in data-driven tests
   * Fixed an issue where pagination was absent in the Order Status grid
   * Fixed an issue where after applying a discount coupon and changing the currency the discount value was incorrect
   * Fixed an issue where trying to a new rating resulted in a fatal error
   * Fixed an issue where the minimum order amount was compared with subtotal without taxes
   * Fixed an issue where it was impossible to open the previous step during Onepage Checkout
   * Fixed an issue with Persistent Shopping Cart where an unexpected message was displayed during checkout if a user started the checkout after the short-term cookie had expired
   * Fixed an issue where a customer was redirected to the shopping cart after selecting shipping method during checkout with a payment method using 3D Secure
   * Fixed an issue where the Cart Item service used itemSku instead itemId
   * Fixed an issue where gift messages for individual items were not saved during backend order creation
   * Fixed an issue where the Purchase Order Number input field was not displayed in Onepage Checkout if only one payment method was enabled
 * GitHub requests:
   * [#446] (https://github.com/magento/magento2/issues/446) -- Rounding different in order to original quote calculation

0.1.0-alpha93
=============
 * Price template refactoring
   * Refactored order item templates in the Sales, Bundle and Downloadable modules
   * Eliminated the unused PHTML templates and removed the direct dependencies on the TaxHelper module in the Catalog module
 * Service layer implementation:
   * Created service layer for Order creation
   * Created service layer for Invoice
   * Created service layer for Credit Memo
   * Created service layer for Shipment
 * Introduce the Search library:
   * Created adapter interfaces for the Search library
   * Created response structure
   * Created parsing of XML declaration and creation of library objects (Queries, Filters, Aggregations)
 * Refactored Framework\Stdlib\Cookie to use CookieManager
 * Added the ability to prevent the backend cookie from going to the storefront
 * Fixed bugs:
   * Fixed an issue where taxes  were  not added in some orders
   * Fixed an issue were the Add New Address button did not work if the default address was already set
   * Fixed a Google Chrome and Internet Explorer specific issue when a JavaScript error made it impossible to register   during checkout downloadable product
   * Fixed an issue when the credit card iframe (PayPal or 3D secure)  was absent on the Order Review step during Onepage Checkout
   * Fixed an issue with the   Tax Rate, Customer Tax Class and Product Tax Class multiselects on the Tax Rule Information page
   * Fixed JavaScript issues which prevented saving a newsletter template.
   * Modified the Button component behavior
   * Fixed an issue where it was impossible for a guest customer to register during Onepage checkout when the Require Customer To Be Logged In To Checkout option was set to Yes
   * Fixed an issue where the Calendar icons were not displayed on the storefront
   * Fixed an  AJAX loader issue in the Admin panel
   * Fixed an issue where it was impossible to upload images for  variations of a configurable product on product form
   * Fixed an issue where clicking on a row in the Search Terms Report Grid leads to 404 page
   * Fixed an issue where configurable products fixture creates out of stock products
   * Fixed an issue where Magento crashed when invalid cookie domain was set
   * Fixed an issue where the Change checkbox label overlapped the text message for a recurring profile attribute on the attribute mass update page
   * Fixed an issue where integrity test determined normal dependencies as redundant
   * Fixed an issue where Catalog\Service\V1\Product\Attribute\ReadService::search returned an error
   * Fixed an issue where Magento\Catalog\Service\V1\Category\Attribute\ReadService::options returned empty results
 * GitHub requests:
  * [#160] (https://github.com/magento/bugathon_march_2013/issues/160) -- Wrong default value for memory_limit in .htaccess.sample
  * [#480] (https://github.com/magento/magento2/pull/480) -- Provide instructions on adding memcache support for Magento 2
  * [#612] (https://github.com/magento/magento2/issues/612) -- Category Layered Navigation : Selection of disabled entity
  * [#626] (https://github.com/magento/magento2/issues/626) -- Unable to install under IIS / FastCGI

0.1.0-alpha92
=============
 * Implemented API services:
   * Shopping Cart Payment
   * Shopping Cart Shipping
   * Shopping Cart Coupon
   * Shopping Cart License Agreements
 * Indexer for Fulltext Search
 * RSS Module become removable
 * Framework Improvements:
   * Ability to drop/regenerate access for native mobile apps
   * Ability to support extensible service data objects
   * No Code Duplication in Root Templates
 * Fixed bugs:
   * Persistance session application. Loggin out the customer
   * Placing the order with two terms and conditions
   * Saving of custom option by service catalogProductCustomOptionsWriteServiceV1
   * Placing the order on frontend if enter in the street address line 1 and 2 255 symbols
   * Using  @357.farm domain emails in registration form
   * Validation for country_id/region_id and percentage_rate during Tax Rate creation
   * Declaration of getSortOrders in Magento\Framework\Api\SearchCriteria
   * Order cancellation for online payment methods
   * Order online processing for Authorize.net Direct Post
   * Backend grids while search
   * Adding of downlodable sample block on product page
   * Variations on duplicated configurable product
 * Added functional tests:
   * Product Review Report
   * Share Wishlist

0.1.0-alpha91
=============
 * Added the following functional tests:
   * Action Newsletter Template
   * Import Custom Options
   * Low Stock Products Report
   * Search Terms Report
 * Catalog:
   * Removed the unused old pricing .phtml templates
   * Removed direct dependencies on the Weee and Tax modules
 * Tax:
   * Added new price renderers for the Weee and Tax modules
 * Fixed the @covers annotation in Integration tests
 * Fixed bugs:
   * Fixed an issue with FPT total line on the Shopping Cart page
   * Fixed the Inline translation functionality both in the backend and the storefront
   * Fixed an issue with the Translation dialog layout on the storefront
   * Fixed an issue where only the first Tier Price row was saved during simple product creation
   * Fixed an issue where it was impossible to save more than one group price
   * Fixed an issue where it was impossible to create a shipping label
   * Fixed an issue where Google Items synchronization resulted in a blank page
   * Fixed an issue where a Shopping Cart with a lot of entries did not fit the page
   * Fixed an issue where a JavaScript error blocked the checkout with credit cards type “Other” in online payment methods
   * Fixed JavaScript error on the Payment Methods tab in System Configuration

0.1.0-alpha90
=============
 * Service layer implementation:
   * Created the Admin Shopping Cart Service
   * Created the Create Shopping Cart Items Service
   * Created the Create Shopping Cart Shipping Address Service
   * Created the Create Shopping Cart Billing Address Service
   * Created the Service Layer for Orders
   * Created CRUD service & APIs to manage options for configurable products
   * Created CRUD service & APIs to manage options for bundle products
 * Fixed bugs:
   * Fixed an issue where adding a customer address with an invalid value of the custom address attribute caused a fatal error in SOAP
   * Fixed an issue where the wrong FedEx rates were displayed
   * Fixed an issue where the Bill Me Later option did not work in Payflow payment methods
   * Fixed an issue where order comments were broken for orders placed with Authorize.net
   * Fixed the naming of the My Account -> Recurring Payment page
   * Fixed a UI elements issue in the disabled Magento_PayPalRecurringPayment and Magento_RecurringPayment modules
   * Fixed an issue where it was impossible to save configuration of a configurable product when adding it to an order in the Admin panel
   * Fixed an issue where the Select a store page was displayed during admin order creation when the Single Store mode was enabled
   * Fixed an issue when an exception was thrown when attempting to open the Customer Account page if the Recently Viewed widget was configured for the store
   * Updated the content of the Privacy Policy page
   * Fixed an issue where it was possible to update a tax rate using the POST http method
   * Fixed an issue where it was impossible to update Inventory Qty for a SKU using API
   * Fixed a JavaScript syntax error on the Create New Customer page
   * Fixed an issue where it was impossible to add new sample while creating a downloadable product
   * Fixed a JavaScript which appeared when clicking the Add New Address button in the Address Book on the storefront
   * Fixed an issue where it was possible to update Tax Rules using the PUT http method which is supposed to be used for create operation only
   * Fixed an issue where it was possible to create a Tax Rule specifying a product tax class instead of a customer tax class and vice versa
   * Fixed an issue with making websiteId a mandatory field when updating a customer using REST
   * Fixed an issue where the default value was not applied after clicking the 'Use default' link for a product price field in the catalog in the Admin panel
   * Fixed an issue where the price update mass action could not be performed
   * Fixed a JS error in the cross-sells product settings in the Admin panel
 * Added the following functional tests:
   * Mass Delete Backend Customer
   * Moderate Product Review
 * Framework improvements:
   * Added the ability to access admin functionality using admin user login for mobile
   * Refactored and unified Access Control List (ACL) to make it more consistent
   * Created a Cookie Manager (a cookie management class)
 * Changes in functional tests:
   * Enabled the CustomerMetadataService tests for SOAP
 * Themes update:
   * Fixed issues in the Blank theme
   * Implemented improvements for the Blank theme, core templates and Storefront UI Library
 * Modularity:
   * Created the Notification library component and made it possible to disable the AdminNotification module
   * Made it possible to disable the SendToFriend module
   * Created an optional ConfigurableImportExport module to remove dependency between the CatalogImportExport and ConfigurableProduct modules
   * Created an optional GroupedImportExport module to remove dependency between the CatalogImportExport and GroupedProduct modules
 * Introduce search library:
   * Created a Search request configuration
   * Created a Query object structure from the XML declaration
 * Composer Integration:
   * Added support for using 3rd-party components as Composer packages

0.1.0-alpha89
=============
* Fixed bugs:
  * Fixed an issue where the Price indexer did not pass successfully from console after the first run
  * Fixed an issue where deleted items were displayed in the Mini shopping cart
  * Fixed an issue with the Mage_Sales_Model_OrderTest  unit test violating the Cyclomatic and NPath complexity requirements
  * Fixed an issue where taxes were not applied for logged in users
  * Fixed a JavaScript issue where the Checkout with PayPal button did not redirect to the PayPal site
* Framework improvements:
  * Removed the head.js library and its calls
  * Implemented the usage of RequireJS for runtime resources loading on the storefront
* Added the following functional tests:
  * Create Backend Product Review
  * Delete Used in Configurable Product Attribute
  * Delete Search Term
  * Mass Actions for Product Review
  * Mass Delete Search Term
  * Reset Currency Symbol
  * Update Currency Symbol
  * Update Grouped Product
* Added composer.json for all the Magento components: modules, language packs, themes and the whole Magento framework
* Removed the downloader, the Magento_Connect module and the Magento_Connect framework component
* Implemented the “alpha-version” of the Independent Deployment Tool

2.0.0.0-dev88
=============
* Fixed bugs:
  * Fixed an issue when PayPal Express Checkout Payflow Edition and PayPal Payments Advanced were available for multiple checkout
  * Fixed an issue when the Bill me later button did not redirect to https when secure url was enabled for frontend
  * Fixed an issue when the Billing agreement option was available in multishipping checkout, even if there were no signed agreements
  * Fixed an issue when DoExpressCheckout request instead of DoCapture did not allow to do refund, when using PayPal Express Checkout Payflow Edition
  * Fixed an issue when eWay was not present on checkout if Base Currency was set to AUD
  * Fixed an issue with fatal error occurring when placing order via SagePay with 3D Secure enabled
  * Fixed an issue when the FedEx shipping method had no option to specify unit for weight attribute
  * Fixed an issue with inability to create credit memo for PalPal Express Checkout/Payments Pro/Payments Pro Hosted Solution (NVP family), if partial refund was initiated on the PayPal side
  * Fixed an issue when a guest user could not return product to store, if product was paid using PayPal
  * Fixed an issue when PayPal Payments Pro Hosted Solution Fraud protection did not work properly
  * Fixed an issue when JavaScript took values from default config for payment methods and used them on the website scope
  * Fixed an issue with incorrect address in request to shipping carrier (DHL International) in case the address contained diacritic letters
  * Fixed an issue when it was possible to hack currency in PayPal Website Payments Standard
  * Fixed an issue when no rows were added to the PayPal Settlement report grid while fetching it from custom server
  * Fixed an issue when order had the Suspected Fraud status after creating partial invoice on the PayPal side
  * Fixed an issue when PayPal Payflow Pro did not properly implement CUSTREF and INVNUM
  * Fixed an issue with PayPal errors handling during IPN postback
  * Fixed an issue when the Paypal Express Checkout button was not available on product page for several product types
  * Fixed an issue with PayPal Payflow Pro and Payflow Link broken unit tests
  * Fixed an issue when PayPal Payments Pro Hosted Solution had the City parameter duplicated in the State parameter for UK
  * Fixed an issue with remove multiple HTTP 100/101 headers
  * Fixed an issue when SagePay did not transfer shopping cart information
  * Fixed an issue when transaction records were absent on Transaction tab for Ogone
  * Fixed an issue when partial cancel with SagePay Direct was unavailable
  * Fixed an issue when order did not place using PayPal Payments Pro Hosted Solution
  * Fixed an issue when order did not place using Authorize.net Direct Post from backend
  * Fixed an issue when sort order for payment methods did not work
  * Fixed an issue with multiple schema of language.xml
  * Fixed an issue with infinite loop in language inheritance
  * Fixed an issue with residual "scopes" logic in i18n implementation
  * Fixed an issue when search did not work for the CMS Blocks grid
  * Fixed an issue when WSDL for one scope was cached and displayed for all scopes
  * Fixed an issue with unit tests coverage build failure
  * Fixed an issue when custom options were lost after product import
  * Fixed an issue when product did not show in backend grid if store contained several store view
  * Fixed an issue when the Recurring Profile section was not updated after changing product template
  * Fixed an issue with incorrect discount calculation
  * Fixed an issue when customer could not register during Checkout if Guest Checkout was disabled
  * Fixed an issue when shopping cart price rule was not applied after updating items and qty in the shopping cart
  * Fixed an issue when updated and created dates were not shown for Billing Agreement in the Billing Agreement Grid in the backend
  * Fixed an issue with broken design on the multiple addresses order review page
  * Fixed an issue when sort by did not work in frontend for Yes/No attributes when Flat catalog was disabled
  * Fixed an issue when a new blank CMS page was displayed after saving the CMS page entity
  * Fixed an issue when product attributes were absent on the Product page after switching to another product template
  * Fixed a 404 error after saving mass update product attributes form
  * Fixed an issue when it was impossible to perform search by all tax classes on the Advanced Search page
  * Fixed an issue when attribute order for configurable product was not preserved after saving product
  * Fixed an issue with no results for the Product Best Sellers report
  * Fixed a fatal error when opening tax configuration page in the backend
  * Fixed an error occurring when opening the Tax Zones and Rates page in the backend
  * Fixed a 404 error occurring while searching products on the New Review page
  * Fixed an error when performing search in the Tax rate grid
* Payments implementation:
  * Ported correct behaviour for Fraud Management in PayPal Payflow Pro from M1 to M2
  * Implemented ability to use negative line items for PayPal Payflowpro
* Language packs:
  * Implemented ability to use multiple packages for the same language from one vendor
* GitHub requests:
  * [#587] (https://github.com/magento/magento2/issues/587) --  The "install/Magento/basic/*_*/layout/*.xml" pattern cannot be processed in "/mnt/fs01/test/mdt/htdocs/app/design/" path Warning!Invalid argument supplied for foreach()
* Unit tests coverage:
  * Magento\Catalog\Model\Product
* Service layer implementation:
  * Created ConfigurableProduct service
  * Created CompositeProduct service
  * Refactored TaxCalculationService
  * Refactored Google Shopping to use tax service
  * Exposed TaxRate and TaxRule search functions as WebAPI TaxCalculationService
  * Refactored QuoteDetails and QuoteDetailsItem to use tax class name
  * Refactored gift wrapping to use tax/weee services
  * Performed more tax refactoring for service layer
  * Improved unit test coverage
* Indexer-less implementation of URL Rewrites functionality in new UrlRedirect module:
  * Implemented URL Rewrites generators for all entities: CMS page, product, category
  * Implemented URL Rewrites matching in the frontend
* Added the following functional tests:
  * Activate Integration
  * Add Compared Products
  * Create Bundle Product
  * Clear All Compare Products
  * Create CMS Block
  * Create CMS Page
  * Create Custom Variable
  * Create Integration
  * Create Grouped Product
  * Create Search Term
  * Delete Assigned to Template Product Attribute
  * Delete CMS Block
  * Delete CMS Page Rewrite
  * Delete Compare Products
  * Delete Custom URL Rewrite
  * Delete Integration
  * Delete Product Template
  * Duplicate Product
  * Edit Search Term
  * Update Bundle Product
  * Update CMS Block
  * Update CMS Page URL Rewrite
  * Update Custom Variable
  * Update Custom URL Rewrite
  * Update Customer on Frontend
  * Update Integration
  * Update Product Template
  * Update Virtual Product

2.0.0.0-dev87
=============
* Service layer updates:
  * Created Tax Calculation service
  * Implemented search Tax Rates(search criteria) in TaxRate service
  * Refactored Tax Helper to use Tax Service
  * Validated and ensured that after helper fix, all modules with cross-dependencies use Tax Services
  * Refactored Bundle, Catalog, Checkout, Customer, Downloadable, Review, Logging Modules to use Tax Services
  * Refactored Internal Tax Module Blocks/Templates to use Tax Services
* GitHub requests:
  * [#579] (https://github.com/magento/magento2/pull/579) -- update GA code from ga.js to analytics.js
  * [#584] (https://github.com/magento/magento2/issues/584) -- Merge and minify js - Exception
  * [#585] (https://github.com/magento/magento2/pull/585) -- Add forgotten return statement
  * [#592] (https://github.com/magento/magento2/issues/592) -- Module name pattern
  * [#618] (https://github.com/magento/magento2/issues/618) -- Fix of unit tests failure on Travis CI
* Tax calculation updates:
  * Separate and display Weee line item totals from Tax
* Fixed bugs:
  * Fixed an issue when Custom attribute template was not applied to a product  during product creation
  * Fixed an issue when report grid with no results contained unnecessary empty "total" section
  * Fixed an issue where MCRYPT_RIJNDAEL_128 Cipher was set instead of 256 version
  * Fixed an issue when inline translate script was always included in the page even if it was not used
  * Fixed an issue where URL Generation was affected by previously processed URLs
  * Fixed an issue with cross-site scripting vulnerability via cookie exploitation
  * Fixed an issue with incorrect success message after system variable was deleted
  * Fixed an issue with category page not opening if it had bundle product with fixed price assigned to it
  * Fixed an issue when subtotal price in a shopping cart was not updated if the product qty is changed
  * Fixed an issue when syntax error appeared while creating new Google Content attribute mapping
  * Fixed an issue with JS error when adding associated simple product to the grouped one
  * Fixed an issue with incorrect items label for the cases when there are more than one item in the category
  * Fixed an issue when configurable product was out of stock in Google Shopping while being in stock in the Magento backend
  * Fixed an issue when swipe gesture in menu widget was not supported on mobile
  * Fixed an issue when it was impossible to enter alpha-numeric zip code on the stage of  estimating shipping and tax rates
  * Fixed an issue when custom price was not applied when editing an order
  * Fixed an issue when items were not returned to stock after unsuccessful order was placed
  * Fixed an issue when error message appeared "Cannot save the credit memo” while creating credit memo
  * Fixed an issue when Catalog price rule was not shown for the product if price was less than a discount
* Indexer implementation:
  * Implemented a new Stock indexer
  * Implemented a new EAV indexer
* Minor updates for integration test framework
* Split action controllers classes into action classes
* Added public MTF repository to the packagist.org
* Added the following functional tests:
  * Create Admin User
  * Create Category
  * Create Custom Url Rewrite
  * Create Frontend Product Review
  * Delete CMS Page
  * Delete Product
  * Delete System Variable
  * Update Admin User Role
  * Update Product Review
* Indexer-less implementation of URL Rewrites functionality in new UrlRedirect module:
  * Ported Admin UI from old UrlRewrite module
  * Implemented URL Rewrites unified storage
* Covered the following Magento application components with unit tests:
  * `Magento/Bundle/Block/Sales/Order/Items/Renderer.php`
  * `Magento/Bundle/Helper/Catalog/Product/Configuration.php`
  * `Magento/Bundle/Helper/Data.php`
  * `Magento/Bundle/Model/Option.php`
  * `Magento/Bundle/Model/Plugin/PriceBackend.php`
  * `Magento/Bundle/Model/Product/Attribute/Source/Price/View.php`
  * `Magento/Bundle/Model/Sales/Order/Pdf/Items/AbstractItems.php`
  * `Magento/Catalog/Model/Product/Attribute/Source/Msrp/Type/Enabled.php`
  * `Magento/Catalog/Model/Product/Attribute/Source/Msrp/Type/Price.php`
  * `Magento/Catalog/Model/Product/Visibility.php`
  * `Magento/Eav/Model/Entity/Attribute/AbstractAttribute.php`
  * `Magento/Eav/Model/Entity/Attribute/Source/AbstractSource.php`
  * `Magento/Eav/Model/Entity/Attribute/Source/Boolean.php`
  * `Magento/Eav/Model/Entity/Attribute/Source/Table.php`
  * `Magento/Tax/Model/TaxClass/Source/Product.php`
* Covered Magento library with unit tests :
  * `lib/internal/Magento/Framework/Simplexml/Config/Cache/AbstractCache.php`
  * `lib/internal/Magento/Framework/Simplexml/Config.php`
  * `lib/internal/Magento/Framework/Stdlib/DateTime/DateTime.php`
  * `lib/internal/Magento/Framework/Stdlib/DateTime/Timezone.php`
  * `lib/internal/Magento/Framework/Stdlib/String.php`

2.0.0.0-dev86
=============
* Service layer updates:
  * Created Category service and methods
  * Renamed attribute option service
  * Implemented an API method to remove for attribute options
  * Created TaxClass service and methods
  * Created APIs for Tax service
* Framework improvements:
  * REST/SOAP calls uses default store if store code not provided
  * Added a warning about using a not secure protocol for theidentity link URL
  * Fixed exception masking and removed unnecessary exceptions from the Webapi framework
* WEEE features parity:
  * Fixed an issue with Tax calculations when FPT is enabled
  * Fixed an issue where FPT was not included in the subtotal number on invoice pages
  * Fixed an issue where FPT was not included in the subtotal number on credit memo pages
  * Free shipping calculated with FPT
  * Fixed an issue where discounts where applied to FPT
  * Fixed an issue with rounding is the Tax detailed info
  * Fixed issues with bundle product pricing with tier and special prices
* Added an integrity test to verify that dictionary and code are synced
* i18n Improvements:
  * Improved the wording of the i18n CLI Tools
  * Removed the helpers which became unused after i18n Improvements
* Fixed bugs:
  * Fixed an issue where configurable attributes were not chosen according to the hash tag
  * Fixed an issue where the Compare Products functionality did not work correctly
  * Fixed an issue where product attribute values were duplicated after import
  * Fixed an issue were the scope of an attribute was not considered in catalog price rule conditions
  * Fixed an issue where shipping address was not saved if it was added during checkout
  * Fixed an issue where there was no POST request when saving a customer group
  * Fixed an issue where an attribute template was not applied after changing it for the first time during product creation
  * Fixed an issue where the Sale Report Grid with no results found contained an unnecessary empty Total section
  * Fixed an issue where a notice was added to system.log when a product was added to cart
  * Fixed integration test coverage failure
  * Fixed an issue where a message about inequality of password and confirmation was displayed in the wrong place
  * Fixed an issue with an XSS warning in 'Used for Sorting in Product Listing' property of Product Attribute
  * Fixed an issue where an order was not displayed  on frontend if its order status was deleted
  * Fixed an issue where  tier pricing was not displayed on a grouped product page
  * Verified and fixed the content of errors returned from SOAP calls
  * Fixed an issue where it was impossible to create a tax rule when using a complex Customer/Product tax class
  * Fixed an issue where the Street Address line count setting was not applied.
  * Fixed an issue where customers were not assigned to the correct VAT customer groups during admin order creation
  * The unused translateArray method of AbstractHelper was removed
  * Fixed an issue where localization did not work for strings containing a single quote (')
  * Fixed issues with  the translate and the logging transformation tools
  * Fixed an issue where it was impossible to create a URL rewrite for a CMS Page with Temporary (302) or Permanent (301) redirect
* GitHub requests:
  * [#598] (https://github.com/magento/magento2/pull/598) -- Add Sort Order to Rules
  * [#580] (https://github.com/magento/magento2/pull/580) -- Set changed status on model to prevent status overwriting when model gets saved
* Unit Tests Coverage:
  * Part of the Catalog module covered with the unit tests
* Added the following functional tests:
  * Applying Several Catalog Price Rules
  * Attribute Set Creation
  * Category Deletion
  * Customer Group Deletion
  * Generating Sitemap
  * Product Attribute Deletion
  * Update Admin User
  * Update Cms Page
  * Update Customer Group
  * Update Downloadable Product
  * Update Product Attribute
  * Update Sales Rule
  * Update Sitemap

2.0.0.0-dev85
=============
* Service layer updates:
  * Implemented API for the CatalogInventory module
  * Refactored the external usages of the CatalogInventory module to service
* Fixed bugs:
  * Fixed an issue where a coupon usage option was not comprehensible enough
  * Fixed an issue where products selection for adding to a bundle option was lost when switching between pages with product grids
  * Fixed an issue where  Google Content was not sending the correct 'description' attribute
  * Fixed an issue where custom attributes were not displayed in layered navigation after a product import
  * Fixed an issue where the Category URL keys did not work correctly after saving
  * Fixed a jQuery error on a product page in the Admin panel, which appeared when switching between product tabs
* Framework Improvements:
  * Created ProductsCustomOptions Service API for Catalog module
  * Created DownloadableLink Service API for Catalog module
* GitHub requests:
  * [#257] (https://github.com/magento/magento2/issues/257) -- JSON loading should follow OWASP recommendation

2.0.0.0-dev84
=============
* Fixed bugs:
  * Fixed an issue where an invalidly filled option did not become in focus after saving attempt on the Create New Order page in the backend
  * Fixed an issue with the default configuration not being applied properly in the CAPTCHA configuration section
  * Fixed an issue with optional State/Province fields on the Create New Order page being marked as required
  * Fixed an issue with incorrect Customer model usage on session in community modules
  * Fixed an issue where cache was not invalidated after applying catalog price rule
  * Fixed an issue where an admin with custom permissions could not create Shopping Cart Price Rule/Catalog Price Rule
  * Fixed an issue with REST request and response format being inconsistent
  * Fixed an issue where there was an error on a bundle product page if bundle items contained an out of stock product
  * Fixed a JS issue which appeared when adding associated products for a grouped product
  * Fixed an issue where layered navigation was absent on the Advanced Search results page
  * Fixed an issue where the leading "0" in numbers were truncated when exporting using Excel XML
  * Fixed the price type attribute filter in Layered Navigation
  * Fixed an issue with a fatal error in \Magento\Framework\ArchiveTest when bz2 extension was not installed
  * Fixed an issue where an admin could search product by attributes set on the Store View level (except default store view)
  * Fixed an issue where extra spaces in search values were not ignored during search and thus wrong search results were given
* GitHub requests:
  * [#542] (https://github.com/magento/magento2/pull/542) -- Fix ImportExport bug which occurs while importing multiple rows per entity
  * [#544] (https://github.com/magento/magento2/issues/544) -- Performance tests not working
* Framework improvements:
  * Covered the following Magento application components with unit tests:
      * `Customer/Model/Address.php`
      * `Customer/Model/Address/AbstractAddress.php `
      * `Customer/Model/Address/Converter.php`
      * `Customer/Model/Customer.php`
      * `Customer/Model/Customer/Attribute/Backend/Billing.php`
      * `Customer/Model/Customer/Attribute/Backend/Shipping.php`
      * `Customer/Model/Customer/Attribute/Backend/Store.php `
      * `Customer/Model/Customer/Attribute/Backend/Website.php `
      * `Customer/Model/Customer/Attribute/Backend/PasswordTest.php`
      * `Customer/Helper/Address.php`
      * `Customer/Helper/View.php`
      * `Customer/Service/V1/CustomerAccountService.php`
  * Covered Magento lib with unit tests:
      * `lib/internal/Magento/Framework/Filter/*`
      * `lib/internal/Magento/Framework/Model/Resource/Db/AbstractDb.php`
      * `lib/internal/Magento/Framework/Model/Resource/Db/Collection/AbstractCollection.php`
      * `lib/internal/Magento/Framework/File/Uploader.php`
      * `lib/internal/Magento/Framework/File/Csv.php`
      * `lib/internal/Magento/Framework/Less/File/Collector/Aggregated.php`
      * `lib/internal/Magento/Framework/Less/File/Collector/Library.php`
      * `lib/internal/Magento/Framework/Locale/Config.php`
      * `lib/internal/Magento/Framework/Locale/Currency.php`
      * `lib/internal/Magento/Framework/App/Config/Element.php`
      * `lib/internal/Magento/Framework/App/Config/Value.php`
      * `lib/internal/Magento/Framework/App/DefaultPath/DefaultPath.php`
      * `lib/internal/Magento/Framework/App/EntryPoint/EntryPoint.php`
      * `lib/internal/Magento/Framework/App/Helper/AbstractHelper.php`
      * `lib/internal/Magento/Framework/App/Resource/ConnectionFactory.php`
      * `lib/internal/Magento/Framework/App/Route/Config.php`
  * Implemented the ability for a mobile client to get a partial response
  * Added authentication support for mobile
  * Refactored the Oauth lib exception not to reference module classes
  * Moved the authorization services according to the new directory format: was \Magento\Authz\Service\AuthorizationV1Interface, became \Magento\Authz\Service\V1\AuthorizationInterface
  * Moved the integration services according to the new directory format:
    * Was \Magento\Integration\Service\IntegrationV1, became \Magento\Integration\Service\V1\Integration
    * Was \Magento\Integration\Service\OauthV1, became \Magento\Integration\Service\V1\Oauth
  * Improved security of the integration registration
  * Introduced language packages with ability to inherit dictionaries
* Improved modularity of ImportExport
* Created Service API for Magento_Catalog module:
   * Implemented Product Attribute Media API
   * Implemented Product Group Price API
   * Implemented Product Attribute Write API
   * Implemented Product Attribute Options Read and Write API
* Created Service for the Magento Tax module:
  * Implemented Tax Rule Service
  * Implemented Tax Rate Service
  * Implemented Tax Calculation Data Objects
  * Implemented Tax Calculation Builders
  * Implemented Tax Calculation Service
* Covered the part of the Catalog Module with unit tests
* Added PayPall Bill Me Later button
* Streamlined checkout experience
* Improved order review page for PayPal Express Checkout

2.0.0.0-dev83
=============
* Created the Service API for the Magento_Catalog Module:
   * Product Attribute Media API
   * Product Group Price API
* Tax calculation updates:
  * Fixed tax calculation rounding issues which appeared when a discount was applied
  * Fixed extra penny issue which appeared when exact tax amount ended with 0.5 cent
  * Fixed tax calculation issues which appeared when a customer tax rate was different from the store tax rate
  * Fixed price inconsistencies between catalog and shopping cart
  * Added support for maintaining consistent prices including tax for customers with different tax rates
  * Added support for applying tax rules with different priorities to be applied to subtotal only
  * Added support for tax rounding at individual tax rate
* Porting Tax Features from Magento 1.x:
  * Price consistency UX and algorithm
  * Canadian provincial sales taxes
  * Fixed issues with bundle product price inconsistency across the system
  * Added warnings if invalid tax configuration is created in the Admin panel
  * Fixed issues with regards to hidden tax
* Fixed bugs:
  * Fixed an issue where grouped price was not applied for grouped products
  * Fixed an issue where a fatal error occurred when opening a grouped product page without assigned products on the frontend
  * Fixed an issue where it was possible to apply an inactive discount coupon
  * Fixed an issue where the linked products information was lost when exporting products
  * Fixed non-informative error messages for "Attribute Group Service"
  * Fixed the invalid default value of the "apply_after_discount" tax setting
  * Fixed an issue where the integration tests coverage whitelist was broken
  * Fixed Admin panel UI issues: grids, headers and footers
* Added the following functional tests:
  * Create Product Url Rewrite
  * Delete Catalog Price Rule
  * Delete Category Url Rewrite
  * Delete CMS Page Rewrite
  * Delete Product Rating
  * Delete Sales Rule
  * Delete Tax Rate
  * Update Catalog Price Rule
  * Update Shopping Cart

2.0.0.0-dev82
=============
* Added support for MTF Reporting Tool
* Framework improvements:
  * Covered the following Magento application components with unit tests:
      * `ConfigurableProduct/Helper/Data.php`
      * `ConfigurableProduct/Model/Export/RowCustomizer.php`
      * `ConfigurableProduct/Model/Product/Type/Configurable.php`
      * `ConfigurableProduct/Model/Product/Type/Plugin.php`
      * `ConfigurableProduct/Model/Quote/Item/QuantityValidator/Initializer/Option/Plugin/ConfigurableProduct.php`
      * `CatalogSearch/Helper/Data.php`
  * Covered Magento lib with unit tests:
      * `lib/internal/Magento/Framework/DB/Helper/AbstractHelper.php`
      * `lib/internal/Magento/Framework/DB/Tree/Node.php`
* Created Service API for Magento_Catalog Module:
  * Implemented the Product API
  * Implemented the ProductAttributeRead API
* Fixed bugs:
  * Fixed issues with form elements visibility on the backend
  * Fixed an issue where backend forms contained an excessive container
  * Fixed an issue where a wrong category structure was displayed on the Category page
  * Fixed an issue where the pub/index.php entry point was broken because of the obsolete constants
  * Fixed an issue where it was impossible to pass an empty array as an argument in DI configuration and layout updates
  * Fixed an issue with status and visibility settings of a related product on the backend
  * Fixed an issue with unused DB indexes, which used resources, but did not contribute to higher performance
  * Fixed an issue where it was possible to create a downloadable product without specifying a link or a file
  * Fixed an issue where a fatal error occured when opening a fixed bundle product with custom options page on the frontend
  * Fixed an issue where the was a wrong config key for backend cataloginventory
* Processed GitHub requests:
  * [#548] (https://github.com/magento/magento2/issues/548) -- Console installer doesn't checks filesystem permissions
  * [#552] (https://github.com/magento/magento2/issues/552) -- backend notifications sitebuild bug
  * [#562] (https://github.com/magento/magento2/pull/562) -- Bugfix Magento\Framework\DB\Adapter\Pdo\Mysql::getCreateTable()
  * [#565] (https://github.com/magento/magento2/pull/565) -- Magento\CatalogSearch\Model\Query::getResultCollection() not working
  * [#557] (https://github.com/magento/magento2/issues/557) -- translation anomalies backend login page
* Added the following functional tests:
  * Advanced Search
  * Existing Customer Creation
  * Product Attribute Creation
  * Product Rating Creation
  * Sales Rule Creation
  * System Product Attribute Deletion
  * Tax Rate Creation
  * Tax Rule Deletion
  * Update Category
  * Update Category Url Rewrite
  * Update Product Url Rewrite

2.0.0.0-dev81
=============
* Framework improvements:
  * Covered the following Magento application components with unit tests:
      * `SalesRule/Model/Observer`
      * `SalesRule/Helper/*`
      * `SalesRule/Model/Plugin/*`
      * `SalesRule/Model/System/Config*`
      * `Sales/Model/Config.php`
      * `Sales/Model/Download.php`
      * `Sales/Model/Quote.php`
  * Covered the following Magento lib form elements with unit tests:
      * `lib/Magento/Framework/Flag.php`
      * `lib/Magento/Framework/Escaper`
      * `lib/Magento/Framework/Event`
      * `lib/Magento/Framework/Logger`
      * `lib/Magento/Framework/Util`
      * `lib/Magento/Framework/Registry.php`
      * `lib/Magento/Framework/Backup/Media`
      * `lib/Magento/Framework/Backup/NoMedia`
      * `lib/Magento/Framework/Archive`
      * `lib/Magento/Framework/Translate.php`
  * Created Service API for Magento_Catalog module:
      * AttributeSet service
      * AttributeSetGroup service
      * ProductLinks service
      * ProductType service
* Payments Improvements:
  * Resolved a performance issue with Merchant Country selector under Payment Methods settings
  * Removed the PayPal Payments Pro Payflow Edition payment solution
  * Removed the Saved Credit Card payment method
* Added the following functional tests:
  * Delete Admin User
  * Delete Backend Customer
  * Delete Product UrlRewrite
  * Downloadable Product Creation
  * Update Simple Product
  * Update Tax Rule
  * Update Tax Rate
  * Suggest Searching Result
* Fixed bugs:
  * Fixed an issue where the Create Order page title was not correct when scrolling down was performed
  * Fixed the concurrent test running in MTF
  * Fixed an issue where product custom options were merged incorrectly
  * Fixed an issue where customer group discount was not applied for bundle products
  * Fixed an issue where it was impossible to  create a refund for the PayPal Exprecch Checkout Payflow Edition if captured from the PayPal admin
  * Fixed an issue where adding customer review caused an error in system.log
  * Fixed an issue where  the Manage Stock option was automatically reset to No after changing the Stock Availability option
  * Fixed an issue where the recurring profile attributes where displayed for a product when they were not included in the product attribute set.
  * Fixed an issue where a fatal error appeared in some cases on attempt to add a product to  cart when FPT was enabled
  * Fixed an issue where back in stock product alert emails showed HTML markup
  * Fixed an issue where the Refresh Statistics link on the Sales Report page redirected to the frontend after setting  Add Store Code to Urls to Yes
  * Fixed an issue where the selected bundle options price was included to the price displayed in the MAP popup
  * Fixed an issue where the wrong allowed countries list was used in Checkout
  * Fixed an issue where configurable products with out of stock associated simple products were displayed in layered navigation
  * Fixed an issue where configurable products lost options  after being duplicated using the Save and Duplicate button
  * Fixed issues with simple product custom options where it was impossible to import them from a product page and they were not duplicated correctly using the Save and Duplicate button
  * Fixed an issue where it was impossible to create a customer on the backend in a single store mode
  * Fixed an issue where reviews created on the backend appeared with the Guest status
  * Fixed an issue where it was impossible to add an image for a configurable product variation during editing
* Processed GitHub requests:
  * [#539] (https://github.com/magento/magento2/issues/539) The "{config.xml,*/config.xml}" pattern cannot be processed
  * [#564] (https://github.com/magento/magento2/issues/564) Catalog product images - Do not removing from file system
  * [#256] (https://github.com/magento/magento2/issues/256) Unused file app\code\core\Mage\Backend\view\adminhtml\store\switcher\enhanced.phtml
  * [#561] (https://github.com/magento/magento2/pull/561) Bugfix Magento\Framework\DB\Adapter\Pdo\Mysql::getForeignKeys()
  * [#576] (https://github.com/magento/magento2/pull/576) Change Request for InvokerDefault::_callObserverMethod()

2.0.0.0-dev80
=============
* Framework improvements:
  * Reworked subsystem of static view files preprocessing
     * Refactored implementation of the view files "fallback" and "collecting" (layout XML files, LESS files for @magento_import) mechanisms for better abstraction
     * Used the concept of "view asset" in client code of the View library across the board
     * Refactored and simplified LESS preprocessing library, mechanisms of merging and minifying static view files
     * Reworked the way how links to static view files are generated and served):
         * Changed the strategy of generating unique URL for view static files
         * Separated the view files publication process from the page generation
         * Added a separate entry point (pub/static.php) for file materialization
     * View files deployment tool changes:
         * Renamed CLI script from generator.php to deploy.php
         * Fixed the known limitation of view files deployment tool of being unable to materialize files per languages. Now the list of intended languages can be provided as a CLI parameter
         * Expanded the tool parameters
     * Improved security and reliability of view files structure:
         * Restructured the module view folder by file type: "web" – view static files, "templates" – module template files, and "layout" – module layout files
         * Reworked the theme module folder to repeat the same structure as in the module
         * Added “web” folder to a theme root which contains static view files
         * Renamed the pub/lib to lib/web. Currently there are no static files that are publicly accessible by default. All static view files may be subject of preprocessing
         * Renamed the former lib to lib/internal
  * Support of RequireJS:
     * Adopted RequireJS library and implemented ability for modules or themes to introduce RequireJS configuration (aka shim-config)
     * Refactored scripts in the Magento_ConfigurableProduct module to be loaded via RequireJS
* Tax calculation updates:
  * Fixed tax calculation rounding issues when discount is applied
  * Fixed extra penny problem when exact tax amount ends with 0.5 cent
  * Fixed tax calculation errors when customer tax rate is different from store tax rate
  * Added support to round tax at individual tax rate
  * Fixed price inconsistencies between catalog and shopping cart
  * Added support to maintain consistent price including tax for customers with different tax rates
  * Added support to allow tax rules with different priorities to be applied to subtotal only
* Fixed bugs:
  * Fixed an issue where it was impossible to place an order with Zero Subtotal Checkout using checkout with multiple addresses
  * Fixed an issue where an irrelevant  confirmation window appeared when placing an order with Zero Subtotal Checkout in the backend
  * Fixed an issue where it was impossible to create an order for a new customer in the backend if gift options were  enabled
  * Fixed an issue where a wrong message about backordered items in cart was displayed in the backend
  * Fixed an issue where it was impossible to perform a checkout with multiple addresses when the  Validate Each Address Separately option in Multi-address Checkout was enabled
  * Fixed an issue where the Minimum Order Amount option was applied to the orders
  * Fixed an issue where the duplicated element  caused  problems when attempting to customize styling  of the section
  * Fixed an issue where a user was redirected to Dashboard when clicking the Search  and Reset buttons on the Recurring Profile page
  * Fixed an issue where the Enabled for RMA option was available for online shipping method in Magento 2 CE
  * Fixed an issue when free shipping was applied even if the Free Shipping with Minimum Order Amount option was disabled
  * Fixed an issue with not displaying a downloadable product with the Links can be purchased separately option enabled on the grouped product page
  * Fixed an issue of not generating product price variations during configurable product creation
  * Fixed an issue with incorrect work of category pager
  * Fixed an issue with file permissions change after the system backup was run
  * Fixed an issue with inconsistency between the REST request and response format
  * Fixed an issue with the Magento Contact Us form not submitted if secure_base_url doesn't contain "https"
  * Fixed an issue with incorrect display of the Price as configured field which didn’t count product options cost
  * Fixed an issue with incorrect redirect when clicking the product URL in Pending Review Rss
* JavaScript improvements:
  * Added standard validation to the front-end address fields
  * Implemented the wishlist widget
  * Implemented the tabs widget
  * Implemented the collapsible widget
  * Implemented the accordion widget
  * Implemented the tooltip widget
  * Standardized widgets used on one page checkout

2.0.0.0-dev79
=============
* Tax calculation updates:
  * Fixed issues in tax calculation rounding with discount applied
  * Fixed an issue with extra penny  when exact tax amount ended with 0.5 cent
  * Fixed an issue where there were tax calculation errors when customer tax rate was different from store tax rate
  * Added support to round tax at individual tax rate
  * Fixed price inconsistencies between catalog and shopping cart
  * Added support for maintaining consistent price including tax for customers with different tax rates
  * Added support for applying tax rules with different priorities to subtotal only

* Fixed bugs:
  * Removed the extra '%' sign in the error\notice message on Gift Card Accounts page on the backend
  * Fixed an issue with image uploading functionality in the Catalog configuration
  * Fixed an issue where a customer could not navigate the store when downloading the downloadable product
  * Fixed an issue where adding CMS block Catalog Events Lister caused an error
  * Fixed an issue where the price was displayed twice on the Product page on the frontend
  * Fixed an issue where an admin could not open search results on the backend
  * Fixed an issue where the Rule Based Product Relations functionality was generating incorrect SQL when product category attribute was set through "is one of" or "contains" operator by constant value
  * Fixed an issue where it was impossible to add a product to the Compare list for  categories with three-column page layout
  * Fixed an issue where a blank page opened when changing store view on a product page on the frontend
  * Fixed an issue where the  "Please specify at least one search term." error message was not displayed if search is performed without search data specified on the frontend
  * Fixed a Google Chrome specific issue where page layout was broken when updating status for reviews on the backend
  * Fixed admin look and feel issues
  * Fixed an issue where the order notices and error messages were not red
  * Fixed a UI issue which appeared during custom attribute creation
  * Fixed an issue where the popup did not open after clicking What's this? next to the Remember Me check box  when persistent shopping cart was enabled
  * Fixed an issue where the options of the Add Product split dropdown did not fit the page
  * Fixed an issue where the default theme preview image sample link was missing
  * Fixed a Safari and Internet Explorer 9 specific issue where the backend menu is not displayed for users with custom admin roles
  * Fixed an issue where  the price of bundle products was not  displayed correctly on the product page on the frontend
  * Fixed a UI issue in the debug mode configuration
  * Fixed minor issues with page layout
  * Fixed an issue where the mini shopping cart loaded data from cache
  * Fixed an issue where there was an incorrect value in the Grand Total (Base) column in the Orders grid if Catalog Price Scope was set to Website
  * Fixed an issue where the Entity Generator tool did not accept the "class" parameter
  * Fixed an issue where the default email template was not applied when the custom template in use was deleted
  * Fixed an issue where shipping price for flat rate was set to 0 in the side block during checkout of a product with a configured recurring profile
  * Fixed an issue where it was possible to create more Shipping Labels than there were products in the shipment
  * Fixed an issue where data about "SHA-IN Pass Phrase" was missing after changing "Payment Action" in the Ogone payment method configuration
  * Fixed performance issues with reindexing of the Price indexer
  * Fixed an issue where importing tax rates with postal code = * led to incorrect data entered into database
  * Fixed an issue where incorrect link to reset password was sent if secure URL was used on the frontend
  * Fixed an issue where the Links section was absent while editing downloadable products from the Wishlist
  * Fixed an issue where specified details for composite products were lost after adding to Gift Card and Downloadable products to the Wishlist
  * Fixed and issue where the Date widget was set to incorrect date when creating a new customer
  * Fixed an issue where a customer was redirected to Dashboard if the Redirect user to dashboard after login option was set to "No"
  * Fixed an issue where a customer was not able to register during checkout if Guest Checkout was not allowed
  * Fixed an issue where System logs were not generated properly in integration tests
  * Fixed benchmarking script
  * Fixed an issue where it was impossible to put store to the maintenance mode during backup
  * Fixed insecure use of mt_rand()
  * Fixed an issue where Quoted price was displayed incorrectly from the shopping cart in the backend
* Functional tests:
  * Tax Rule Creation
  * Admin User Role Creation
  * Simple Product Creation
  * Customer Group Creation
  * Update Backend Customer
  * Newsletter Creation
  * Virtual Product Creation
  * Catalog Price Rule Creation
  * Category Url Rewrite Creation
  * Admin User Role Deletion
* Update composer.json.dist in order to download and install MTF from Public GitHub
* GitHub requests:
  * [#542] (https://github.com/magento/magento2/pull/542) Fix ImportExport bug which occurs while importing multiple rows per entity
  * [#507] (https://github.com/magento/magento2/issues/507) "Insert Image" window is overlapped on menu

2.0.0.0-dev78
=============
* Fixed bugs:
  * Fixed an issue where a blank page was displayed when changing store view on a product page
  * Fixed an issue where it was impossible to change attribute template during product creation
  * Fixed an issue where the Categories field and the New Category button was displayed during product creation for users with no permissions to access Products and Categories
  * Fixed an issue where no records were found in the User Roles grid if no users were assigned to a role
  * Fixed an issue where variable values in the Newsletter templates were not displayed
  * Fixed an issue where 'No files found' was displayed in the JS Editor on the Design page
  * Fixed an issue where the State/Province list on frontend was displayed with HTML tags if inline translate was enabled
  * Fixed an issue where CAPTCHA was not displayed on the Contact Us page
  * Fixed an issue where scheduled backups were not displayed and neither performed
  * Fixed functional tests failing PSR-2 test

2.0.0.0-dev77
=============
* Themes update:
  * Blank theme was refactored to implement the mobile-first approach
* Added Readme.md file
* Fixed bugs:
  * Fixed an issue where it was impossible to place order using store credit
  * Fixed an issue where adding products with custom options from a wishlist to shopping cart caused an error
  * Fixed an issue where it was impossible to add a product to the shopping cart from the Wishlist sidebar
  * Fixed an issue where the Add to Wishlist drop-down arrow was missed on the category page on the frontend
  * Fixed an issue where it was impossible to manage multiple wishlists on the frontend if FPC was disabled
  * Fixed an issue where prices with taxes were not displayed on the category and product pages on the frontend
  * Fixed an issue where it was impossible to store cache when using either Varnish or built-in cache
  * Fixed an issue where all refactored indexers were in the REINDEX REQUIRED status after installation
  * Fixed an issue where admins with limited access could perform operations not allowed by role permissions
  * Fixed an issue where http links were generated instead of https links
  * Fixed an issue where it was impossible to use Subcategories when building a condition for a catalog price rule
  * Fixed an issue where a registered customer could not place an order using PayPal Payments Advanced
  * Fixed an issue where PayPal Settlement report was empty
  * Fixed an issue where a newly created subcategory was still active after switching to the Default category
  * Fixed an issue where it was impossible to save changes or remove a customer address on the backend
  * Fixed an issue where for an admin with restricted permissions previewing a newsletter template caused a fatal error
  * Fixed an issue where it was impossible to save a Tax Rate if specified that Zip was a range, and the Zip/Post Code field was left empty
  * Fixed an issue where Puerto Rico was listed both as a state and as a country
  * Fixed an issue where the Special Price was displayed instead of the place of Original Price in the Items Ordered column if the orders list.
  * Fixed an issue in Widget configuration where category check boxes did not stay selected when Anchor Categories were specified in the Display On drop-down list.
  * Fixed an issue where admin user password confirmation was not validated on the server side
  * Fixed an issue where adding a customer review caused an error
  * Fixed an issue where the incorrect error messages were displayed if an invalid email was entered during admin user or customer creation
  * Fixed an issue with the Debug section in developer settings, which should only be displayed for the website or store view scope level
  * Fixed an issue where the fatal error was displayed after uninstall if during installation it was specified to save session in the database
  * Fixed an issue where a wrong error message was displayed when a non-existing database was specified when installing Magento using the console install script
  * Fixed an issue where it was impossible to add products from a wishlist to a shopping cart
  * Fixed an issue where an error appeared after Magento installation
  * Improved the Blank theme UI
  * Fixed an issue with a zooming product image overlapped by category design on the frontend
  * Fixed an issue where it was impossible to select only billing or only shipping address when editing the user address on the frontend
  * Fixed an issue where it was impossible to view a Wishlist in the Wishlist Search widget
  * Fixed an issue where partial cache invalidation did not work for built-in caching
  * Fixed an issue where it was impossible to find a catalog event using the Countdown Ticker grid filter if the event had been specified to be displayed on both category and product pages
  * Fixed incorrect error messages displayed during customer registration
  * Fixed an issue where product attributes from other store views were displayed for products in a Wishlist
  * Fixed an issue where it was impossible to place an order without the CheckoutAgreements module
  * Fixed an issue where the Media Image attribute type was not available when creating the product attribute
  * Fixed an issue with incorrect label attribute for the State/Province drop-down list on the Shipping Information tab
  * Fixed an issue where using only digits in the SKU field of configurable products led to a confusing behavior
  * Fixed an issue where a catalog price rule was not shown on the catalog and product pages on the frontend
  * Fixed an issue where Recurring Profiles (payments) were available on the frontend for any registered user who had the URL
  * Fixed an issue where a credit card frame was absent on the Payment Information step of Onepage Checkout, if there was only one payment method with a credit card available
  * Fixed an issued where it was impossible to use inline translate for the My Account and Register links on the frontend
  * Fixed an issue where it was impossible to activate a customer using REST
  * Fixed an issue with the undefined version_compate method called in \lib\Magento\Connect\Validator.php
  * Fixed an issue with invalid XML formatting of Boolean in REST response
  * Fixed an issue where it was impossible to perform installation using index.php from the pub folder (problem with JS and CSS)
  * Fixed an issue where the Multiple Wishlist functionality did not work correctly with enabled Full Page Cache in the Chrome browser
  * Fixed an issue where it was impossible to change an admin frontname using console installation
  * Fixed an exception on the Transaction page when searching by payment method
  * Fixed an issue where the "Add to wishlist" link was displayed in catalog even when the Wishlist functionality was disabled
  * Fixed an issue where the system was broken if an admin user unassigned his own role
  * Fixed an issue with exceptions thrown on attempt to export products for users with store-level restrictions
  * Fixed an issue where two loaders were displayed when saving a category
  * Fixed an issue where it was impossible to search for a newsletter in the Newsletter grid
  * Fixed an issue where the displayed currency and product price were not changed after switching to a new currency
  * Fixed an issue with frontend crashing when deleting a product from a mini shopping cart
  * Fixed an issue where it was impossible to add a bundle product to a shopping cart
  * Fixed an issue where a configurable product base image disappeared when selecting product variations
* Functional tests:
  * Functional end-to-end tests publication
     * Bundle product
     * Category
     * Customer
     * Configurable product
     * Downloadable product
     * Newsletter
     * Review
     * Simple product
     * Sitemap
     * Store
     * Tax Rule
     * User
     * Virtual product
* Service layer updates:
  * Move CurrentCustomerService from Service to Helper
* GitHub requests:
  * [#544] (https://github.com/magento/magento2/issues/544) Performance tests not working
  * [#554] (https://github.com/magento/magento2/pull/554) Performance tests - Fix jmeter output format
  * [#525] (https://github.com/magento/magento2/pull/525) Fix typo in FS Generator help message
  * [#563] (https://github.com/magento/magento2/issues/563) Admin Login not working #563

2.0.0.0-dev76
=============
* Pricing improvements:
  * Eliminated code duplication from templates and implemented new calculation models for the following modules:
     * ConfigurableProduct
     * Wishlist
     * Rss
     * ProductAlert
* JavaScript improvements:
  * Removed head.js usages from frontend
  * Removed head.js usages from adminhtml
* Themes update:
  * Plushe styles are removed, Plushe theme is now based on blank
* Fixed bugs:
  * Unable to place order with product that contains custom option 'file'
  * OnePageCheckout is not working if PayPal method is enabled to work via Payment Bridge
  * Impossible to reset password for admin user (incorrect reset password link in email)
  * Errors when deleting customer group specified as default one in the config
  * A number of essential buttons do not work and block other functionality in Internet Explorer 10
  * "Insert Widget" button is missing in Insert Widget popup while creating CMS page
  * Impossible to change status for rating in admin
  * System email templates are not loaded when user creates new email template
  * Billing Agreements tab displays during New Customer creation in admin panel
  * Images are not displayed in WYSIWYG when editing default pages
  * Error message "Asymmetric transaction rollback" when creating simple product with flat catalog product option enabled in config
  * Fatal error when trying to preview sample(type=link) or view link for download(type="link") for downloadable product
  * Customer is redirected to Home Page after adding new address during multiple address checkout if secure URLs are enabled for frontend in config
  * Impossible to select value in the State/Province field in the customer registration form when customer uses multiple address checkout
  * Manage Stock option is not editable when using mass action on several products in the admin panel
  * Category is not displayed in layered navigation block when Flat Catalog is enabled in config
* GitHub requests:
  * [#489] (https://github.com/magento/magento2/issues/489) -- PHPUnit 4.0 Compatibility
  * [#535] (https://github.com/magento/magento2/issues/535) -- Image management for products
* Framework improvements:
  * Covered Magento lib form elements with unit tests:
      * `lib/Magento/Framework/Data/Form/Element/AbstractElement.php`
      * `lib/Magento/Framework/Data/Form/Element/Button.php`
      * `lib/Magento/Framework/Data/Form/Element/Checkbox.php`
      * `lib/Magento/Framework/Data/Form/Element/CollectionFactory.php`
      * `lib/Magento/Framework/Data/Form/Element/Column.php`
      * `lib/Magento/Framework/Data/Form/Element/File.php`
      * `lib/Magento/Framework/Data/Form/Element/Hidden.php`
      * `lib/Magento/Framework/Data/Form/Element/Editablemultiselect.php`
      * `lib/Magento/Framework/Data/Form/Element/Factory.php`
      * `lib/Magento/Framework/Data/Form/Element/Image.php`
      * `lib/Magento/Framework/Data/Form/Element/Imagefile.php`
      * `lib/Magento/Framework/Data/Form/Element/Label.php`
      * `lib/Magento/Framework/Data/Form/Element/Link.php`
      * `lib/Magento/Framework/Data/Form/Element/Multiselect.php`
      * `lib/Magento/Framework/Data/Form/Element/Note.php`
      * `lib/Magento/Framework/Data/Form/Element/Obscure.php`
      * `lib/Magento/Framework/Data/Form/Element/Password.php`
      * `lib/Magento/Framework/Data/Form/Element/Radio.php`
      * `lib/Magento/Framework/Data/Form/Element/Reset.php`
      * `lib/Magento/Framework/Data/Form/Element/Submit.php`
      * `lib/Magento/Framework/Data/Form/Element/Text.php`
      * `lib/Magento/Framework/Data/Form/Element/Textarea.php`

2.0.0.0-dev75
=============
* Modularity improvements:
  * Introduced a new CheckoutAgreements module. Moved all "Terms and Conditions" related logic from Magento_Checkout to Magento_CheckoutAgreements
  * Moved library related logic from `Magento\Core\Model\App`
* Fixed bugs:
  * Fixed an issue where Currency Options were not displayed on the Currency Setup tab
  * Fixed an issue where a fatal error appeared during customer registration if mail server was off
  * Fixed an issue where customer with middle name did not appear in the Customers grid in the backend
  * Fixed an issue where related products were not displayed on the product page in the backend
  * Fixed the broken View Files Population tool
  * Fixed an issue where Magento broke down if the Main Web Site was deleted
  * Fixed potential security issue with orders protect_code
  * Fixed an issue where an error appeared when placing an order if cache was turned on
  * Fixed an issue where a warning appeared when running system_config.php tool
  * Fixed an issue with incorrect reset password link for users on custom websites
  * Fixed an issue with invalid error message displayed when trying to save a customer group with existing group name
  * Fixed an issue with  menu layout non-responsive behavior  in the Blank theme
* Framework Improvements:
  * Covered Magento library components with unit tests
    * `Magento\Framework\Error\*`
    * `Magento\Framework\Event\Observer\*`
    * `Magento\Framework\Filesystem\*`
    * `Magento\Framework\Filesystem\File\*`
  * Updated the obsolete_classes list with changes, introduced by Offline Payment Methods Module implementation
  * Moved `lib/Magento/*` to `lib/Magento/Framework/*`
  * Covered Magento application components with unit tests:
     * `Store\Model\*`
     * `Sales/Helper/Guest.php`
     * `Sales/Helper/Admin.php`
     * `Sales/Model/Observer.php`
     * `Sales/Model/Payment/Method/Converter.php`
     * `Sales/Model/Email/Template.php`
     * `Sales/Model/Observer/Backend/CustomerQuote.php`
     * `Sales/Model/Status/ListStatus.php`
* Refactored the following modules to use Customer Service:
  * Magento_Persistent
  * Magento_GoogleShopping
  * Magento_ProductAlert
  * Magento_SendFriend
  * Moved customer-specific logic from the Magento_ImportExport module to the Customer module
  * Refactored the rest of Customer Group usages
  * Refactored customerAccountService::createAccount to not expose the hashed password input from webapi
  * Implemented a delimiter usage for Cache key in Customer Registry
* Customer Service usage:
  * Updated exception hierarchy with a new localized exception class
  * Updated CRUD APIs to support email and base URL instead of IDs
* JavaScript improvements:
  * Implemented the validation widget
  * Implemented the tooltip widget
  * Implemented the popup/modal window widget
  * Implemented the calendar widget
  * Implemented the suggest widget
* Added configuration for Travis CI

2.0.0.0-dev74
=============
* Pricing Improvements:
  * Added price calculation component to library
  * Eliminated price calculation from blocks and templates and implemented new calculation models for the following product types:
     * Bundle
     * Simple/Virtual
     * Grouped
     * Downloadable
  * Resolved price calculation dependencies on the Tax and Weee modules
* Themes update:
  * Updated the look&feel of the Admin theme
* Fixed bugs:
  * Fixed an issue with the inability to save product with grouped price when Price Scope = Website
  * Fixed an issue with fatal error on attempt to edit product from wishlist in stores with multiple store views
  * Fixed an issue where it was impossible to add to a wishlist a product with custom quantity
  * Fixed an issue where JS validation was skipped during CMS page creation
  * Fixed an issue with the New Customer Address Attribute page and the New Customer Attribute page having the same title
  * Fixed an issue where a form was submitted two times during CMS page creation
  * Fixed an issue where a fatal error appeared when trying to edit product in a wishlist in stores with multiple store views
  * Fixed an issue with inability to change page layout for categories
  * Fixed an issue where the Quantity drop-down list box was disabled for bundle products
  * Fixed an issue where inactive Related Products rules were applied
  * Fixed a clickjacking vulnerability
  * Fixed bugs and added improvements in the Blank theme
  * Fixed an issue where the Flat Rate shipping method was not enabled by default
  * Fixed an issue with incorrect order of products on the Add Product split button
  * Fixed an issue with saving the tier price attribute value
  * Fixed an issue with creating integration from config file
  * Fixed an issue where the Cookie Restriction Mode = Yes configuration was not applied
  * Fixed an issue where it was impossible to perform ajax actions from backend grids in Internet Explorer
  * Fixed the improper usage of DIRECTORY_SEPARATOR
  * Fixed an issue where it was impossible to add new address on customer's account page if default address had been already set
  * Fixed an issue where setting memory_limit to -1 caused installation failure
  * Fixed an issue where the configuration of Admin Session Lifetime was not applied correctly
  * Fixed an issue where Scheduled Export was not performed if exporting to remote FTP server
  * Fixed the wrong default value for PHP memory_limit
  * Fixed an issue where frontend messages were not displayed when FPC was turned off
  * Fixed the position of page action buttons on the Categories page in the backend
  * Improved backend grids UI
* Framework Improvements:
  * Simplified Search related Data Objects
  * Moved lib/Magento/* to lib/Magento/Framework/*
    * Moved lib/Magento/App to lib/Magento/Framework/App
* Refactored the following modules to use Customer service:
  * PayPalRecurringPayment
  * RecurringPayment
  * Multishipping
  * Paypal
* Customer Service usage:
  * Implemented Service Context Provider
  * Restructured webapi.xml
  * Renamed createAccount to createCustomer in CustomerAccountService
  * Implemented Caching strategy for the Customer service
* GitHub requests:
  * [#488] (https://github.com/magento/magento2/issues/488) -- Converted several grids from Magento\Sales module to new layout XML config format

2.0.0.0-dev73
=============
* Framework Improvements:
  * Eliminated the StoreConfig class, and ability to work with Configuration through the Store object. Scope Config was introduced instead.
  * Fixed performance degradation caused by DI argument processors
  * Covered Magento library components with unit tests:
     * Magento/App/Request
     * Magento/App/Resource directory and Magento/App/Resource.php
     * Magento/App/Response
     * Magento/App/Route
     * Magento/App/Router
     * Magento/App/Http.php
     * Magento/Translate.php
  * Improved the Web API framework based on Customer Service
  * Updated the API Service Exception Handling
  * Changed the conventional notation of Vendor name in theme path: from `app/design/<area>/<vendor>_<theme>` to `app/design/<area>/<vendor>/<theme>`
  * Renamed the 3DSecure library to CardinalCommerce, and removed the unused flex library
* Themes update:
  * Updated the look&feel of the Admin theme
* Modularity improvements:
  * Introduced a new Store module. Moved all Store related logic from Magento_Core to Magento_Store
  * Moved the library part of the Config component from the Magento_Core module to the library
  * Moved the Session related logic from the Magento_Core module to the library
  * Moved the abstract logic related to Magento "Module" from Magento_Core to the library
  * Moved the form key related functionality to the library
  * Introduced a new Magento_UrlRewrite module and moved related classes from Magento_Core to the new module
  * Moved the resource model to Magento_Install module
  * Eliminated the Core\Helper\Js class
  * Moved the Email related logic from Magento_Core module to Magento_Email module
  * Moved the Cache related logic from the Magento_Core module to the library
  * Resolved issues which appeared when an order had been placed before the Magento_Payment module was disabled
  * Eliminated Magento_Catalog dependency on Magento_Rating
  * Removed the Magento_Rating module, its logic moved to Magento_Review
  * Moved the View related components from Magento_Core to the Magento/View library
* Refactored the following modules to use Customer Service
  * Magento_Multishipping
  * Magento_Paypal
  * Magento_Log
  * Magento_RSS
  * Magento_Review
  * Magento_Wishlist
  * Magento_Weee
  * Magento_CatalogInventory
  * Magento_CatalogRule
  * Magento_SalesRule
* GitHub requests:
  * [#520] (https://github.com/magento/magento2/issues/520) -- Fixed spelling in Magento\Payment\Model\Method\AbstractMethod
  * [#481] (https://github.com/magento/magento2/issues/481) -- GD2 Adapter PHP memory_limit
  * [#516] (https://github.com/magento/magento2/issues/516) -- Make Sure That save_before Event Is Dispatched
  * [#465] (https://github.com/magento/magento2/issues/465) -- Absolute path is assembled incorrectly when merging js/css files
  * [#504] (https://github.com/magento/magento2/issues/504) -- Renamed "contacts" module to "contact"
  * [#529] (https://github.com/magento/magento2/issues/529) -- Fixed exception at admin dashboard
  * [#535] (https://github.com/magento/magento2/issues/535) -- Fixed an issue during creating or editing product template
  * [#535] (https://github.com/magento/magento2/issues/535) -- Fixed Typo in the module name
  * [#538] (https://github.com/magento/magento2/issues/538) -- Fixed missing tax amount in the invoice
  * [#518] (https://github.com/magento/magento2/issues/518) -- Change to Magento\Customer\Block\Widget\Dob new version
* Fixed bugs:
  * Fixed implementation issues with Cron task group threading
  * Fixed inability to place order during customer registration flow
  * Fixed an issue where after JS minification errors appeared when loading pages which contained minified JS
  * Fixed an issue where it was impossible for users with restricted permission to export certain entities
  * Fixed an issue where checkout was blocked by the "Please enter the State/Province" pop-up for customers that had saved addresses
  * Fixed an issue where a fatal error appeared when trying to check out the second time with OnePageCheckout
  * Fixed an issue where a fatal error appeared when trying to create an online invoice for an order placed with PayPal Express Checkout (Payment Action = Order)
  * Fixed an issue where the special price for a bundle product was calculated wrongly
  * Fixed an issue where a fatal error appeared when trying to create a shipment for an order if Magento was installed without the USPS module
  * Fixed an issue where the Lifetime Sales and Average Orders sections of the Admin Dashboard were missing
  * Fixed an issue where the active tab changed after changing the attribute set
  * Fixed an issue with incorrect order of product types in the Add Product menu in the backend
  * Fixed an issue with saving the tier price attribute
* JavaScript improvements:
  * Upgraded the frontend jQuery library to version 1.11
  * Upgraded the frontend jQuery UI library to version 1.10.4
  * Modified the loader widget to render content using handlebars
  * Added the 'use strict' mode to the accordion widget
  * Added the 'use strict' mode to the tab widget

2.0.0.0-dev72
=============
* Framework Improvements:
  * Fixed performance degradation caused by DI argument processors
* Modularity improvements:
  * Introduced the Magento_UrlRewrite module, and moved corresponding classes from Magento_Core to Magento_UrlRewrite
  * Moved all Install logic to the Magento_Install module
  * Eliminated the Core\Helper\Js class
  * Moved the Email related logic from the Magento_Core module to the Magento_Email module
  * Moved the Cache related logic from the Magento_Core module to library
* Indexer improvements:
  * Added execution time hints for console reindex
* Customer Service usage:
  * Refactored the Magento_Newsletter module to use Customer service layer
* Fixed bugs:
  * Fixed an issue with resetting customer password from the frontend
  * Fixed an issue where mistakenly the attribute of the Customer Address Edit form was cached
  * Fixed an issue where admin could not unsubscribe customer on the customer edit page in the backend
  * Fixed an issue where customers were always subscribed to the newsletter even if not selected during registration
* GitHub requests:
  * [#325] (https://github.com/magento/magento2/pull/325) -- ImportExport: Fix notice if _attribute_set column is missing

2.0.0.0-dev71
=============
* Fixed bugs:
  * Fixed an issue with displaying product on the frontend when the product flat indexer is enabled
  * Fixed an issue with applying catalog price rules on the category level
  * Fixed an issue where the essential cookies like CUSTOMER, CART, and so on were not created in Google Chrome
  * Fixed an issue with placing orders by customers assigned to a VAT group
  * Fixed an issue with incorrect error message during registration, and inability for a shopper to ask for resending a confirmation email
  * Fixed an issue where the Catalog module resource Setup Upgrade logic was broken
* Modularity improvements:
  * Moved abstract Core models and related logic to the Magento/Model library
  * Moved the abstract DB logic and Core resource helpers to the Magento/DB library
  * Eliminated the Core\Model\App class
  * Moved the Magento Flag functionality to the library
  * Resolved dependency of the Catalog and related modules on the Review module
  * Moved indexers related logic from the Core module to the Indexer module
  * Moved the Inline translation and user intended translate functionality from the Core module to a separate Translation module
* Framework Improvements:
  * Covered Magento library components with unit tests:
     * Magento\Config
     * Magento\Convert
     * Magento\Controller
     * Magento\Data\Collection\Db
     * Magento\Mview
     * Magento\Url and Magento/Url.php
  * Covered Magento application components with unit tests:
     * Magento\Checkout\Model\Config
     * Magento\Checkout\Model\Observer
     * Magento\Checkout\Model\Type
     * Magento\Sales\Model\Config
  * Renamed LauncherInterface to AppInterface
* Improvements in code coverage calculation:
  * Updated the whitelist filter with library code for integration tests code coverage calculation
* GitHub requests:
  * [#512] (https://github.com/magento/magento2/issues/512) -- Theme Thumbnails not showing
  * [#520] (https://github.com/magento/magento2/pull/520) -- Corrected Search Engine Optimization i18n
  * [#519] (https://github.com/magento/magento2/issues/519) -- New Theme Activation
* Customer Service usage:
  * Refactored the Log module to use Customer Service
  * Refactored the RSS module to use Customer Service
  * Refactored the Review module to use Customer Service
  * Refactored the Catalog module to use Customer service layer
  * Refactored the Downloadable module to use Customer service layer

2.0.0.0-dev70
=============
* Fixed bugs:
  * Fixed an issue where the schedule of recurring payments was not displayed in the shopping cart
  * Fixed an issue with displaying tax class names in the Customer Groups grid
  * Fixed an issue with testing Solr connection
  * Fixed an issue with using custom module front name
  * Fixed an issue with USPS and DHL usage in the production mode
* Modularity improvements:
  * Consolidated all logic related to Layered Navigation in one separate module
* Framework Improvements:
  * Covered Magento library components with unit tests:
     * Magento/Interception
     * Magento/ObjectManager
     * Magento/Message
     * Magento/Module
     * Magento/Mail
     * Magento/Object
     * Magento/Math
* Updated XML files to include a reference to the schema file in a form of a relative path
* Updated code to be PSR-2 compliant

2.0.0.0-dev69
=============
* Themes update:
  * LESS styles library added in pub/lib/css/
  * A new Blank theme set as default
* GitHub requests:
  * [#491](https://github.com/magento/magento2/pull/491) -- Fixed bug, incorrect auto-generation Category URL for some groups of symbols (idish, cirrilic, e, a, and other).
  * [#480](https://github.com/magento/magento2/pull/480) -- Fixing a bug for loading config from local.xml
  * [#472](https://github.com/magento/magento2/issues/472) -- Params passed in pub/index.php being overwritten
  * [#461](https://github.com/magento/magento2/pull/461) -- Use translates for Quote\Address\Total\Shipping
  * [#235](https://github.com/magento/magento2/issues/235) -- Translation escaping
  * [#463](https://github.com/magento/magento2/pull/463) -- allow _resolveArguments to do sequential lookups
  * [#499](https://github.com/magento/magento2/issues/499) Deleted unclosed comment in calendar.css
* Fixed bugs:
  * Fixed a fatal error that occurred with a dependency in pub/errors/report.php
  * Fixed an issue where code coverage failed for Magento\SalesRule\Model\Rule\Action\Discount\CartFixedTest
  * Fixed an issue where PayPal Express Checkout redirected to the PayPal site even though the Allow Guest Checkout option was set to 'No'
  * Fixed an issue where invalid password reset link was sent when resetting customer password from the backend
  * Fixed an issue where it was not possible to download a previously created backup
  * Fixed a security issue with possibility of a XSS injection in the Integration re-authorization flow
  * Fixed an issue where Billing Agreement cancellation from the backend did not work
  * Fixed an issue with the debug section in the developer settings
  * Fixed the unreliable implementation of the fetching authorization header via SOAP
  * Fixed issues with WSDL generation error reporting
  * Fixed an issue with incorrect order of the Recurring Profile tab in Account Customer on the frontend
  * Fixed an issue when the information about a custom option of the 'File' type was not displayed correctly on the recurring profile page
  * Fixed an issue with editing Product template
  * Fixed an issue with duplicated shipping method options during checkout
  * Fixed an issue where flat indexers were re-indexed in shell when they were disabled
  * Fixed an issue where adding a wrong/nonexistent SKU using 'Order by SKU' from My Account caused a fatal error
  * Fixed an issue with the JS/CSS merging functionality
  * Fixed an issue with static view files publication tool used for the 'production' mode
* Modularity improvements:
  * Removed the deprecated GoogleCheckout functionality
  * Removed all dependencies on the RecurringPayment module
  * Removed the Sales module dependencies on Customer models/blocks
  * Renamed the RecurringProfile module to RecurringPayment
  * Resolved dependencies between the Email Templates functionality and other modules
  * Moved Core module lib-only depended components to library
  * Moved CSS URL resolving logic from publisher to the separate CSS pre-processor
  * Re-factored the View publisher
* Framework improvements:
  * Added restrictions on the data populated to the Service Data Object
  * Renamed Data Transfer Object to Service Data Object
  * Updated the view files population tool to support LESS
* Customer Service usage:
  * Refactored the Tax module to use Customer service layer
  * Refactored Customer module Adminhtml internal controllers and helper to use Customer services
  * Added and updated the Customer service APIs
  * Exposed Customer services as REST APIs
* Indexer implementation:
  * Implemented a new optimized Product Price Indexer
* Updated various PHPDoc with the parameter and return types

2.0.0.0-dev68
=============
* Cache:
  * Implemented depersonalization of private content generation
  * Implemented content invalidation
  * Added Edge Side Includes (ESI) support
  * Added a built-in caching application
* GitHub requests:
  * [#454](https://github.com/magento/magento2/pull/454) -- Allow to specify list of IPs in a body on maintenance.flag which will be granted access even if the flag is on
  * [#204](https://github.com/magento/magento2/issues/204) -- Mage_ImportExport: Exporting configurable products ignores multiple configurable options
  * [#418](https://github.com/magento/magento2/issues/418) -- Echo vs print
  * [#419](https://github.com/magento/magento2/issues/419) -- Some translation keys are not correct.
  * [#244](https://github.com/magento/magento2/issues/244) -- Retrieve base host URL without path in error processor
  * [#411](https://github.com/magento/magento2/issues/411) -- Missed column 'payment_method' of table 'sales_flat_quote_address'
  * [#284](https://github.com/magento/magento2/pull/284) -- Fix for Issue #278 (Import -> Stores with large amount of Configurable Products)
* Fixed bugs:
  * Fixed an issue where Mage_Eav_Model_Entity_Type::fetchNewIncrementId() did not rollback on exception
  * Fixed an issue where a category containing more than 1000 products could not be saved
  * Fixed inappropriate error messages displayed during installation when required extensions were not installed
  * Fixed synopsis of the install.php script
  * Fixed an issue where the schedule of recurring payments was not displayed in the shopping cart
* Modularity improvements:
  * Introduced the OfflinePayments module - a saparate module for offline payment methods
  * Added the ability to enable/disable the Paypal module
  * Moved the framework part of the Locale functionality from the Core module to library
  * The Locale logic was split among appropriate classes in library, according to their responsibilities
  * Removed the deprecated DHL functionality
  * Introduced the OfflineShipping module for offline shipping carrier functionality: Flatrate, Tablerate, Freeshipping, Pickup
  * Introduced a separate module for the DHL shipping carrier
  * Introduced a separate module for the Fedex shipping carrier
  * Introduced a separate module for the UPS shipping carrier
  * Introduced a separate module for the USPS shipping carrier
* Framework Improvements:
  * Added the ability to intercept internal public calls
  * Added the ability to access public interface of the intercepted object
  * Added a static integrity test for plugin interface validation
  * Added support for both class addressing approaches in DI: with and without slash ("\") at the beginning of a class name
* Customer Service usage:
  * Refactored the Customer module blocks and controllers to use customer service layer
* Security:
  * Introduced the ability to hash a password with a random salt of default length (32 chars) by the encryption library
  * Utilized a random salt of default length for admin users, and frontend customers

2.0.0.0-dev67
=============
* GitHub requests:
  * [#235](https://github.com/magento/magento2/issues/235) -- Translation escaping
  * [#463](https://github.com/magento/magento2/pull/463) -- allow _resolveArguments to do sequential lookups
* Fixed bugs:
  * Fixed an issue where nonexistent store views flat tables cleanuper dropped the catalog_category_flat_cl table
  * Fixed an issue where the Product Flat Data indexer used the helpers logic instead of the Flat State logic
  * Fixed an issue where an exception was thrown when applying a coupon code
  * Fixed an issue where a Shopping Cart Price Rule was applied to the wrong products
  * Fixed an issue with the broken Related Orders link on the Recurring Profile page
  * Fixed an issue with CMS pages preview not working
  * Fixed an issue with a sales report for a store view returning wrong result
  * Fixed an issue where shipping did not work for orders containing only bundle products
  * Fixed an issue where a custom not found page action did not work
  * Fixed an issue where user configuration for a shopping cart rule to stop further rules processing was ignored
* Modularity improvements:
  * Resolved dependencies of the Sales module on the RecurringProfile module
  * Resolved dependencies of the Email Templates functionality on application modules
  * Lib-only dependent components of the Core module moved to library
  * CSS URL resolving logic moved from the publisher to a separate CSS pre-processor
  * Refactored the View publisher
* Customer Service usage:
  * Refactored the Sales module to use Customer service layer
  * Refactored the Checkout module to use Customer service layer
* Updated various PHPDoc with the parameter and return types

2.0.0.0-dev66
=============
* GitHub requests:
  * [#134] (https://github.com/magento/magento2/pull/134) Fixed a typo in "Vorarlberg" region of Austria (was Voralberg)
* Fixed bugs:
  * Fixed an issue with the "Add to Cart" button on the MAP popup of compound products
  * Fixed an issue where the "Add Address" button for Customer in Admin was broken
  * Fixed an issue where predefined data are not loaded for a newsletter when it is added to a queue
* Indexer implementation:
  * Implemented a new optimized Catalog Category Product Indexer
  * Implemented a new optimized Catalog Category Flat Indexer
  * Implemented a new optimized Catalog Product Flat Indexer
* Modularity improvements:
  * Moved all Configurable Product functionality to a newly created ConfigurableProduct module
  * Moved the Shortcut Buttons abstraction from PayPal to Catalog
  * Moved the Recurring profile functionality to a separate module
  * Moved the Billing Agreements functionality to the PayPal module
  * Finalized the work on resolving dependencies between the Multishipping module, and all other modules. Module can be removed without any impact on the system
* Customer Service usage:
  * Updated Customer Group Grid to use Customer Service for data retrieving and filtering
  * Updated CustomerMetadataService::getAttributeMetadata to throw an exception if invalid code is provided
* Unified the format of specifying arguments for class constructors in DI and in Layout configuration:
  * A common xsd schema is being used for defining simple types. Layout and DI customize common types with their specific ones
  * Argument processing is unified, and moved to library

2.0.0.0-dev65
=============
* Fixed bugs:
  * Fixed inability to execute System Backup, Database Backup, and Media Backup
* Indexer implementation:
  * Implemented a new optimized Catalog Category Flat Indexer
* Cron improvements:
  * Added the ability to divide cron tasks into groups
  * Added the ability to run cron groups in separate processes
* Caching improvements:
  * Added a new mechanism to identify uniquely page content (hash-key for cache storage)
  * Added a tab for Page Cache mechanism in System Configuration
  * Implemented the ability to configure the Varnish caching server settings and download it as a .vcl file
* LESS pre-processing to CSS
  * LESS files in library, theme, module are automatically compiled to CSS during materialization
  * LESS files compilation caching mechanism added in Developer mode
* Modularity improvements:
  * Moved the Shortcut Buttons abstraction from PayPal to Catalog
  * Moved the Recurring Profile functionality to a separate module
  * Moved the Billing Agreements functionality to the PayPal module
* Improvements in code coverage calculation:
  * Added code coverage calculation in the clover xml format for unit tests
* GitHub requests:
 * [#377] (https://github.com/magento/magento2/issues/377) Remove and avoid javascript eval() calls
 * [#319] (https://github.com/magento/magento2/issues/319) No message was displayed when product added to shopping cart.
 * [#367] (https://github.com/magento/magento2/issues/367) Improve the error message from the contact form
 * [#469] (https://github.com/magento/magento2/issues/469) Can't change prices on different websites for custom options
 * [#484] (https://github.com/magento/magento2/pull/484) Calling clear / removeAllItems / removeItemByKey on Magento\Eav\Model\Entity\Collection\AbstractCollection does not remove model from protected _itemsById array
 * [#474] (https://github.com/magento/magento2/pull/474) Change for Options Collection class
 * [#483] (https://github.com/magento/magento2/pull/483) Update Category.php
* Update Customer Service Exception handling and add tests
* Add usage of Customer Service to Customer Module, replacing some direct usage of Customer Model
* Updated various PHPDoc with parameter and return types

2.0.0.0-dev64
=============
* Modularity improvements:
  * Moved abstract shopping cart logic from the Paypal module to the Payments module
* Caching improvements:
  * Added a new mechanism to uniquely identify page content (a hash-key for cache storage)
* Fixed bugs:
  * Fixed an issue with inserting an image in WYSIWYG editor where the selected folder was stored in session
  * Fixed an issue with CMS Page Links not being shown because of the empty text in the link
  * Fixed an issue where zooming functionality was not disabled for the responsive design
  * Fixed an issue with zooming on a configurable product page where the main product image was shown instead of the selected option images
* Updated various PHPDoc with parameter and return types
* Moved quote-related multishipping logic to the Multishipping module
* Resolved dependencies between the Payment and Multishipping modules
* Moved the framework part of the Translate functionality from modules to the library
* Created the architecture for the email template library
* Introduced a consistent approach for using the Config scope
* Fixed an issue with the dependency static test
* Replaced the "magentoZoom" plugin with two widgets: the "gallery" and "zoom"

2.0.0.0-dev63
=============
* Modularity improvements:
  * Consolidated all PayPal-related logic in a separate module
  * Resolved dependencies on the Magento_GroupedProduct module
  * Added the ability to enable/disable/remove the Magento_GroupedProduct module without impact on the system
* Implemented the Oyejorge Less.php adapter
* Implemented the Less files importing mechanism
* Added the ability to configure certain cache frontend, and associate it to multiple cache types, thus avoiding the duplication of cache configuration
* Implemented the more strict format of array definition in the DI configuration:
  * Covered array definitions with XSD, and made the whole DI configuration validated with XSD
  * Added the ability to define arrays with keys containing invalid XML characters, that was impossible when keys were represented by the node names
* Fixed bugs:
  * Fixed an issue with missed image for a cron job for the abandoned cart emails
  * Restored the ability to configure cache storage in `local.xml`
  * Fixed an issue with the css\js merging functionality
  * Fixed an issue with customer selection on the order creation page
* AppInterface renamed to LauncherInterface
* Removed the reinit logic from the Config object
* Framework part of the "URL" functionality removed from modules
* Framework part of the "Config" functionality removed from modules
* Removed the deprecated EAV structure creation method from the EAV setup model
* Updated various PHPDoc with parameter and return types
* Indexer implementation:
  * Implemented a new indexer structure
* Refactored Web API Framework to support the Data Object based service interfaces
* Refactored controllers, blocks and templates of the Sales module to use Customer service
* GitHub requests:
  * [#275] (https://github.com/magento/magento2/issues/275) -- XSS Vulnerability in app/code/core/Mage/CatalogSearch/Block/Result.php
* Removed the outdated Customer service

2.0.0.0-dev62
=============
* Modularity improvements:
  * Moved all Grouped Product functionality to newly created module GroupedProduct
  * Moved Multishipping functionality to newly created module Multishipping
  * Extracted Product duplication behavior from Product model to Product\Copier model
  * Replaced event "catalog_model_product_duplicate" with composite Product\Copier model
  * Replaced event "catalog_product_prepare_save" with controller product initialization helper that can be customozed via plugins
  * Consolidated Authorize.Net functionality in single module Authorizenet
  * Eliminated dependency of Sales module on Shipping and Usa modules
  * Eliminated dependency of Shipping module on Customer module
  * Improved accuracy and quality of Module Dependency Test
* Fixed bugs:
  * Fixed an issue when order was sent to PayPal in USD regardless of currency used during order creation
  * Fixed an issue with 404 error when clicking any button on a Recurring Billing Profile in the backend
  * Fixed an issue with synchronization with Google Shopping on product update caused by missed service property
  * Fixed ability to submit order in the backend when Authorize.Net Direct Post is used
  * Fixed an issue with notice that _attribute_set column is missing during Import/Export
* Removed the deprecated service-calls and data source functionality
* Request\Response workflow improvements:
  * Added Console\Response
  * Changed behavior of AppInterface to return ResponseInterface instead of sending it

2.0.0.0-dev61
=============
* Introduced a new layout block attribute - cacheable
* Fixed bugs:
  * Fixed an issue with displaying configurable product images in shopping cart
  * Fixed an issue with Tax Summary not being displayed properly on the Order Review page
  * Optimized the Plushe theme CSS
  * Fixed attribute types for configurable product variations
  * Fixed an issue with incorrect link in the Reset Password email for customers registered on the non-default website
  * Fixed an issue with creating orders using DHL on holiday dates
  * Fixed product export
  * Fixed 3D secure validation
  * Fixed an issue with session being lost when a logged in user goes from store pages using secure URL to the store pages which do not use secure URL
  * Fixed an issue with price ranges in the Advanced search

2.0.0.0-dev60
=============
* Fixed bugs:
  * Fixed an issue with exceeding the memory limit when uploading very big images
  * Fixed an issue in moving a category when $afterCategoryId is null
  * Fixed an issue when products from a non-default website were not available as bundle items
  * Fixed an issue when orders placed via Authorize.net had the wrong statuses
  * Fixed an issue where orders placed via PayPal Express Checkout could not be placed if HTTPS was used on the frontend
  * Fixed a security issue with a user session during registration
  * Removed a CSRF vulnerability in checkout
  * Fixed an issue with JavaScript static testing framework not handling corrupted paths in white/black lists properly
  * Fixed an issue with Google Shopping synchronization
  * Fixed the contextual help tooltip design
  * Fixed an issue with the Authorize.net CC section UI on the Onepage Checkout page
  * Fixed UI issues on the order pages in the backend
  * Fixed UI issues in the backend for IE9
  * Fixed UI issues on the Edit Customer page in the backend
  * Fixed a UI issue with the image preview placeholder on the Edit Product page for IE9
  * Fixed UI issues with forms in the backend
  * Fixed UI issues with buttons in the backend
  * Fixed an issue with a product status after a virtual product was duplicated
  * Fixed a fatal error with attribute file from the customer account page in the backend
  * Fixed a security issue when CURLOPT_SSL_VERIFYPEER and CURLOPT_SSL_VERIFYHOST where used with improper values sometimes
  * Updated the field descriptions for Secure Base URL settings in the backend
  * Fixed an issue in product duplication for multiple store views
  * Consolidated several 3rd-party JavaScript libraries in the pub/lib directory, and fixed license notice texts to conform to the open source license requirements
* Service Layer implementation:
  * Implemented the initial set of services for the Customer module

2.0.0.0-dev59
=============
* Fixed bugs:
  * Fixed invalid year in exception log errors
  * Fixed the double-serialization in saving data for shipments
  * Fixed an issue with adding a gift wrapping for multiple items
  * Fixed shipping labels generation for DHL
  * Fixed an issue with lost product price and weight during import
  * Fixed a fatal error when a file reference is added to the HTML head
  * Fixed an issue with printing orders containing downloadable product(s)
  * Fixed an issue with the 'Same as shipping' check box not being selected on the Review Order page for PayPal Express checkout
  * Fixed an issue with Email Templates preview showing a blank page
  * Fixed an issue with a refund creation from the PayPal side
  * Removed the occurrences of the non-existing Mage_Catalog_Model_Resource_Convert resource model
  * Fixed an issue with a coupon usage after applying it with multiple addresses
  * Fixed the Abandoned Cart emails sending
  * Fixed an issue where users with "Reorder" permission could not perform reorder
  * Fixed an issue with adding items from wishlist to the Shopping Cart with quantity increments enabled
  * Fixed an issue with the catalog_url indexer incorrect rewrites history for categories
  * Fixed an issue in saving an integration with a duplicate name
  * Fixed an issue when a customer could see someone's else reviews on the private Account Dashboard
  * Fixed an issue when a "New Theme" page was displayed as broken when trying to create a theme with incorrect "Version" value
  * Fixed an issue in saving an integration with XSS injection in the required fields
  * Fixed an issue with the Mini Shopping Cart when it contained virtual product
  * Fixed an issue in disabling the Shopping Cart sidebar
  * Fixed an issue when the "Adminhtml" cookie was not set when a user logged in to the backend
  * Fixed an issue when the "Persistent_shopping_cart" cookie was not set after customer's login
  * Fixed inability to publish products to Google Shopping
  * Fixed inability to download or revert the backup
  * Fixed inability to create a customer account when placing an order with a downloadable product
* Various improvements:
  * Disabled PHP errors, notices and warnings output in the production mode, to prevent exposing sensitive information

2.0.0.0-dev58
=============
* Fixed bugs:
  * Security improved for the Login, Update Cart, Add to Compare, Review, and Add entire wishlist actions on the frontend
  * Removed warnings on category pages when Flat Catalog Category is enabled
  * Fixed product price displayed in wrong currency after switching currency on the frontend
  * Fixed the Save & Duplicate action in product creation
  * Fixed big image scaling in product description
  * Fixed admin dashboard styling issue
  * Fixed validation message for the Quantity field on the product page in the backend
  * Fixed the email template for sharing a Wishlist
  * Fixed the response of the drop-down menu in the Plushe theme
  * Fixed the missing Related Banners tab for Catalog Price Rule
  * Fixed inability to enable the duplicated product
  * Removed warnings on saving payment method configuration
  * Fixed gift messages displaying on the Order View page after admin edits
  * Fixed inability to create a new order status
  * Fixed the behavior of the Save and Previous and the Previous buttons on the Edit Review page
  * Fixed inability to delete a website if the number of websites is less or equal to two
  * Fixed Export on the All Customers page
  * Fixed inability to add products to the Shopping Cart from the Category page in Internet Explorer
  * Fixed logo on the backend login page
  * Fixed visual elements to indicate that Tax details can be expanded on the order creation page in the backend
  * Fixed the CMS page preview design
  * Fixed the newsletter template preview design
  * Fixed the Matched Customers grid design in the Email Reminder Rules
  * Fixed the theme version validation message displayed when creating a new theme
  * Fixed performance degradation during installation wizard execution
  * Fixed cron shell script
  * Fixed user login on the frontend, when the Redirect Customer to Account Dashboard after Logging option is set to No
  * Fixed errors in requests to shipping carrier (DHL International) when the shipping address contains letters with diacritic marks
  * Fixed invalid account creation date
  * Fixed displaying Product Alert links on product view page when the functionality is disabled
  * Fixed the absence of some bundle options when configuring a bundle product in the Shopping Cart on the frontend
  * Fixed the issue which allowed to view and cancel billing agreements belonging to another customer
  * Fixed the content spoofing vulnerability when Solr was used
  * Fixed a potential XSS vulnerability in customer login
  * Fixed RSS feed for categories containing bundle product(s)
  * Fixed inability to place an order with 3D Secure in Internet Explorer 10
  * Fixed inability to place an order with PayPal Payflow Link and PayPal Payments Advanced
  * Fixed integrity constraint violation in catalog URL rewrites
  * Fixed the absence of the error when a wrong website code is specified during a website creation
  * Fixed saving in the backend a new customer address, which contains new customer address attributes configured to be not visible on frontend
  * Fixed USPS shipping method in the checkout
  * Fixed placing orders with recurring profile items via PayPal Express Checkout
  * Fixed email template creation in the backend
  * Fixed the issue with default billing address being used instead of default shipping address during admin order creation
  * Fixed inability to choose DB as Media Storage
  * Fixed PHP issues found during the UI testing of the backend
  * Fixed shipping label creation for USPS Priority Mail Shipping methods
  * Fixed the issue which allowed to create customers with duplicate email
  * Fixed the abstract product block error in the tier price template getter
  * Fixed system message displaying in the backend
  * Fixed the "404" error on customer review page
  * Fixed autocomplete enabled on the admin login page
  * Fixed the 3D Secure iframe
  * Fixed the indicators of mandatory fields on the Package Extension page
  * Fixed product image scaling on the Compare Products page
  * Fixed product page design for products with the Fixed Product Tax attribute
  * Removed spaces between parentheses and numbers in the Cart, Wishlist, and Compare Products blocks
  * Fixed the message displaying the quantity for products found on the Advanced Search page
  * Fixed incorrect caching of locale settings and URL settings during web installation
  * Fixed inability to use a newly created store for admin user roles
  * Fixed absence of the Advanced Search field on the frontend, when the Popular Search Terms functionality is disabled
  * Fixed incorrect link to downloadable product(s) in the email invoice copy
  * Fixed customs monetary value in labels/package info for international shipments
  * Fixed importing for files with blank URL Key field on the store view level
  * Fixed table rate error message
  * Fixed frontend login without pre-set cookies
  * Fixed date resetting to 1 Jan 1970 after saving a design change in the admin panel in case date format is DD/MM/YY
  * Fixed CAPTCHA on multi-address checkout flow
  * Fixed view files population tool
  * Fixed DHL functionality of generation shipping labels
  * Fixed target rule if it is applied for specific customer segment
  * Fixed product importing that cleared price and weight
  * Fixed fatal error when a file reference is added to HTML head
* GitHub requests:
  * [#122](https://github.com/magento/magento2/pull/122) -- Added support of federal units of Brazil with 27 states
  * [#184](https://github.com/magento/magento2/issues/184) -- Removed unused blocks and methods in Magento_Wishlist module
  * [#390](https://github.com/magento/magento2/pull/390) -- Support of alphanumeric order increment ids by the quote resource model
* Themes update:
  * Responsive design improvements
* Improvements in code coverage calculation:
  * Code coverage calculation approach for unit tests was changed from blacklist to whitelist

2.0.0.0-dev57
=============
* Fixed bugs:
  * Fixed [MAP]: "Click for price" link is broken on the category page
  * Fixed tax rule search on the grid
  * Fixed redirect on dashboard if "Search", "Reset", "Export" buttons are clicked on several pages
  * Fixed switching user to alternate store-view when clicking on the Category (with Add Store Code to Urls="Yes" in the config)
  * Fixed printing Order/Shipping/Credit Memo from backend
  * Fixed 404 Error on attempt to print Shipping Label
  * Fixed duplication of JavaScript Resources in head on frontend
  * Fixed inconsistency with disabled states on Configurable product page in the Plushe theme
  * Fixed 3D Secure Information absence on Admin Order Info page
  * Fixed possibility to download or revert Backup
  * Fixed session fixation in user registration during checkout
  * Fixed fatal error during login to backend
  * Fixed inline translations in the Adminhtml area
  * Fixed partial refunds/invoices in Payflow Pro
  * Fixed the issue with ignoring area in design emulation
  * Fixed order placing with virtual product using Express Checkout
  * Fixed the error during order placement with Recurring profile payment
  * Fixed wrong redirect after customer registration during multishipping checkout
  * Fixed inability to crate shipping labels
  * Fixed inability to switch language, if the default language is English
  * Fixed an issue with incorrect XML appearing in cache after some actions on the frontend
  * Fixed product export
  * Fixed inability to configure memcache as session save handler
* GitHub requests:
  * [#406](https://github.com/magento/magento2/pull/406) -- Remove cast to (int) for the varch increment_id
  * [#425](https://github.com/magento/magento2/issues/425) -- Installation of dev53 fails
  * [#324](https://github.com/magento/magento2/pull/324) -- ImportExport: Easier debugging
* Modularity improvements:
  * Removed \Magento\App\Helper\HelperFactory
  * Removed the "helper" method from the abstract block interface
  * Layout page type config moved to library
  * Design loader moved to library
  * Theme label moved to library
  * Remaining part from Adminhtml moved to the appropriate modules. Adminhtml module has been eliminated
  * Core Session and Cookie models decomposed and moved to library
    * \Magento\Stdlib\Cookie library created
    * Session Manager and Session Config interfaces provided
    * Session save handler interface created
    * Session storage interface created, session does not extend \Magento\Object anymore
    * Session validator interface created
    * Session generic wrapper moved to library
    * Messages functionality moved from the Session model as separate component, message manager interface created
    * Sid resolver interface created to handle session sid from request

2.0.0.0-dev56
=============
* Fixed bugs:
  * Fixed placing order with PayPal Payments Advanced and Payflow Link
  * Fixed losing previously assigned categories after saving the product with changed category selector field
  * Fixed losing of a newly created category assignment after variations generation during Configurable product or Gift Card creation
  * Fixed the error in order placement with Recurring profile payment
* GitHub requests:
  * [#299](https://github.com/magento/magento2/pull/299) -- Fix for issue Refactor Mage_Rating_Model_Resource_Rating_Collection
  * [#341](https://github.com/magento/magento2/pull/341) -- Replacing simple preg calls with less expensive alternates
* Modularity improvements:
  * Layout page type config moved to library
  * Design loader moved to library
  * Theme label moved to library
* Themes update:
  * Reduced amount of templates and layouts in Magento/plushe theme
  * Responsive design improvements
* Integrity improvements:
  * Covered all Magento classes with argument sequence validator
  * Added arguments type duplication validator
* Implemented API Integration UX flows:
  * Ability to create and edit API Integrations
  * Ability to delete API integrations that were not created using configuration files
* Removed System REST menu item and all associated UX flows:
  * Users, Roles, and Webhook Subscriptions sub-menu items were removed
* Removed the Webhook module until it can be refactored to use the new Authorization service

2.0.0.0-dev55
=============
* Modularity improvements:
  * Session configuration is moved to library
  * FormKey logic is moved out from Session model
  * SessionIdFlags is removed from Session model
  * Move Page logic to the Theme module and library
* Created UX for the Integration module
* Created authorization service (Magento_Authz module)
  * Implemented an API Authz check in the Webapi framework
* Fixed bugs:
  * Fixed the issue that prevented a customer group's shopping cart rules from applying properly to prices. The issue occurred when a customer was manually assigned to a customer group and automatic group assignment was enabled.
  * Fixed the bug with schema upgrade scripts not running after installation
  * Fixed the error with a blank page when user tries to get access to a restricted resource via URL (add Secret Key for URL set to "No")

2.0.0.0-dev54
=============
* Modularity improvements:
  * Breakdown of the Adminhtml module:
     * Moved Newsletter, Report logic to the respective modules
     * Moved blocks, config, view, layout files of other components from Adminhtml folder to respective modules
  * Removed application dependencies from the library
* Move Magento\Core common blocks in the library
* Application areas rework:
  * Areas are independent from Store
  * Removed deprecated annotation from the getArea methods
* GitHub requests:
  * [#245](https://github.com/magento/magento2/pull/245) -- Resolve design flaws in core URL helper
  * [#247](https://github.com/magento/magento2/pull/247) -- Bug in Mage_Page_Block_Html_Header->getIsHomePage
  * [#259](https://github.com/magento/magento2/pull/259) -- Turkish Lira (TRY) is supported for Turkish members.
  * [#262](https://github.com/magento/magento2/pull/262) -- Update Rule.php
  * [#373](https://github.com/magento/magento2/pull/373) -- [Magento/Sales] Fixed typos
  * [#382](https://github.com/magento/magento2/pull/382) -- [Magento/Core] Fixed typos
  * [#304](https://github.com/magento/magento2/pull/304) -- Removed Erroneous closing "
  * [#323](https://github.com/magento/magento2/pull/323) -- InstanceController.php - made setBody protected
  * [#349](https://github.com/magento/magento2/pull/349) -- Move Mage_Catalog menu declaration into Mage_Catalog module.
  * [#265](https://github.com/magento/magento2/pull/265) -- Update Merge.php
  * [#271](https://github.com/magento/magento2/pull/271) -- Check Data should validate gallery information
  * [#305](https://github.com/magento/magento2/pull/305) -- Extra ", tidied up nested quotes
  * [#352](https://github.com/magento/magento2/pull/352) -- Add Croatia Country as part of European Union since 1st July 2013 for default european local countries in configuration
  * [#224](https://github.com/magento/magento2/pull/224) -- Tax formatting is locale aware and should not
  * [#338](https://github.com/magento/magento2/pull/338) -- Correcting SQL for required_options column
  * [#327](https://github.com/magento/magento2/pull/327) -- cart api bug fix & partial invoice credit memo divide by zero warning
* Themes update:
  * Old frontend (magento_demo) and backend (magento_basic) themes are removed
  * Updated templates and layout updates in the Bundle, Catalog, CatalogInventory, CatalogSearch, Downloadable, ProductAlert, Reports, Sendfriend modules
* Fixed bugs:
  * Fixed the error when  Magento cannot be reinstalled to the same database with table prefix
  * Fixed report Products in Cart
  * Fixed error on attempt to insert image to CMS pages under version control
  * Fixed order status grid so that you can assign state, edit, and view custom order status
  * Fixed Related Products Rule page so that category can be selected on conditions tab
  * Fixed Magento_Paypal_Controller_ExpressTest integration test so it is re-enabled
  * Fixed the bug with international DHL quotes

2.0.0.0-dev53
=============
* Moved general action-related functionality to \Magento\App\Action\Action in the library. Removed Magento\Core\Controller\Varien\Action and related logic from the Magento_Core module
* Moved view-related methods from action interface to \Magento\App\ViewInterface with corresponding implementation
* Moved redirect creation logic from the action interface to \Magento\App\Response\RedirectInterface
* Moved Magento\Core common blocks to the library
* Added reading of etc/integration/config.xml and etc/integration/api.xml files for API Integrations
* Various improvements:
  * Email-related logic from the Core and Adminhtml modules consolidated in the new Email module
* GitHub requests:
  * [#238](https://github.com/magento/magento2/pull/238) -- Improve escaping HTML entities in URL
  * [#199](https://github.com/magento/magento2/pull/199) -- Replaced function calls to array_push with adding the elements directly
  * [#182](https://github.com/magento/magento2/pull/182) -- By default use collection _idFieldName for toOption* methods
  * [#233](https://github.com/magento/magento2/pull/233) -- Google Rich Snippet Code
  * [#339](https://github.com/magento/magento2/pull/339) -- Correcting 'cahce' typo in documentation
  * [#232](https://github.com/magento/magento2/pull/232) -- Update app/code/core/Mage/Checkout/controllers/CartController.php (fix issue #27632)
* Fixed bugs:
  * Fixed JavaScript error when printing orders from the frontend
  * Fixed Captcha problems on various forms when Captcha is enabled on the frontend
  * Fixed "Page not found" on category page if setting "Add Store Code to Urls" to "Yes" in the backend config
  * Fixed Fatal error when creating shipping label for returns

2.0.0.0-dev52
=============
* Better Navigation menu rendering due to improved Caching of Categories
* Added Magento\Filesystem\Directory and Magento\Filesystem\File to the library
* Various improvements:
  * Added a static test to check for incorrect dependencies in the library
  * Moved Magento\Core\Model\Theme to the Magento\View component
  * Moved Magento\Core\Model\Design to the Magento\View component
  * Consistent declaration of page-types
* Themes update:
  * Updated templates and layout updates in the Captcha, Customer, Newsletter, Persistent, ProductAlert, Wishlist modules; old files moved to the "magento-backup" theme
  * Refactored and removed duplicate Persistent module templates
  * Plushe theme is responsive now
* Fixed bugs:
  * Fixed inability to print order, invoice, or creditmemo in the frontend
  * Fixed fatal error caused by the Mage_Backend_Block_System_Config_FormTest integration test
  * Fixed the broken link when the MAP feature is enabled and actual product price is set to be displayed in the shopping cart
* Moved the following methods from Core Helpers to the appropriate libraries:
  * Moved the Data Helper date format related functions to \Magento\Core\Model\Locale
  * Moved the Data Helper array decoration related functions to Magento\Stdlib\ArrayUtils
  * Moved the Data Helper functions that copy data from one object to another to \Magento\Object\Copy

2.0.0.0-dev51
=============
* Application areas rework:
    * Single point of access to the current area code
    * Declare Application Areas
* Various improvements:
  * Breakdown of the Adminhtml module:
     * Moved the Customer-related logic to the Customer module
     * Moved the System-related logic to the Backend module
     * Moved the Checkout-related logic to the Checkout module
     * Moved the Cms-related logic to the Cms module
     * Moved the Promotions-related logic to the CatalogRule and SalesRule modules
  * Eliminated the setNode/getNode methods from Magento\Core\Model\Config and adopted all client code
  * Moved all application bootstrapping behavior to library
  * Moved application-specific behavior from the entry points to the Magento\AppInterface implementations
  * Removed the obsolete behavior from routing and front-controller
  * Refactored the route configuration loading
  * Extracted the modularity support behavior to the Magento\Module component
  * Refactored the Resource configuration loading
  * Removed the obsolete configuration loaders
  * Removed the obsolete configuration from config.xml
  * Refactored the code-generation mechanism
  * Added constructor integrity verification to the Compiler tool
  * Added strict naming rules for the auto-generated Factory and Proxy classes
  * Global functions are now called from app\functions.php
  * Removed functions.php from the Magento\Core module
  * Methods related to mageCoreErrorHandler, string and date were moved from functions.php to the Library components
  * Moved the following methods from Core Helpers to the appropriate libraries:
     * Moved the Abstract Helper to the Magento\Escaper and Magento\Filter libraries
     * Moved the String Helper to the Magento\Filter, Magento\Stdlib\String, Magento\Stdlib\ArrayUtils libraries
     * Moved the Data Helper to the Magento\Math, Magento\Filter, Magento\Convert, Magento\Encryption, Magento\Filesystem libraries and to Magento\Customer\Helper\Data libraries
     * Moved the Http Magento Helper to the Magento\HTTP library
  *  The Hint Magento Helper, Http Magento Helper helpers were removed from the Magento\Core module
  * Implemented SOAP faults declaration in WSDL
  * Web API config reader was refactored to use Magento\Config\Reader\Filesystem
  * Created integrations module. Added 'Integrations Grid' and 'New/Edit' Integration pages in the admin
  * Removed obsolete page fragment code
* Fixed bugs:
  * Fixed inability to create an Invoice/Shipment/Credit Memo if the Sales Archive functionality is enabled
  * Fixed the Minimum Advertised Price link on the Product view
  * Fixed the View Files Population Tool
  * Fixed the error on saving the Google AdWords configuration
  * Fixed the error with the 'Invalid website code requested:' message appearing when enabling payment methods
  * Fixed inability to insert spaces in credit card numbers
  * Fixed inability to print orders from the frontend
  * Fixed the fatal error on removal of reviews that have ratings
  * Fixed JS error with the browser not responding when Virtual/Downloadable product are added to cart
  * Fixed inability to delete a row from the 'Order By SKU' form in Internet Explorer
  * Fixed inability to enable the Use Flat Catalog Product option
  * Fixed inability to configure Grouped and Configurable products during order creation in the backend
  * Fixed inability to insert a widget and/or a banner in CMS pages
  * Fixed inability to set the Quantity value for Gift Cards
  * Fixed the fatal error on the Customer Account > Gift Registry tab in the backend
  * Fixed inability to import with the "Customers Main File" entity type selected
  * Fixed the "Recently Viewed/Compared products" option missing on the New Frontend App Instance page
  * Fixed the fatal error on managing Shopping Cart for a customer with a placed order in the backend
  * Fixed the fatal error on an attempt to create an RMA request for Configurable products
  * Fixed error on the backend dashboard if any value except "Last 24 Hours" is chosen in the "Select Range" dropdown
  * Fixed duplicate values of options in the drop-downs on the RMA pages in the backend

2.0.0.0-dev50
=============
* Modularity improvements:
  * Breakdown of the Adminhtml module:
     * Moved Sales, Catalog, Tax-related logic to respective modules
     * Moved Action, Cache, Ajax, Dashboard, Index, Json, Rating, Sitemap, Survey, UrlRewrite from root of Adminhtml Controller folder
  * View abstraction was moved into library
  * Eliminated dependency in Magento\Data\Form from Magento\Core module
  * Eliminated Magento\Media module
* Themes update:
  * Templates and layout updates are updated in Cms, Contacts, Core, Directory, GoogleCheckout, Page, Payment, PaypalUk, Paypal, Rating, Review, Rss,Sales, Widget modules, old files moved to magento_backup theme
* Layout improvements:
  * Removed page type hierarchy and page fragment types
  * No direct code execution: methods addColumnRender, addRenderer, addToParentGroup usages as action nodes were eliminated
* Fixed bugs:
  * Impossible to add image using WYSIWYG
  * Legacy static test ObsoleteCodeTest::testPhpFiles produced false-positive results
  * Incorrect copyright information

2.0.0.0-dev49
=============
* Various improvements:
  * Unified Area configuration
  * Moved EventManager to Magento\Event lib component
  * Moved FrontController, Routers, Base Actions to Magento\App
  * Created Magento\App component in library
  * Declared public interfaces for View component into library
  * Plushe theme is set as the default theme
  * Refactor the Blacklist Pattern in the Integrity Test Suite's ClassesTest to Replace Blacklist.php Files
  * Removed JavaScript unit test TreeSuggestTest.prototype.testBind as obsolete
  * Introduced ability to register a template engine to process template files having certain extension
  * Removed support of the Twig template engine along with the corresponding component from the library
  * Removed layout flag that forced template blocks to output rendered content directly to a browser bypassing the response object
  * Moved out responsibility of rendering template debugging hints from the template block to the plugin and decorator for a template engine
* Fixed bugs:
  * Fixed inability to create product if multiple attributes are assigned to attribute set
  * Fixed inability to create a new widget instance
  * Fixed error on Customers Segments Conditions tab while the 'Number of Orders' condition is chosen
  * Fixed blank page when placing order via Ogone
  * Fixed various UI issues in Admin Panel with layout, aligning, buttons and fields
  * Fixed static tests failing to verify themes files

2.0.0.0-dev48
=============
* Various improvements:
  * Added static integrity test for compilation of DI definitions
  * Lightweight replacement for PhpUnit data providers is implemented and involved in static and integrity tests with big data providers (primarily file lists)
* Fixed bugs:
  * Fixed broken styles on front-end due to usage of nonexistent stylesheet
  * Fixed plugins configuration inheritance for proxy classes
  * Fixed OAuth consumer credentials expiry not being correctly calculated and added credentials HTTP post to the consumer endpoint
  * Fixed Namespace class references
  * Fixed error on creating shipment with bundle products
  * Fixed uninstallation via console installer
  * Fixed JavaScript error in bootstrap in IE8/9
  * Fixed placing order within PayPal Payments Advanced and Payflow Link
  * Fixed fatal error on placing order with Billing Agreement

2.0.0.0-dev47
=============
* Fixed bugs:
  * Fixed compilation of DI definitions
  * Fixed direct injection of auto-generated proxy classes
  * Fixed usages of auto-generated factories on the library level
  * Fixed fatal error after saving customer address with VAT number
  * Fixed fatal error caused by USPS shipping method with debug
  * Fixed url to Tax Class controller
  * Fixed incorrect subtotal displayed on the Order page
  * Fixed incorrect arguments for shipping xml elements factory
  * Fixed theme editing in developer mode (PHP 5.4)
  * Fixed absent conditions during New Shopping Cart Price Rule creation
  * Fixed fatal error while try to edit created configurable product while Dev Mode enabled (PHP 5.4)
  * Fixed frontend error when persistent shopping cart functionality is enabled
  * Fixed Tax tab
  * Fixed broken link "Orders and returns" on frontend
  * Fixed placing order within OnePageCheckout using online payment methods
  * Fixed error when product is being added to order from backend if Gift Messages are enabled
  * Fixed error when product is being added to cart if MAP is enabled
  * Fixed error when product attribute template is being edited
  * Fixed error when setting configuration for Google API
  * Fixed backend issue when Stores>Configuration>System>Advanced page was not displayed and did not allow to save changes
  * Fixed not executable button "Continue shopping" on Multi-shipping process
  * Fixed error on adding product to shopping cart from cross-sells block
  * Fixed fatal error on Recurring Billing Profiles page
  * Fixed error on setting configuration for Catalog
  * Fixed error on placing order with Configurable product
  * Fixed issue with downloadable product creation
  * Fixed error on update configuration for payment methods
  * Fixed blank page on shopping cart if FedEx shipping method is enabled
  * Fixed fatal error when SID presents in URL
  * Fixed absence of selection of a role assigned to an admin user

2.0.0.0-dev46
=============
* Translation mechanism improvements:
  * Translate function ->__() was removed from Magento model interfaces. Global function __() was created
  * Added I18n tools for translation dictionary generation and language package generation
* Configuration improvements:
  * Implemented Magento Config component that allows to create new configuration types in a simple way
  * Improved default/store/website configuration
     * config.xml file is designed to store only default/store/website configuration data
     * concrete store/website configuration is loaded on demand
  * Improved Install, Category, Product, EAV, Customer, Wishlist, PDF, VDE, Currency, Email Template, Crontab, Events, Routes, Modules, Locale, Import/Export, Indexer, Resources configuration segments:
     * Configuration moved to separate files. Some parts are transformed to DI configuration and moved to `di.xml` files
     * New configuration files are validated with XSD
     * Format of the configuration changed to make possible its validation
  * Improved configuration in `widget.xml`, `fieldset.xml`, `persistent.xml` and `install.xml` files:
     * `install.xml` was renamed to `install_wizard.xml`
     * The configuration is validated with XSD
     * Format of the configuration changed to make possible its validation
     * Some parts are transformed to DI configuration and moved to `di.xml` files
  * Removed `jstranslate.xml` files and moved all message definitions to `Magento_Core_Helper_Js`.
  * List of non-structured nodes from config.xml were transformed into DI configuration
* JavaScript improvements:
  * Prototype.js usages converted to jQuery:
     * Deprecated prototype.js based method removed from app/code/Magento/Weee/view/frontend/tax-toggle.js
     * Removed deprecated prototype.js based file: app/code/Magento/Checkout/view/frontend/opcheckout.js
     * Updated to use jQuery redirectUrl widget vs prototype based solution:
         * app/code/Magento/Oauth/view/adminhtml/authorize/form/login.phtml
         * app/code/Magento/Oauth/view/frontend/authorize/form/login.phtml
         * app/code/Magento/Catalog/view/frontend/product/list.phtml
  * Removed file containing jQuery that did not meet the Magento 2 coding standard. Replaced with redirect-url widget
     * app/code/Magento/Catalog/view/frontend/js/mage-attributes-processing.js
  * Updated to meet Magento 2 coding standard: app/code/Magento/Checkout/view/frontend/cart/item/default.phtml
  * Added jQuery widgets:
     * mage.deletableItem - Widget to tag DOM element as deletable, by default on click
     * mage.fieldsetControls & mage.fieldsetResetControl - Widget to easily reset a subset of form fields with a reset ui control
     * mage.itemTable  - Widget to easily add a data template block dynamically on an event, by default click.
     * mage.redirectUrl - Simple widget to allow for consistent javascript based redirects that meet the Magento 2 coding standard
     * Added new validation rules for validation widget: 'required-if-not-specified', 'required-if-specified', and 'validate-item-quantity'
* Various improvements:
  * Changed VendorName from Mage to Magento
  * Implemented PSR-0 and PSR-1 Coding Standards
     * All Magento source code has been converted.
     * Tests have been written to enforce PSR-0 and PSR-1 coding standards.
  * Removed empty module setup models. Core resource setup model is used as a default setup model now. Custom setup model must be injected via DI configuration
  * Removed some events (plugins must be used instead):
     * adminhtml_widget_container_html_before
     * admin_session_user_logout
     * model_config_data_save_before
     * admin_system_config_section_save_after
     * backend_menu_load_after
     * catalog_controller_category_init_before
     * catalog_helper_output_construct
     * catalog_controller_product_init
     * catalog_category_tree_move_before
     * catalog_category_tree_move_after
     * catalog_product_website_update_before
     * catalog_product_website_update
     * catalog_product_media_save_before
     * catalog_product_media_add_image
     * catalog_product_type_grouped_price
     * catalog_product_collection_load_before
     * catalogsearch_index_process_start
     * catalogsearch_index_process_complete
     * cms_page_get_available_statuses
     * cms_wysiwyg_config_prepare
     * application_clean_cache
     * theme_copy_after
     * customer_registration_is_allowed
     * log_log_clean_before
     * log_log_clean_after
     * sales_convert_quote_payment_to_order_payment
     * sales_convert_quote_item_to_order_item
     * sales_quote_config_get_product_attributes
  * Removed the Poll module including references and dependencies to/on it.
* Redesign and reimplementation of web services framework
  * Removed the Api module and all existing SOAP V1, SOAP V2, and XML-RPC web services code
  * Implemented new web services framework to support both REST and SOAP based off of a common service interface
  * Implemented a 2-legged OAuth 1.0a based authentication mechanism for both REST and SOAP API calls
* Layout improvements:
  * Arbitrary handle name moved to handle node, id attribute
  * New arguments format, which introduce argument types implemented
  * Translation specified just on the level of node which is going to be translated
  * XSD validation for Layouts XML added
  * Referential integrity check with XSD introduced
  * Added ability to update containers via references
  * Type casting for all kind of types (url, option, array and simple types) added
  * Covered introduced argument types with integrity test
  * Types restrictions was implemented
  * Removed access to direct execution of API through layout by removing <action> nodes
  * Implemented ability to declare containers in layout that don't have any specific semantic value
  * Removed handle declaration from layout update files. Name of the file stands for the handle ID and handle's attributes are defined in the root <layout> node
* PHP 5.4 and 5.5 support:
  * Made application compatible with PHP 5.4 and 5.5
  * Removed workarounds for older PHP versions
  * Minimum supported PHP version is set to 5.4.0
* God Class Mage Eliminated
* Fixed bugs:
  * Fixed address field "State/Province" on frontend, which contained "[object Object]" items instead of necessary values
  * Fixed overriding/extending of global plugin configuration in area specific configuration

2.0.0.0-dev45
=============
* Product management improvements:
  * Added ability to create variation on the Product creation page
  * Added ability to add attributes on the Product creation page. Attribute values also can be added directly from the Product creation page
  * Removed "Delete" button from the Product Edit page. Products can be deleted from Products Grid only
  * Enhanced Product Edit page
  * Improved visual styles and business logic for new category creation from product creation page
  * Updated button names for Grouped and Bundle products, added an ability to translate them
  * Changed design of all popup windows on product creation page
  * Simplified UI to add an attribute: made less fields there by default, restructured element positions, hidden rarely-used controls
  * Created a popup to select image for product variations
* JavaScript improvements:
  * Eliminated `json2.js` library since JSON parsing is bundled in all supported browsers
  * `Ajax.Autocompleter` is replaced with jQuery suggest widget for search in backend
  * `jsTree` jQuery plugin is utilized for User Roles, Api Roles, CMS Pages and URL Rewrites management pages in backend
  * Improved jQuery validation for credit cards
  * Added support of `$.mage.component` in some frontend themes
  * Further refactoring of JavaScript to use JQuery library:
     * Scripts are converted in the following modules and components: Centinel, Authorize.net, Payflow Link, Payflow Pro, Paygate, Paypal Express, Checkout, Captcha
     * Refactored Prototype-based implementation of validation in "New Category" dialog to use jQuery
     * Removing Prototype inclusion in several places
  * Enhanced menu behavior in backend
* VDE improvements:
  * Implemented inline translate tool for VDE
     * Added new dedicated button "T" in interface
     * 3 different modes: Page Text, Variable Text (for script texts), Alternative Text (for attributes)
     * Independent enabling of inline translation on frontend and in VDE
  * Modified some text messages in VDE and in themes management
  * Added ability to upload, browse and delete images and fonts that can be used in custom CSS
  * Added ability to duplicate a theme
  * Added ability to revert theme modifications to a last saved checkpoint
  * Improved theme's background image handling
  * Added alert, when deleting a block
  * Removed drag-n-drop feature
  * Refined and streamlined interface
* HTML improvements:
  * Enhanced accessibility in admin by labeling form fields
* Payment improvements:
  * Incorporated changes to the PayPal UI configuration from CE 1.7.0.1
     * Moved PayPal configuration to the Payment Methods menu section
     * Set the default value of the cUrl `VERIFYPEER` option to `true` for PayPal and added the ability to change this value
     * Changed the design and position of the configuration field tooltips
  * Removed support of Moneybookers payment method and underlying module in favor of 3rd party extensions
  * Implemented support of PayPal IPN protocol HTTP 1.1
  * Implemented a single place to configure credentials for Payflow Link and Express Checkout
* System Configuration improvements:
  * Added the functionality for creating nested field sets
  * Implemented the support for the extended and shared configuration fields
  * Added the ability to define dependencies between fields from different field sets
* `Varien_Image` library refactored:
  * Created adapters factory instead of class `Varien_Image_Adapter`
  * Refactored ImageMagick and GD adapters to make them testable
  * Added feature of generating image from text
* Support of Google services:
  * Changed module `Mage_GoogleOptimizer` to support Google Content Experiment instead of Google Optimizer
  * Implemented support of Google AdWords on the checkout success page
* DI improvements:
  * Added ability to configure DI for individual class instances
  * Added ability to pass differently configured instances to different parts of the system
  * Refactored proxy and factory generation mechanism
* Layout improvements:
  * Implemented all-new mechanism of layout merging and customization:
     * Convention over configuration approach is used, i.e. there is no need to declare layout files in module configs anymore
     * Added support for merged modular layout files in a theme instead of single `local.xml` theme file
     * All theme `local.xml` files are broken down and moved to theme modular layout files
     * All the layout files are broken into smaller ones - one layout handle per one file, so that code duplication cane reduced, when overriding layout files in themes
     * Covered new layout customization mechanism with integrity tests
  * Relocated several files, declared in layouts
  * Streamlined several design customizations
* Various improvements:
  * Refactored fallback paths to prevent searching of modular view files in non-module context, covered application with appropriate integrity test
  * Added configuration for limits on sending wishlist emails
  * Refactored default theme fixture in integration tests in order to divide it into smaller and easier to understand fixtures
  * Moved Currency Symbol module files from Adminhtml module to the module itself
  * "Contact Us" page is available through HTTPS only
  * Language selector for backend interface removed from footer. Language can be chosen on My Account page or on backend user edit page
  * Updated page titles in backend
  * Improved mechanism of notification and system messages in backend. All related blocks and controllers are moved to AdminNotification module. Enhanced visual representations of notifications: bubble for unread messages, popup for notifications and their descriptions
  * Updated text of some system messages, backend interface messages, backend menu items
  * Several classes are refactored to use Event Manager instead of `Mage::dispatchEvent()`
  * Improved test coverage of entry point classes
  * Improved authorization logic to be reusable with minimal configuration changes
  * Introduced App Area in Integration Testing Framework
  * Improved media entry point
  * Added plugins/interceptors support for easier extensibility of Magento functionality
  * Added `application_process_reinit_config` event, so that it is possible to react, when Magento config gets reinitialized
  * Added "less" to a list of files that are not published to the public directory during deployment process
  * Eliminated requirement of write access to `pub/static` directory in "production" mode. "Developer" and "default" modes still require write access to this directory
  * Improved test coverage of recently introduced `Mage_Core_Model_Config_` classes
  * Added proper description to the error message, shown when uploading too big file with a content to import
  * Refactored `Mage_Core_Model_Design_Package` - broken it down into several smaller classes according to the sets of responsibilities
  * Refactored Theme and Theme Service models to follow best practices of OOP design
  * Removed legacy API tests
  * Improved transparency of cache control by tag scope in the framework
  * Improved verification process for the application directories write-access by moving it to the top-level of framework initialization
  * Introduced separate configurable application directory to be used for merged Javascript files
  * Implemented support for minification of Javascript files; JSMin library adapter is created
  * Implemented explicit usage of cache type in collections; engaged it for website, store and store view collections; added tests for a number of collections
  * Implemented explicit usage of cache types in translations
  * Implemented explicit usage of cache types in layouts
  * Removed ability to set limits on maximal amount of categories, products, websites, stores, store views and admin users as an unusable feature
  * Improved and simplified path normalization methods in `Magento_Filesystem` component
  * Implemented proper exceptions instead of PHP warnings in `Magento_Filesystem` component
  * Introduced `Mage_Core_Model_ModuleManager` to provide "enabled" information about modules
  * Enabled following cache types in integration tests to improve performance: configuration, layouts, translations, EAV
  * Improved and refreshed design for backend
  * Removed "demo_blue", "iphone" and "modern" themes
  * Converted more backend grids from PHP implementation to layout declarations
  * Refactored experimental implementation of Service Calls Framework
     * All code from to `lib/Magento/Datasource` and `app/code/Mage/Core/Datasource` moved to `app/code/Mage/Core/DataService`
     * Added service calls support in Layout model
     * Fixed bugs and architectural flaws
     * Changed service calls declaration
     * Improved test coverage
     * Introduced mechanism to retrieve values from nested arrays by path
     * Added Invoker class to invoke service calls by given name
     * Added ability to define system configuration options via service calls. Refactored implementation for select field in XML
  * Refactored support of Twig templates
     * Removed experimental implementation of product view page with Twig templates
     * Template abstraction moved to separate module `Mage_Core_Model_TemplateEngine`
     * Modified various blocks and templates due to inability to call protected methods from templates
     * Improved test coverage
  * Refactored support for webhooks
     * Code that provides communication with outbound endpoint moved to library `Magento_Outbound`
     * Code that provides implementation of publish-subscribe mechanism and instruments moved to library `Magento_PubSub`
     * Removed experimental webhook implementation in client code
     * Used WebApi mechanism to provide authorization
     * Improved UI for working with webhooks in Magento backend
     * Improved test coverage
  * Removed support of callbacks from the framework
* GitHub requests:
  * [#71](https://github.com/magento/magento2/pull/71) -- Add event prefix for Cms blocks
  * [#108](https://github.com/magento/magento2/pull/108) -- Fix issue with `PHP_VERSION` on Ubuntu servers
  * [#110](https://github.com/magento/magento2/pull/110) -- Fixes `Varien_Io_Sftp::write`, `Varien_Db_Adapter_Pdo_Mysql::insertOnDuplicate`
  * [#123](https://github.com/magento/magento2/pull/123) -- Performance problem & memory leak in `Mage_Index_Model_Process`
  * [#125](https://github.com/magento/magento2/pull/125) -- Ability to disable triggering controller action
  * [#148](https://github.com/magento/magento2/pull/148) -- Fixed readability
  * [#156](https://github.com/magento/magento2/pull/156) -- Event `adminhtml_cms_page_edit_tab_content_prepare_form` and `$form->setValues($model->getData());` in wrong order
  * [#161](https://github.com/magento/magento2/pull/161) -- FIXED `http://www.magentocommerce.com/bug-tracking/issue/?issue=7419`
  * [#176](https://github.com/magento/magento2/pull/176) -- Add print/log query flags to collection
  * [#202](https://github.com/magento/magento2/pull/202) -- Installer fails to detect `InnoDB` on `MySQL 5.6+`
  * [#215](https://github.com/magento/magento2/pull/215) -- There is no sort-order "best value"
  * [#217](https://github.com/magento/magento2/pull/217) -- Update `app/code/core/Mage/Adminhtml/locale/de_DE/Mage_Adminhtml.csv`
  * [#243](https://github.com/magento/magento2/pull/243) -- Fix helper for determining system memory usage on Windows (pull request for issue #237)
  * [#267](https://github.com/magento/magento2/pull/267) -- Issue with camel case in custom defined source models
* Bug fixes:
  * Fixed absence of a product for store view created after the product
  * Fixed incorrectly displayed or absent product image on configurable product pages
  * Fixed incorrectly displayed Tier Price message for Bundle product in backend
  * Fixed absence of configured options, when composite product is edited from wishlist
  * Fixed inability to set product rating from backend
  * Fixed bug with adding product with decimal quantity
  * Fixed bug with incorrect theme saving when wrong preview file is uploaded
  * Fixed incorrectly displayed error message when unsupported JavaScript file is uploaded while editing a theme
  * Fixed bug with incorrect price and stock availability information
  * Fixed absence of "Delete" button on Widget Instance and Edit Custom Variable pages
  * Fixed inability to change PayPal configuration
  * Fixed inventory return after cancelling order, paid with PayPal Website Payment Standard method
  * Fixed removal of all the items from shopping cart, when cancelling payment by PayPal Website Payment Standard method
  * Fixed issue with customer address saved in `sales_flat_quote_address` table as `null` or as default address instead of new one during checkout
  * Fixed hard dependency on GD extension during installation. Now the application can be installed if any of GD or ImageMagick extension is enabled
  * Fixed handling of creation a customer with already existing e-mail in backend
  * Fixed exception on customer edit page, when profiler is enabled
  * Fixed removal of "NOT LOGGED IN" customer group, when attempting to delete nonexistent group
  * Fixed absence of a welcome email for a new customer that is created in backend
  * Added validation of customer DOB
  * Fixed bugs related to "Add Store Code to Urls" configuration setting: the setting applied to backend and produced exceptions on frontend
  * Fixed inability to edit Newsletter Template
  * Fixed inability to preview Newsletter Template while creating it
  * Fixed inability to save Configuration from "Web" tab
  * Fixed incorrect roles assignment for backend users
  * Fixed incorrect message during checkout via Authorize.Net
  * Fixed inability to create order in backend with Authorize.Net as payment method
  * Fixed unexpected alert during one-page checkout
  * Fixed bug with broken RSS link on some pages
  * Fixed inability to delete non-empty customer groups
  * Fixed bug with absent tracking number in notification email
  * Fixed JS bug in bundle products
  * Fixed bug with missing product configuration in bundle products
  * Fixed absence of a summary for a configured bundle product on Product View page
  * Fixed bug with missing wishlist grid on customer configuration page
  * Added validation for the "Weight" field in Product Create/Modify admin form
  * Fixed infinite loop in reports, when one of the GET-parameters was not submitted
  * Fixed integration test that failed at the midnight
  * Fixed image placeholder, being displayed instead of Base image, in Product View page
  * Fixed multiple bugs in IE 8 and 9
  * Restored export for table rates
  * Fixed weight calculation for DHL
  * Fixed anchor categories, which didn't show products from child categories
  * Fixed exception, when applying catalog price rules
  * Disabled "State" dropdown for Tax Rates in countries, where there are no states
  * Fixed inability to save a CMS page
  * Fixed Javascript calendar in backend Customer grid
  * Fixed issues with fields validation on order management page
  * Fixed taxes on Bundle product page
  * Fixed "Rating isn't available" message on Edit Review page
  * Fixed lack of data in New Order emails, when order is composed at backend
  * Fixed exception message, when importing wrong tax rates file
  * Fixed editable multiselect control - new value was not shown there, when added
  * Fixed exception, when saving a configurable product without associated products
  * Fixed inability to properly save system configuration, when submitting files there
  * Fixed performance issue with excessive generation of category menu on "Add to Cart" page
  * Fixed amounts, being shown in a wrong currency, when viewing created order
  * Fixed calculation for amount of items, remaining in an order, after shipping and invoicing
  * Fixed wrong price, calculated, when ordering multiple bundle products in backend
  * Fixed issues with changing order statuses in backend
  * Tested backend design, fixed the discovered issues - general and browser-related bugs
  * Fixed order items, that have been shipped with Free Shipping method, to have "free shipping" status
  * Fixed issue with a State field being required in countries, where it is not mandatory
  * Fixed inability to upload a file via File custom option, when ordering a product at frontend
  * Fixed incorrect cron timezone settings
  * Fixed performance issues with product saving in case of concurrent search requests
  * Fixed bug in migration script
  * Fixed incorrect email when "Send auto-generated password" was hit
  * Fixed bug with missing category image
  * Fixed incorrect handling of `GET` parameter `isAjax` after session expiration
  * Fixed incorrect translation of "month" field for customer's birthday
  * Fixed Google Analytics script inclusion
  * Fixed bug with excessive custom rewrites after reindex
  * Fixed performance tests failure on login page
  * Fixed incorrect value for average rating on Edit Review page
  * Fixed bug with incorrect module configuration overriding
  * Fixed exception in Nominal Tax model
  * Fixed bug in sitemap URL used in `robots.txt`
  * Fixed bug with incorrect `custom_design` field value during export
  * Fixed bug with incorrect RSS title
  * Fixed CSS style for validation message in CMS widgets
  * Fixed bugs in `Mage_Tag` module on product creation page
  * Fixed incorrect Products In Cart report
  * Fixed incorrect price for bundles with default quantity more than 1
  * Fixed displaying of "Import Behavior" section in the `System -> Import` page
  * Fixed exception, when importing a CSV file with Byte Order Mark
  * Removed remains of code pools in JavaScript tests
  * Fixed bugs in shipping label creation
  * Fixed inability to save some sections of configuration
  * Fixed bug with empty "New Shipment" e-mail
  * Fixed inability to save Attribute Set in IE8
  * Fixed wrong tax summary for partial invoices and credit memos
  * Fixed bug with categories custom design, where the chosen theme was not applied
  * Fixed empty list of themes in CMS pages and Frontend Apps backend sections
  * Fixed fatal error, when trying to access a customer account in a non-installed Magento
  * Fixed Javascript error, when accessing system Design configuration in Chrome
  * Fixed wrong representation of a widget on frontend, after hiding and showing WYSIWYG editor during CMS page modification
  * Fixed exception, when using 2-level cache backend
  * Fixed random test failures in `Mage_CatalogSearch_Block_Advanced_ResultTest`
  * Fixed duplication of a view file signature, e.g. "file.ext?mtime?mtime"
  * Prevented tracking of merged Javascript files metadata (and re-merging them) in production mode
  * Fixed incorrect memory usage calculation in Integration tests
  * Fixed issues in performance test scenarios
  * Fixed inability to delete customer's address on frontend
  * Fixed incorrect "No file chosen" message, shown after a successful upload of product image placeholder in Chrome
  * Made "Print Order" page to display theme-customized logo instead of a default one
  * Fixed other bugs in management of categories, products, product attributes, product templates (attribute sets), customers, taxes and tax rules
  * Product creation fixes:
     * Fixed inability to search and select category in IE8, including via mouse
     * Fixed usability of category search tree field to not hang after entering each symbol
     * Fixed inability to select/change attribute for product variations (configurable product) in IE8
     * Fixed field highlighting and error placement after validation on "Create Category" dialog
     * Fixed validation of parent category to be a require field
     * Fixed bug with displaying special price for a product on frontend after the product template is switched to one without special price
     * Fixed incorrectly displayed regular price for products with catalog price rule applied
     * Fixed inability to upload an image in the WYSIWYG editor
     * Fixed Javascript error, when replacing variation image in IE
     * Fixed Javascript errors in production mode
     * Unified look of all the popups
     * Removed "Add Attribute" link, when Product Details section is collapsed
     * Fixed issue with product template selector menu, which was not shown
  * Shopping Cart Price Rule fixes:
     * Fixed inability to save Shopping Cart Price Rule with Coupon = "No Coupon"
     * Fixed saving of Shopping Cart Price Rule having specific coupon
     * Fixed absence of fields on rule information tab
  * Payment fixes:
     * Fixed PayPal Pro (formerly Website Payment Pro) to pass shipping address in request to PayPal service
     * Fixed triggering of a credit memo creation when Charge Back notification comes from PayPal
     * Fixed emptying shopping cart after canceling on PayPal page
     * Fixed error "10431-Item amount is invalid." when a Shopping Cart Price Rule is applied in Express Checkout Payflow Edition
     * Fixed PayPal Payments Pro Hosted Solution to send "City" in place of the "State" parameter for UK and CA, if Region/State is disabled in the configuration
     * Fixed ability to invoice order without providing payment using Google Checkout API
     * Fixed validation of a Discover card number
     * Fixed issues in configuration for payment methods: absence of "Sort Order" field, excessive fields with class name as a value, issues with form elements and groups
     * Fixed exception, when using 2-level cache backend
     * Fixed inability to place order with PayPal Payments Advanced and Payflow Link payment methods
  * VDE fixes:
     * Removed full file path information from the title of an uploaded store logo
     * Fixed bugs in VDE with color picker, file uploader, themes assigning, Remove and Update buttons for custom CSS
     * Fixed hint for the Scripts palette in dock
     * Fixed inability to upload more than one Javascript file
     * Fixed bug with improper scaling images in UI
     * Fixed inability to preview and edit a physical theme
     * Fixed inability to delete a block
     * Fixed inability to delete a background image
     * Fixed preview of a virtual theme in production mode
     * Fixed JavaScript tests
     * Fixed bugs with inline translation
     * Added validation to the theme name field
     * Fixed absence of error message in IE, when uploading unsupported file type in Theme Javascript
     * Fixed corrupting of a `custom.css` file, when saving Custom CSS text
     * Fixed wrong design of "Chain" and "Reset to Original" image buttons
     * Fixed color picker, being cut off by small browser window
     * Fixed bottom indent in "Quick Styles" panel - it was too big
     * Fixed corrupted layout of "Image Sizing" tab, when resizing browser window
     * Fixed "We found no files", being displayed all the time in the form to upload theme Javascript files
  * API fixes:
     * Added missing fields to SOAP API
     * Fixed inability to set default customer address
     * Fixed error message, when saving a customer with wrong email address
     * Fixed inability to create partial order shipment
     * Fixed absence of special price information in return of `productGetSpecialPrice` method
     * Fixed incorrect content length of server response
     * Fixed absence of `productAttributeAddOption`, `catalogProductAttributeUpdate`, `catalogProductAttributeTypes`, `catalogProductAttributeRemoveOption` and `catalogProductAttributeInfo` methods with WS-I mode enabled
     * Fixed absence of `catalogProductDownloadableLinkList` method
     * Fixed bug with incorrect credit memo creation when order item id is set
     * Fixed bug with inability to update stock status or price of multiple products in one call
     * Fixed `shoppingCartOrderWithPaymentRequestParam` method description in WSDL
     * Fixed inability to add comment to order without changing order status
     * Fixed incorrect redirect after SOAP POST request
     * Fixed inability to end session by `endSession` method
     * Fixed Save button for Web Services User Roles in backend
     * Fixed memory issue due to incorrect filtering for the single field in `salesOrderList` method
     * Fixed bug with getting product information by numeric SKU
     * Fixed inability to add configurable product by `cart_product.add` method
     * Fixed ACL initialization in WebApi
     * Fixed bug with the same cache key used for both WS-I and non WS-I WSDL files
     * Fixed bug with updating shopping cart by `shoppingCartProductUpdate` method
     * Fixed product id validation in `shoppingCartProductAdd` method
     * Fixed absence of tracking number in `salesOrderShipmentInfo` method response
     * Fixed absence of tracking number in shipment transactional email

2.0.0.0-dev44
=============
* Product creating & editing:
  * Added ability to control base text styling without WYSIWYG when editing description fields
  * Added validation for price and quantity fields
  * Removed category suggest limit
* Product template management:
  * Automatically update Product Template when modifying structure in Create Product flow
  * Improvements to change attribute set functionality
* Refactored JavaScript to use JQuery library:
  * Refactored the following pages: catalog tags, one page checkout, multishipping checkout, gift options, gift messages (across the board)
  * Converted jQuery popupwindow.js plugin to a jQuery widget
  * Replaced Prototype code for Switch/Maestro and Solo credit card with jQuery widget
  * Replaced Prototype Validation with jQuery validation plugin
  * Converted credit card payment tool tip to jQuery in all themes
  * Removed legacy JS files from all themes
* Various improvements in look & feel of backend UI:
  * Styling of components: catalog, sales, customers, reports, CMS, newsletter
  * Generic styling: grids, popup windows
  * Changes to support IE browser
* Enhancements in "suggest" JavaScript widget:
  * Ability to delete selected item using keyboard
  * Ability to display all available search items, if "recent items" is empty
  * Fixes of behavior of currently selected elements and "spinner"
  * Display "No Records" message in suggest widget if all items already selected
  * Fixed suggest widget to no longer show deleted items
* Improved `Magento_Test_Helper_ObjectManager` in unit tests to discover types of constructor arguments
* Removed workaround of unsetting objects referenced in `tearDown()` of integration tests
* Updated Menu and Navigation layout, including redesigned backend menu item System -> My Account
* Made store address format consistent with format of shipping origin address
* Added ability to navigate directly to a section in backend system configuration, with corresponding accordion expanded
* Removed some of unnecessary coupling between several modules
* General improvements to unit and integration test code coverage, as well as compliance with coding standards
* Application framework:
  * Implemented ability to compress/decompress data in a cache backend
  * Verified ability to disable in configuration triggering of system upgrade
  * Abolished code pools and the mechanism of overriding files using include\_path (without alternative)
  * Implemented segmentation of cache by types -- ability to assign separate cache configuration per type. Reviewed and verified possibility to isolate configuration cache segment
  * Segregated application configuration into several layers. Primary configuration is used by the object manager and loaded before application is initialized
  * Instead of `Zend\Di`, implemented `Magento\ObjectManager` library that has less features and suits Magento application needs better in terms of performance
  * Introduced "context" object as dependencies for super-classes (`Mage_Core_Model_Abstract`, `Mage_Core_Block_Abstract`, etc) to reduce complexity of their constructors' API
  * Implemented tools for pre-populating all auto-generated proxy and factory classes, used by dependency injection framework
  * Replaced "developer" mode with general "mode", that has 3 states: developer, default, production
  * In "production" mode, the application will not invoke fallback for static view files (images, CSS-files, JavaScript). Instead, it will assume that they are already placed in a fully qualified location. Added tools for populating static view files from `app` directory into `pub/static`
  * Introduced support for Twig templating
    * template rendering, including phtml, was abstracted into a `Mage_Core_Block_Template_Engine` to make support for other template engines easier
    * included Magento-specific Twig functions and filters
    * phtml templates can now only access public methods of the corresponding Block class
    * ability to define dependencies on data provided by a service that is then made available to the templates -- eliminates some of the code in Blocks
  * Introduced support for webhooks and callbacks: outbound HTTP requests for notifications and real-time integrations
  * Added ability to define options for System Configuration select fields in XML: static options are defined inline, dynamic options can reuse data provided by a service
* Moved product business logic found in blocks into `Mage_Catalog_Service_Product` to consolidate logic into a single structure that both controllers and web services can invoke
* Converted product view page to demonstrate use of Twig templates and services
* Updated shipping carrier `collectRates` logic to support remote callbacks and converted the FedEx shipping carrier to comply with the same interface
* Added webhook support for the following topics: `customer/created`, `customer/updated`, `customer/deleted`, and `order/created`
* Visual design editor:
  * Ability to view all CSS-files of a theme
  * Ported numerous features of visual design editor from Magento Go 1.x to Magento Core 2.x: style editing, managing catalog images
  * Various improvements in UI
  * Improved image sizing functionality
  * Improved test coverage
  * Ability to launch physical themes, including workflow preview mode and workflow design mode
  * Ability to duplicate existing themes for customization
* GitHub requests
  * [#162](https://github.com/magento/magento2/pull/162) -- classmap needs to be prepended to autoloader stack to have any effect
  * [#179](https://github.com/magento/magento2/pull/179) -- fix that makes `Mage_Install` compatible with the new version of SimpleXml
  * [#180](https://github.com/magento/magento2/pull/180) -- fixed `getBaseUrl()` when type was injected via setter
  * [#203](https://github.com/magento/magento2/pull/203) -- fixed problem with login in to backend area on php 5.4
  * [#216](https://github.com/magento/magento2/pull/216) -- explicit nullification of `$_store` in `Mage_Core_Model_Sore_Storage_Db->_initStores()`
  * [#220](https://github.com/magento/magento2/pull/220) -- make topmenu HTML editable by an event
  * [#221](https://github.com/magento/magento2/pull/221) -- changed minimum required PHP version from PHP 5.2.3 to 5.3.3
* Bug fixes:
  * Restored missing Paypal configuration options
  * Fixed numerous display issues on the following pages: admin login, product management, category management, CMS poll, VDE, tax, shipping
  * Fixed XSS vulnerability related to customer data & bundle options
  * Fixed "Preview Theme" functionality
  * Fixed JS File upload problem with Internet Explorer
  * Replaced `truncateOptions` function in `varien/js.js` with inline widget
  * Fixed broken XPaths in `SystemConfiguration.yml`
  * Fixed jQuery metadata plugin's data attribute scanning for validation
  * Synchronized default value of `quantity_and_stock_status` with Stock Availability control
  * Fixed display of G.T. Purchased column in Order grid when order in non-default currency
  * Fixed Foreign Key support for MS SQL
  * Fixed "Create Customer" functionality on New Order screen
  * Restored State/Province field to Review Order page
  * Fixed Add New Tax Rate functionality
  * Fixed problem with displaying New Shopping Cart Price Rule tab
  * Fixed problem of configurable product options getting lost when adding product to wishlist
  * Fixed UPS Shipping label printing
  * Fixed performance issue with Catalog Management
  * Fixed input file type validation when importing customers
  * Fixed custom product placeholder image display
  * Added missing files referenced by `quick\_style.css`
  * Fixed validation error messaging and message placement
  * Fixed access problem to SOAP/XML User and Roles pages
  * Fixed access problem created when editing your own permissions
  * Several fixes for problems with cleaning cache in tag scope
  * Fixed invalid link problem in Gift Card email
  * Fixed problem with deleting selected product category after changing attribute set
  * Fixed theme management for Windows by adopting `Magento_Filesystem` abstraction to access directories
  * Fixed cart rendering in case of empty cart
  * Remove duplicate "Link to Store Front" link from admin, made obsolete by "Customer View" link
  * Removed "Flat Rate" from pre-installed shipping methods

2.0.0.0-dev43
=============
* Implemented functional limitation that restricts max number of catalog products in the system
* Implemented cache backend library model for MongoDB
* Converted some more grids in backend from PHP implementation to declarations in layout
* Removed `app/etc/local.xml.additional` sample file, moved detailed description of possible configuration options to documentation
* Refactored `Mage_Core_Model_EntryPointAbstract` to emphasize method `processRequest()` as abstract
* Moved declaration of functional limitations to the nodes `limitations/store` and `limitations/admin_account`
* Bug fixes:
  * Fixed JavaScript and markup issues on product editing page in backend that caused erroneous sending of AJAX-queries and not rendering validation messages
  * Fixed issues of application initialization in cases when `var` directory doesn't have writable permissions. Writable directories are validated at an early stage of initialization
  * Fixed array sorting issues in test `Magento_Filesystem_Adapter_LocalTest::testGetNestedKeys()` that caused occasional failures

2.0.0.0-dev42
=============
* Application initialization improvements:
  * Removed application initialization responsibility from `Mage` class
  * Introduced entry points, which are responsible for different types of requests processing: HTTP, media, cron, indexing, console installing, etc.
  * New configuration classes are introduced and each of them is responsible for specific section of configuration
  * Class rewrites functionality removed from `Mage_Core_Model_Config` model. DI configuration should be used for rewriting classes
* Added ability to configure object manager with array in addition to object and scalar values
* VDE improvements:
  * Theme CSS files viewing and uploading/downloading of custom CSS file
  * Updated styling of VDE Tools panel
* Refactored various components to an analogous jQuery widget:
  * Refactored components:
    * Category navigation
    * Products management and gallery
    * Send to friend
    * Sales components, including orders and returns
    * Retrieve shipping rates and add/remove coupon in shopping cart
    * Customer address and address book
    * Customer wishlist
    * "Contact Us" form
    * CAPTCHA
    * Weee
  * New tabs widget is used instead of `Varien.Tabs`
  * Refactored `Varien.dateRangeDate` and `Varien.FileElement`
  * Replaced `$.mage.constants` with jQuery UI `$.ui.keyCode` for keyboard key codes
* Refactored configurable attribute, category parent and attribute set selectors to use suggest widget
* Bug fixes:
  * Improvements and bug fixes in new backend theme
  * Image, categories attributes and virtual/downloadable fields are displayed on Update Attributes page, where they shouldn't be present
  * Undefined config property in `reloadOptionLabels()` function in `configurable.js` (Chrome)
  * Impossible to edit existing customer/product tax class
  * Incorrect format of customer's "Date of Birth"
  * Theme preview images are absent in VDE
  * Search by backslash doesn't work for Categories field on product creation page
  * Impossible to assign a category to a product, if category name contains HTML tag
  * Incorrect URL generated for logo image

2.0.0.0-dev41
=============
* All-new look & feel of backend UI -- "Magento 2 backend" theme
  * This theme includes "Magento User Interface Library" -- a set of reusable CSS-classes, icons and fonts
* Theme editing features (in backend UI):
  * Ability to view static resources, such as CSS and JavaScript files, which are inherited by virtual themes from physical themes and application, and library
  * Ability to upload and edit custom CSS/JavaScript code assigned to a particular virtual theme
  * Ability to manage image and font assets for virtual themes
  * The uploaded or edited theme resources are used in page generation
  * Ability to rename virtual themes
  * Physical themes are read-only
* Visual design editor:
  * Ability to enter a "Design Mode" directly from the list of "My Customizations" in "Design Gallery"
  * Updated styling of theme selector and VDE toolbars
* Added functional limitations (managed through configuration files):
  * Ability to limit maximum number of store views in the system
  * Ability to limit maximum number of admin user records in the system
* Introduced mechanism of early discovery of memory leaks in integration tests:
  * Added ability to integration testing framework to detect usage of memory and estimate memory leaks using OS tools outside of PHP process
  * Also ability to set memory usage threshold which would deliberately trigger error, if integration tests reach it
* Refactoring in integration tests:
  * Broke down `Magento_Test_Bootstrap` into smaller testable classes
  * Minimized amount of logic in `bootstrap.php` of integration tests
  * Factored out memory utility functions from memory integration tests into a separate helper
  * Removed hard-coding of the default setting values from `Magento_Test_Bootstrap` in favor of requiring some crucial settings
  * Fixed integration tests dependency on `app/etc/local.xml`, changes in which were involved into the sandbox hash calculation `dev/tests/integration/tmp/sandbox-<db_vendor>-<hash>`
* Improvements in JavaScript widget "Suggest" (`pub/lib/mage/backend/suggest.js`):
  * Added ability to set callback for "item selection"
  * Added ability to provide a template in widget options
  * Implemented "multiple suggestions" ability directly in this widget and removed the "multisuggest" widget
* Converted several grids in backend from PHP implementation to declarations in layout
* Other various improvements:
  * Factored out logic of handling theme images from `Mage_Core_Model_Theme` into `Mage_Core_Model_Theme_Image`
  * Ability to filter file extensions in uploader component
  * Publication of resources linked in CSS-files will only log error instead of crashing page generation process
* Bug fixes:
  * Fixed several memory leaks in different places, related with dispatching controller actions multiple times in integration tests and with excessive reference to `Mage_Core_Model_App` object
  * Fixed integration test in `Mage_Install` module that verifies encryption key length
  * Fixed DHL shipping carrier declaration in config that caused inability to use it with shopping cart price rules
  * Fixed issues in generating of configurable product variations when the button "Generate" is invoked second time
  * Fixed an error that caused inability to create a theme in Windows environment in developer mode
  * Fixed various errors in JavaScript tests for visual design editor
  * Fixed broken "Edit" link on backend product management page

2.0.0.0-dev40
=============
* Implemented ability to customize all the main directory paths for the application, i.e. locations of `var`, `etc`, `media` and other directories
* Implemented ability to pass application configuration data from the environment
* Magento Web API changes:
  * Added SOAP V2 API coverage from Magento 1.x
  * Improved integration testing framework to develop Web API tests. Covered SOAP V2 API with positive integration tests.
  * Changed `Mage_Webapi` module front name from `api` to `webapi`
* Improvements for product creation UI:
  * Implemented AJAX suggestions popup and categories tree popup for convenient assignment of categories
  * Moved selection of Bundle and Grouped sub-products to the "General" tab, implemented popup grids for them
  * Made "Weight" checkbox to be selected by default for a Configurable product
* Implemented integration test to measure and control PHP memory leak on application reinitialization
* Changed format of configuration files for static tests that search for obsolete Magento 1.x code
* Bug fixes:
  * Fixed Web API WSDL incompatibility with C# and Java
  * Fixed issue, that Magento duplicated custom options field for a product, if creating them via a multi-call to API
  * Fixed `shoppingCartPaymentList` method in Web API, which was throwing exception "Object has no 'code' property"
  * Fixed invalid Wishlist link and several invalid links in Checkout on frontend store view
  * Made Stock Status in 'quantity_and_stock_status' attribute editable again for a Configurable product
  * Fixed issue, that it was not possible to save Customer after first save, because "Date Of Birth" format was reported by Magento to be incorrect
  * Fixed fatal error in Code Sniffer exemplar test
  * Fixed wrong failures of `Varien_Db_Adapter_Pdo_MysqlTest::testWaitTimeout()` integration test in developer mode
  * Fixed issue, that mass-action column in backend grids moved to another position in single store mode

2.0.0.0-dev39
=============
* Visual design editor improvements:
  * VDE changes can be saved to DB for current store and theme. Layout updates composed by VDE are combined into one record
  * Introduced temporary layout changes which should store non-applied modifications made during VDE functioning. Added new column `updated_at` to `core_layout_update` table for this, added observers and cron jobs to clean outdated layout updates
  * Added `vde/` prefix to all links inside VDE frame
  * Disabled caching (layout, blocks HTML, etc) in Design mode
  * Reviewed and improved "Quit" action to properly cleanup session, cache and cookies
  * Visual enhancements added when block is being dragged (block display, highlighting, cursor shape)
  * Added ability to set placeholder for a draggable block in VDE canvas
  * Fixed sorting of items within container
  * Improved logic of VDE canvas iframe sizing according to window size to have one scroll bar and static toolbar at the bottom of the page
* Improved themes management:
  * New separate tab on theme edit page which allows to view and download CSS files used on frontend. Files are divided to framework files, library files and theme files
  * Added an ability to upload and store custom CSS file which can be applied on frontend
  * Improved renaming of virtual themes, restricted modifying of physical themes
* Implemented changes in product creation process in admin interface:
  * Added "Variations" block with configurable product attributes in "General" tab. With this block all final prices of configurable product variations can be easily added and configured in one place. Easy sorting mechanism helps understand the order of applying price modifications. "Variations" block can be reloaded itself without reloading all product creation page. All product variations are being created automatically with saving the parent configurable product
  * Improved image management control. Multiple image control is placed in General tab. It provides easier upload and basic management of product's images than image gallery does.
  * Changed Save button on product edit page. Save button is implemented as a split-button with the following options: "Save & New", "Save & Duplicate", "Save & Close"
* Changed representation of a configurable product's image on frontend to use product's variation image instead of parent product's one
* Implemented js-plugin `mage` to give an ability to extend Magento js-code and modify initializing parameters during the runtime. Replaced instantiation of `form` and `validation` instances with `mage` widget
* Implemented autocomplete js-component on backend based on jQuery-ui
* Refactored frontend design theme to use jQuery library instead of Prototype for the following frontend components:
  * Varien Product class – class handles product price calculations on the client side as product price options are changed: `Product.Config`, `Product.Zoom`, `Product.Super`, `Product.OptionsPrice`
  * `RegionUpdater` & `ZipUpdater` classes – classes handle dynamically changing State/Province field from drop down to text field depending on selected country. They also handle "required" setting for State/Province and Zip/Postal Code fields.
  * `Varien.searchForm` – class handles quick search autocomplete functionality
* `VarienForm` class is deprecated
* Improved floating toolbar in backend
* Refactored the following grids in backend to make them configurable through layout, rather than hard-coded: `Mage_Adminhtml_Block_Newsletter_Queue_Grid`, `Mage_Adminhtml_Block_Report_Refresh_Statistics`
* Dependency injection improvements:
  * Added ability to generate proxy and factory classes on-the-fly for use with DI implementations. Generators can be managed in DI configuration
  * Implemented tools (shell scripts) that allow generating skeletons of factory and proxy classes for use with DI implementations
  * Added ability to set preferences to object manager and specify them through configuration
* Refactored the following modules to utilize `Magento_Filesystem` library instead of using built-in PHP core functions directly: `Mage_Adminhtml`, `Mage_Backend`, `Mage_Backup`, `Mage_Captcha`, `Magento_Catalog`, `Mage_Cms`, `Mage_Connect`, `Mage_Core`, `Mage_Install`
* Bug fixes:
  * Fixed bug with incorrect order processing in `Mage_Authorizenet_Model_Directpost`
  * Fixed bug with unnecessary "loading" image on Category field during product editing in backend
  * Fixed bug in `Mage_Adminhtml_CustomerController` with error message during subscription to newsletter
  * Fixed bug in custom option template with incorrect import of custom option during product creation
  * Fixed JavaScript bug with image uploader control on product edit page on backend in IE9
  * Fixed bug with incorrect CSS directives in `app/code/core/Mage/Adminhtml/view/adminhtml/catalog.xml`
  * Replaced usages of Validation Prototype class with jQuery analog in modules `Manage Attributes Sets`, `Reports` and `Order`
  * Fixed bug with Javascript errors in "accordion" tabs and incorrect tab content during product creation.
  * Fixed XSS vulnerability in configurable product on backend product page
  * Fixed inability to update values in PayPal system configuration
  * Fixed "The command line is too long" error triggered by static code analysis CLI tools when the lists become too large
  * Fixed inability to create shipping label for DHL caused by wrong logo image path
  * Fixed inactive navigation menu item of "Import/Export Tax Rates" page

2.0.0.0-dev38
=============
* Changed application initialization procedure
  * Application can be started with specific initial configuration data. `Mage_Core_Model_Config::loadBase()` merges this configuration with the highest priority
  * `Mage` class is no longer responsible for application installation status. `Mage_Core_Model_App` has this responsibility (`Mage_Core_Model_App::isInstalled()`)
* Implemented new library component `Magento_Filesystem` for working with file system
  * New component has more abstract layer of interaction with file system, better path isolation
  * Introduced interface Magento_Filesystem_AdapterInterface for file operations. Added concrete implementation in `Magento_Filesystem_Adapter_Local`
  * Introduced interface Magento_Filesystem_StreamInterface for stream operations with content. Added concrete implementation in `Magento_Filesystem_Stream_Local`
  * Added special class `Magento_Filesystem_Stream_Mode` to set parameters of stream on opening (read-only, write-only etc.)
* Added an ability to skip some service functions for lighter launch of application in `app/bootstrap.php`
* Improved batch tool for launching automated tests. Tool has an ability to run specified test types. Tool was moved from `dev/tools/batch_tests` to `dev/tools/tests.php`
* Improved integration test Mage_Adminhtml_DashboardControllerTest to skip test case when Google service is unavailable
* Improved url building process in new jQuery form widget to have it more secure
* Removed obsolete `@group module::<Namespace_Module>` annotation from integration tests, restricted its further usage
* Updated jQuery library used in application, used unified file name instead of version-based one
* Relocated XSD files for System Configuration, Menu Configuration, ACL Configuration, ACL Configuration for WebApi, Theme Configuration, View Configuration, Validator Configuration to `etc` subfolders of corresponding modules
* Bug fixes
  * Fixed bug with placing order in backend using Authorize.Net Direct Post payment method
  * Changed `Mage_Core_Model_Url` to fix bug with incorrect links on frontend (My Wishlist, Go to Shopping Cart, Continue button at first checkout step)
  * Fixed several bugs after converting backend grids to layout declaration. Changes were made in EAV Attributes, Design, Newsletter, Backup modules
  * Fixed incorrect current working directory behavior on application isolation in tests

2.0.0.0-dev37
=============
* Refactored a variety of grids in backend (admin) to make them configurable through layout, rather than hard-coded. The following classes were affected (converted): `Mage_User_Block_User_Grid`, `Mage_User_Block_Role_Grid`, `Mage_Adminhtml_Block_System_Design_Grid`, `Mage_Adminhtml_Block_Catalog_Product_Attribute_Set_Grid`, `Mage_Adminhtml_Block_Newsletter_Problem_Grid`, `Mage_Adminhtml_Block_Backup_Grid`, `Mage_Adminhtml_Block_Tax_Rate_Grid`, `Mage_Adminhtml_Block_System_Store_Grid`, `Mage_Adminhtml_Block_System_Email_Template_Grid`, `Mage_Adminhtml_Block_Sitemap_Grid`, `Mage_Adminhtml_Block_Catalog_Search_Grid`, `Mage_Adminhtml_Block_Urlrewrite_Grid`, `Mage_Adminhtml_Block_System_Variable_Grid`, `Mage_Adminhtml_Block_Report_Review_Customer_Grid`, `Mage_Adminhtml_Block_Report_Review_Product_Grid`
* Modified behavior of configuration merging. Each config file can be separately validated against DOM schema.
* Moved `Mage_Adminhtml_Utility_Controller` to `Backend` and changed all child classes
* Changes in Profiler system:
  * Created separate component for handling Profiler Driver selection logic
  * Extended `Magento_Profiler::start()` calls with tags as second argument
* Bug fix - Added additional validation into `Mage_Adminhtml_Catalog_CategoryController` to prevent saving new category with any id using firebug

2.0.0.0-dev36
=============
* Visual design editor refactored
  * VDE controls and VDE actions moved to backend area
  * Added IFRAME that allows to navigate through frontend pages in Navigation Mode and to modify blocks position in Design Mode
  * Inline JavaScript code is disabled in Design Mode
  * Store selection is performed on saving instead of reviewing the theme. List of all available stores is shown during assigning the theme to a store
  * `System -> Design -> Editor` page divided into two tabs:
    * "Available Themes" tab contains all available themes
    * "My Customizations" tab contains themes customized by the store administrator and consists of area with themes assigned to stores and area with unassigned themes
  * Added `vde` area code and `Mage_DesignEditor_Controller_Varien_Router_Standard` to handle requests from design editor
  * Added ability to use custom layout instance in controllers to use specific layout, when design editor is launched
* JavaScript updates
  * Replaced `varienTabs` class with an analogous jQuery widget
  * Displaying of loader during AJAX requests became optional
* Removed `dev/api-tests` directory added by mistake
* Bug fixes
  * Impossible to login to backend with APC enabled. Added call of `session_write_close()` in the session model destructor
  * Unnecessary regions shown when no country is selected in `System -> Sales -> Shipping Settings -> Origin`
  * Fixed various bugs caused by virtual themes implementation and other themes improvements

2.0.0.0-dev35
=============
* Enhancements of System Configuration:
  * Introduced new items that can be configured in the similar to Magento 1.x way, using xml files: nested groups in System Configuration form, group dependencies, intersected dependencies
  * Enhanced handling of field dependencies, required fields functionality
  * Changed Configuration structure to be represented as an object model
  * Improved performance of configuration rendering
* Implemented new API in `Mage_Webapi` module
  * Removed `Mage_Api` and `Mage_Api2` modules as obsolete API implementation
  * Added support of REST and SOAP 1.2 [WS-I 2.0](http://ws-i.org/Profiles/BasicProfile-2.0-2010-11-09.html) APIs
  * Introduced versioning per API resource. The application will support old version(s) of API after upgrading to not make old API requests fail
  * Unified implementation for all API types
  * Significantly simplified coverage of new API resources
  * Added two-legged `OAuth` 1.0 for REST authentication
  * Added WS-Security for SOAP authentication
  * Added automatic generation of REST routes and SOAP WSDL on the basis of API class interface and annotations
  * Introduced generation of API reference from annotated WSDL (for SOAP API)
* Introduced service layer. Business logic should be implemented once on service layer and could be utilized from different types of controller (e.g., general or API)
  * Business logic is implemented on service layer to be utilized from different types of controller (e.g., general or API)
  * Implemented abstract service layer class - `Mage_Core_Service_ServiceAbstract`
  * Implemented concrete service layers for customers, orders and quotes. Appropriate duplicate logic has been eliminated from controllers and API
* Improved validation approach:
  * Added support of describing validation rules in a module's configuration file - `validation.xml` in the module's `etc` directory
  * Added `Mage_Core_Model_Validator_Factory`
  * Added new validators to Magento Validator library
  * Added `Magento_Translate_Adapter` as a translator for the validators
  * New approach is utilized in `Mage_Customer`, `Mage_Eav` and `Mage_Webapi` modules
* Added profiling of DB and cache requests
* Minor Improvements:
  * Added an ability to choose the image for logo and upload it from backend web-interface
  * Added notification in backend in case of product SKU change
* Bug fixes:
  * Fixed bug in `Mage_Adminhtml_Sales_Order_CreditmemoController` that changed item’s stock status after each comment
  * Removed `Debug` section in `System -> Configuration -> Advanced -> Developer` for default configuration scope
  * Fixed bug in `Mage_Tax_Model_Resource_Calculation` that prevented placing order with two tax rules having the same rate
  * Removed `Url Options` section in `System -> Configuration -> General -> Web` for website and store configuration scope
  * Changed backend template for UPS shipping provider to fix translation issue
* Fixed security issue - set `CURLOPT_SSL_VERIFYPEER` to `true` by default in cUrl calls
* Added `Zend/Escaper`, `Zend/I18`, `Zend/Validator` ZF2 libraries
* Updated `Zend/Server` and `Zend/Soap` libraries to ZF2 versions

2.0.0.0-dev34
=============
* Test Framework:
  * Created `CodingStandard_ToolInterface` - new interface for coding standard static tests. Refactored `CodeSniffer` class as an implementation of the interface
  * Fixed DB isolation in integration tests after themes refactoring
  * Minor test fixes
* Changes in product creation process
  * Added ability to change product type "on the fly" depending on selected options
  * Added ability of new category creation on "General" tab
  * Moved "Associated Products" tab contents to collapsible block on "General" tab for configurable products
  * Visual enhancement made for base image and Virtual/Downloadable checkbox
  * Refactored implementation of associated products in backend (admin) to make them configurable through grid layout, rather than hard-coded.
  * Enhanced product variation matrix for configurable products
  * Changed "Apply To" feature in product attributes management due to changes in product creation process
* Fixed XSS vulnerabilities in `Mage_Wishlist_IndexController`, `Mage_Adminhtml_Block_Review_Edit_Form`, `Magento_Catalog_Product_CompareController`
* Bug fixes
  * Fixed error on `Catalog -> Google Content -> Manage Items page`
  * Fixed bug with "Update Attributes" mass action for products on backend caused by setting incorrect inheritance of `Mage_Adminhtml_Helper_Catalog_Product_Edit_Action_Attribute`
  * Added additional validation of "quantity" field to fix issues with inventory during product saving
  * Added additional validation into `EAV` models to forbid creation of two products with the same unique multi-select attribute

2.0.0.0-dev33
=============
* Improved Themes functionality to meet the following requirements:
  * Magento instance doesn’t crash in case there’re no themes at all
  * Features like selection of themes in system configuration, custom theme selection in Custom Design, CMS pages, Products and Categories can work without themes. They use base view files only.
  * Virtual themes work in the same way as the non-virtual (which are present in file system) though they additionally have inheritance property. Changes were made in theme switcher, in fallback mechanism, in widgets etc.
  * Non-virtual themes are being added to DB during installation
  * Application framework uses theme id as identifier instead of theme code
* Refactored a variety of report grids in backend (admin) to make them configurable through layout, rather than hard-coded.
* Removed obsolete modules:
  * `Mage_XmlConnect`
  * `Mage_Dataflow`
* Significantly changed Logging subsystem:
  * `Mage_Core_Model_Logger` class is responsible for logging
  * Changes are made to comply with DI paradigm
  * Custom logger in `Mage_Backend_Menu` subsystem is removed due to usage of regular one
* Changes made in autoload process
  * Fixed autoload to prevent `class_exists()` from causing fatal error
  * The `Magento_Autoload` library was divided into 2 classes: `Magento_Autoload_IncludePath` is responsible for loading from include path, `Magento_Autoload_ClassMap` from a class map. Stacked "class map" loader on top of "include path" loader in application bootstrap.
* Implemented new jQuery form widget. Its responsibility is to prepare form for submission (change form attributes if needed)
  * Replaced usage of different instances of `varienForm` with a new form widget (`productForm`, `categoryForm`, instances of type "onclick declaration", "as child component", "instantiation only")
  * Replaced prototype validation with jQuery analog
  * Additionally implemented form widget in different modules (CMS, Customer, Backend, Sitemap, DesignEditor, Tags, SystemEmail, Newsletters, ImportExport, Connect, Authorize.net)
* Minor improvements
  * Fixed css styles for validation messages in different parts of the system
  * Removed usage of `jquery-ui-1.8.21.custom.css`
  * Updated versions of jQuery and jQuery-UI on backend
  * Updated Magento trademark and copyright labels at the bottom of pages: changed legal entity name to X.Commerce, Inc, made translation engine pick them up
  * Improvements made in indexers to stabilize tests. Fixed wrong initialization order of indexers that sometimes caused failure of reindexing all at once
* Bugfixes:
  * Set correct order's data change state during voiding the order
  * Set translator to pick up status labels in drop-down in "Shopping cart price rule" admin grid
  * Fixed an issue in console installer that initialized application in such a way, that it could not load certain event area.
  * Fixed incorrect loader image source in backend during new tax rule creation
  * Fixed JS error in IE with creating products via floating toolbar
  * Fixed image save url during uploading product's images
  * Added permission check for editing shipping and billing addresses during viewing the order
  * Changed saving of order comments from backend. Comment is saved even without status change
  * Added additional validation into `quickCreateAction` of `Mage_Adminhtml_Catalog_ProductController` to prevent saving new product with any id using firebug
  * Fixed JS errors in Authorize.net Direct Post submodule
  * Fixed JS errors in split button on creating product
  * Fixed errors in poll's list template in backend

2.0.0.0-dev32
=============
* Improved product edit workflow:
  * Introduced Category Assignment control on "General" tab
  * Eliminated attribute preselection screen
  * Base image assignment control moved to "General" tab
  * Base inventory attributes controls displayed on "General" tab. Values of the attributes are synchronized between "General" and "Inventory" tabs
* Improved static code analysis tests to verify existence of paths specified in white/black lists
* Reduced memory usage by integration tests by automatic cleaning properties of test classes
* Added migration tool `dev/tools/migration/themes_view.php` for replacing old `{{skin}}` with new `{{view}}` placeholders
* Changed handling of exceptions, produced by non-existing view files, to not break whole page
* Removed empty locale files
* Bug fixes:
  * Page with tracking information absent, if shipping labels integration is used
  * Category is not displayed on frontend with "Use Flat Catalog Category" option enabled
  * Exception on "Coupons Usage Report" page after upgrade from Magento 1.x
  * Exception on "Most Viewed Products" page after upgrade from Magento 1.x
  * Quick search produces error, if searching for a product with an attribute that has "Use In Search Results Layered Navigation" option set to "Yes"
  * Exception on "Add/Edit Customer" page when Magento profiler with html output is enabled
  * Can't duplicate downloadable product with sample file attached
  * Product Type dropdown on "Add Product" page doesn't work in IE9
  * Various issues related to adding/editing product

2.0.0.0-dev31
=============
* Themes:
  * Eliminated "skins" as a concept. Skins and themes are consolidated into one entity and now called just "themes"
  * New themes out of the box are named by their distinctive characteristic (thus, "default" is renamed to "demo")
  * Revised logic of handling "virtual" (which are present in database registry only, but not in the file system) VS "physical" themes
* Dependency injection:
  * Reduced memory leaks of integration tests caused by introduction of object manager
  * Added compiler for dependency injection definitions and ability to run Magento application with the compiled definitions
* `Mage_Adminhtml` breakdown:
  * Implemented XML-schema for system configuration form declaration files (`etc/system.xml` in each module), refactored them to comply with schema and relocated to `etc/adminhtml/system.xml`
  * Removed remnants of `Mage_Admin` module (replaced with `Mage_Backend` and others)
  * Removed multiple obsolete models in `Mage_Adminhtml` module -- replaced with more generic classes in `Mage_Backend` (less classes overall)
* Replaced `Magento_Test_TestCase_ObjectManagerAbstract` in unit testing framework by a helper in test suite
* Made the PHP coding standard test (`Php_LiveCodeTest`) treat white/black lists as `glob()` patterns and verify correctness of the actual patterns
* Consolidated `upload_max_filesize` logic into one helper
* Bug fixes:
  * Fatal error on Product Tags and Customers Tagged Product (on product editing page in backend)
  * Trailing space in date caused by new "date picker" JavaScript component
  * Impossibility to add product to an order in backend in IE8
  * Not picking a template on customer "Shopping Cart" page at the backend
  * "Use Default" checkbox is checked again after saving multiselect attribute config if option does not contain value
  * "Single Store Mode" UI fixes
  * Runtime error when previewing transactional email template
  * Incorrect redirect after applying filter in grids
  * Various asynchronous placement of profiler keys
  * Various fixes in Taxes backend UI
  * Various fixes in translation literals

2.0.0.0-dev30
=============
* Framework changes
  * Added dependency injection of framework capability
    * Adopted Zend\Di component of Zend Framework 2 library
    * Implemented object manager in Magento application
    * Refactored multiple base classes to dependency injection principle (dependencies are declared in constructor)
  * Themes/View
    * Implemented storing themes registry in database, basic CRUD of themes, automatic registration of themes in database from file system out of the box
    * Renamed `Mage_Core_Model_Layout_Update` into `Mage_Core_Model_Layout_Merge`, the former becomes an entity domain model. Similar changes with `Mage_Core_Model_Resource_Layout` -> `Mage_Core_Model_Resource_Layout_Update`, `Mage_Core_Model_Layout_Data` -> `Mage_Core_Model_Layout_Update`
* Performance tests
  * Improved indexers running script `dev/shell/indexer.php` to return appropriate exit code upon success/failure
  * Implemented running the same performance scenario file with different parameters
  * Slightly refactored framework class `Magento_Performance_Testsuite_Optimizer` for better visibility of algorithm
* Visual design editor
  * Added ability to remove elements in editor UI
  * Revised history of changes VDE toolbar and algorithm of "compacting" operations (moving, removing elements) as a layout update XML
  * Added selection of themes to VDE launcher page
* Refactored JavaScript of some UI elements to jQuery:
  * "Simple" and "configurable" product view pages
  * "Create Account" page
  * "Shopping Cart" page
  * CAPTCHA
  * Newsletter subscription
* Tax management UX improvements
  * Split Basic and Advanced Settings for Tax Rule Management UI
  * Moved the Import/Export functionality to Tax Rate page
  * Moved Tax menu to System from Sales
* Implemented the editable multiselect JavaScript component
* Added mentioning sitemap in `robots.txt` after generation
* Removed creation of DB backup in integration testing framework
* Fixed logic of order of loading ACL resources in backend
* Fixed JavaScript error during installation when one of files in `pub/media` is not writable
* Fixed structure of legacy test fixtures that allowed ambiguous keys in declaration
* Fixed inability to restore admin password when CAPTCHA is enabled
* Various minor UX fixes (labels, buttons, redirects, etc...)
* GitHub requests:
  * [#59](https://github.com/magento/magento2/issues/59) -- implemented handling of unexpected situations in admin/dashboard/tunnel action
  * [#66](https://github.com/magento/magento2/issues/66)
    * refactored ImageMagick adapter unit test to avoid system operation
    * simplified unit testing framework -- removed unused classes, simplified handling logic of directory `dev/tests/unit/tmp` and removed it from VCS
  * [#73](https://github.com/magento/magento2/pull/73), [#74](https://github.com/magento/magento2/pull/74) -- fixes in docblock tags
  * [#75](https://github.com/magento/magento2/pull/75), [#96](https://github.com/magento/magento2/pull/96) -- fixed translation module contexts in a few places
  * [#80](https://github.com/magento/magento2/issues/80) -- fixed some runtime errors in import/export module
  * [#81](https://github.com/magento/magento2/issues/81) -- removed usage of "remove" directive in places where it is overridden by setting root template anyway
  * [#87](https://github.com/magento/magento2/issues/87) -- changed paths of files to include from relative into absolute in `dev/shell/indexer.php` and `log.php`
  * [#88](https://github.com/magento/magento2/issues/88) -- provided comments for values that can be configured in `app/etc/local.xml` file
  * [#90](https://github.com/magento/magento2/issues/90) -- slightly optimized logic of implementation of loading configurable product attributes

2.0.0.0-dev29
=============
* Implemented and verified ability to upgrade DB from CE 1.7 (EE 1.12) to 2.x
* Replaced calendar UI component with jQuery calendar
* Restored back the public access to `pub/cron.php` entry point (in the previous patch it was denied by mistake)
* Fixed typo in label of "Catalog Search" index in UI

2.0.0.0-dev28
=============
* Introduced block arguments to the layout syntax:
  * Introduced the "object" block argument type to specify a grid data source
  * Introduced the "options" block argument type to accommodate key-value pairs
  * Introduced the "URL" block argument type to represent a URL with parameters
  * Introduced block argument updaters for the block arguments, which allow to customize the original grid arguments
  * Implemented extraction of translatable strings from block arguments in layout by the Translation Tool
* Declared the Customer Wishlist and Sales Order grids through the layout instead of the PHP classes
* Implemented the block `Mage_Backend_Block_Widget_Grid_Massaction` to encapsulate grid mass actions
* Moved grid columns and mass action management from the base grid `Mage_Backend_Block_Widget_Grid` to the `Mage_Backend_Block_Widget_Grid_Extended`
* Introduced the column set block `Mage_Backend_Block_Widget_Grid_ColumnSet` responsible for grouping columns in a grid
* Updated the grid rendering template to render a column set instead of rendering columns
* Eliminated dependency between the grid column block and parent blocks
* Denied the public access to the `pub/cron.php`
* Fixes:
  * Fixed the broken Billing Agreement View page
  * Fixed absence of the Default and Minimal attribute sets in the Manage Attribute Sets grid, if the total number of records is 22 and the view of 20 rows per page is chosen
  * Fixed typos on the Sales Order page
  * Fixed typos on the Sales Order's Packing Slips printed to PDF
  * Fixed preserving selected rows after searching in the Sales Order grid
  * Fixed "column not found" SQL error while sorting by the "Product Name" in the Catalog Pending Reviews grid

2.0.0.0-dev27
=============
* Removed unused `Mage_DesignEditor_Model_History_Compact_Diff` class
* Fixes:
  * Incorrect title for Manage Products page
  * 'Element with ID 'wishlist_column_qty' already exists.' error on Manage Shopping Cart page
  * Incorrect redirect on "Print Shipping Labels" action, when shipment without shipping label selected
  * Error message is displayed twice, when restoring admin password with captcha enabled
  * Impossible to retrieve admin password, when captcha is enabled

2.0.0.0-dev26
=============
* Performance Testing Framework improvements:
  * Added ability to specify fixtures per scenario
  * Implemented Magento application cleanup between scenarios
  * Implemented support of PHP scenarios. The framework distinguishes type of the scenario by its extension: `jmx` or `php`
  * Added ability to skip warm-up for a certain scenario
  * JMeter scenarios are run with `jmeter` command instead of `java -jar ApacheJmeter.jar`. It's impossible to specify path to JMeter tool now, it should be accessible from command line as `jmeter`
* Implemented fixture for Performance Tests with 80k products distributed among 200 categories
* Tax rule management UI simplified:
  * Added `Jeditable` jQuery library
  * Added multiselect fields for customer tax class, product tax class and tax rate
  * Added ability to add/edit Tax Rate directly from Tax Rule page
* Simplified product creation workflow:
  * Added product types dropdown to "Add Product" button. Default attribute set is used for product creation
  * "Add Product" button opens form for Simple product with Default attribute set
  * Attribute set can be changed from product creation form
* Implemented auto-generation of product SKU and meta fields. The templates can be configured in `System -> Configuration -> Catalog -> Catalog -> Product Fields Auto-Generation`
* Added ability to unassign system attribute from an attribute set, if it's not "Minimal" one
* Specified UI IDs for base Backend elements. UI ID is represented as HTML "id" attribute intended to identify certain HTML element
* Refactored `Catalog_Model_Product_Indexer_Flat::matchEvent()` method - reduced cyclomatic complexity
* Updated DB structure to make possible to store Themes' and Widgets' layout updates
* Migration to jQuery:
  * Replaced Ajax, Dialog and Template mechanisms with jQuery analogs
  * Added jQuery loader for translation process
  * Migrated Inline-Translator to jQuery
* JavaScript improvements:
  * Implemented `editTrigger` jQuery widget intended to display "Edit" button for elements it is attached to
* Fixes:
  * Incorrect title for "Currency Symbols" page on Backend
  * References to website, store and store view aren't displayed on Backend, if Single Store mode is disabled
  * "Store" column and dropdown are displayed on `System -> Import/Export -> DataFlow-Profiles` page, when Single Store mode is enabled
  * Options are absent for `'tax_class_id'` product attribute
  * No exception/error message is produced, when attempting to commit/rollback asymmetric DB transaction
  * Links are not copied during downloadable product duplication
  * PayPal tab is absent in `System -> Configuration -> Sales` section
  * "Edit" link in wishlist opens Product View page instead of "Configure Product" page
  * Default value for a product attribute is not saved
  * Escaped HTML blocks with `Mage_Core_Helper_Data::jsonEncode`, where necessary
  * Impossible to add new Dataflow profile
  * Impossible to specify default option for new product attribute with "dropdown" type
  * Unable to send the email when creating new invoice/shipment/credit memo
  * "Segmentation Fault" in Integration tests
* GitHub requests:
  * [#36](https://github.com/magento/magento2/pull/36) -- added ability to force set of "Include Tax" option for catalog prices
  * [#63](https://github.com/magento/magento2/pull/63) -- removed obsolete "args" node in event subscribers
  * [#64](https://github.com/magento/magento2/pull/64) -- fixed EAV text attribute validation for "0" value
  * [#72](https://github.com/magento/magento2/pull/72) -- fixed collecting shipping totals for case, when previous invoice value is 0

2.0.0.0-dev25
=============
* Refactoring Magento 2 to use jQuery instead of Prototype:
  * Implemented simple lazy-loading functionality
  * Converted decorator mechanism to jQuery
  * Moved Installation process to jQuery
  * Moved Home, Category and Simple Product View pages to jQuery
  * Moved all frontend libraries from `pub/js` directory to `pub/lib`
* Improved Javascript unit tests to be consistent with other test frameworks in Magento
* Added Javascript code analysis tests to the static tests suite
* Added jQuery file uploader for admin backend, cleaned out old deprecated uploaders
* Implemented fixture of 100k orders for the performance tests
* Fixes
  * Admin menu elements order differs for a cached page and non-cached one
  * Typos in System > Configuration > General Tab
  * Wrong elements positions on "View Order" page
  * Impossible to configure checkout on store scope
  * Warning message in `system.log` when using GD2 image adapter
  * "Preview" link is absent for managing CMS Pages in single store mode
  * "Promotions" tab is missing on Configuration page
  * Wrong format of performance tests config

2.0.0.0-dev24
=============
* Implemented the option to enable the single store mode in the system configuration, which simplifies the back-end GUI:
  * Hiding scope labels from the system configuration
  * Hiding the scope switcher from the CMS management pages and the system configuration
  * Hiding scope related fields from the system configuration
  * Hiding scope related columns and fields from the sales pages (order, invoice, shipment pages)
  * Hiding scope related fields from the promotions
  * Hiding scope related fields from the catalog pages
  * Hiding scope related columns and fields from the customers management page
  * Hiding scope related columns and fields from the customer and customer address attributes management pages
* Implemented the history management for the Visual Design Editor
* Implemented the user interface for themes management, which allows to list existing themes and add new ones
* Replaced all usages of the old JavaScript translations mechanism with the new jQuery one
* Refactored methods with high cyclomatic complexity
* Converted some surrogate integration tests into functional Selenium tests
* Converted some surrogate integration tests into unit tests
* Fixes:
  * Fixed inability to install application with a prefix defined for database tables
  * Fixed displaying fields with model name in the payment methods settings
  * Fixed performance degradation of the back-end menu rendering
  * Fixed absence of the success message upon newsletter template creation/deletion/queueing
  * Workaround for occasional segmentation fault in integration tests caused by `Mage_Core_Model_Resource_Setup_Migration`
* GitHub requests:
  * [#51](https://github.com/magento/magento2/issues/51) -- fixed managing of scope-specific values for Categories
  * [#56](https://github.com/magento/magento2/pull/56) -- removed excessive semicolon in the CSS file
  * [#60](https://github.com/magento/magento2/issues/60) -- fixed taking bind parameters into account in `Mage_Core_Model_Resource_Db_Collection_Abstract::getAllIds()`
  * [#61](https://github.com/magento/magento2/pull/61) -- relocated declaration of the "Google Checkout" payment method into `Mage_GoogleCheckout` module from `Mage_Sales`

2.0.0.0-dev23
=============
* Implemented encryption of the credit card name and expiration date for the payment method "Credit Card (saved)"
* Implemented console utility `dev/tools/migration/get_aliases_map.php`, which generates map file "M1 class alias" to "M2 class name"
* Implemented automatic data upgrades for replacing "M1 class aliases" to "M2 class names" in a database
* Implemented recursive `chmod` in the library class `Varien_Io_File`
* Improved verbosity of the library class `Magento_Shell`
* Migrated client-side translation mechanism to jQuery
* Performance tests:
  * Improved assertion for number of created orders for the checkout performance testing scenario
    * Reverted the feature of specifying PHP scenarios to be executed before and after a JMeter scenario
    * Implemented validation for the number of created orders as a part of the JMeter scenario
    * Implemented the "Admin Login" user activity as a separate file to be reused in the performance testing scenarios
  * Implemented fixture of 100k customers for the performance tests
  * Implemented fixture of 100k products for the performance tests
    * Enhanced module `Mage_ImportExport` in order to utilize it for the fixture implementation
  * Implemented back-end performance testing scenario, which covers Dashboard, Manage Products, Manage Customers pages
* Fixes:
  * Fixed Magento console installer to enable write permission recursively to the `var` directory
  * Fixed performance tests to enable write permission recursively to the `var` directory
  * Fixed integration test `Mage_Adminhtml_Model_System_Config_Source_Admin_PageTest::testToOptionArray` to not produce "Warning: DOMDocument::loadHTML(): htmlParseEntityRef: expecting ';' in Entity" in the developer mode
* GitHub requests:
  * [#43](https://github.com/magento/magento2/pull/43) -- implemented logging of executed setup files
  * [#44](https://github.com/magento/magento2/pull/44)
    * Implemented support of writing logs into wrappers (for example, `php://output`)
    * Enforced a log writer model to be an instance of `Zend_Log_Writer_Stream`
  * [#49](https://github.com/magento/magento2/pull/49)
    * Fixed sorting of totals according to "before" and "after" properties
    * Introduced `Magento_Data_Graph` library class and utilized it for finding cycles in "before" and "after" declarations
    * Implemented tests for totals sorting including the ambiguous cases

2.0.0.0-dev22
=============
* Fixes:
  * Fixed name, title, markup, styles at "Orders and Returns" homepage
  * Fixed displaying products in the shopping cart item block at the backend

2.0.0.0-dev21
=============
* Decoupled Tag module functionality from other modules
* Visual Design Editor:
  * Implemented tracking of user changes history and rendering the actions at VDE toolbar
  * Implemented compacting of user changes history. Compacting is done in order to save all the changes as a minimal layout update.
* Improvements:
  * Added Atlassian IDE Plugin configuration files to `.gitignore`
  * Relocated `add_to_cart`, `checkout` and `product_edit` performance scenarios from `samples` to the normal `testsuite` directory. These scenarios can be used for Magento performance testing.
  * Implemented verification of number of orders that were created during execution of `checkout` performance scenario
  * Removed usage of deprecated `PHPUnit_Extensions_OutputTestCase` class from unit tests
* Fixes:
  * Fixed MySQL DB adapter to always throw exception, if it was not able to connect to DB because of wrong configuration. So now the adapter's behavior is not dependent on `error_reporting` settings.
  * Added the missing closing tag to New Order email template
  * Fixed `Mage_ImportExport_Model_Import_Entity_CustomerComposite` integration test issues
  * Marked several integration tests in `Mage_Adminhtml_CustomerControllerTest` as incomplete, as the tested functionality was not MMDB-compliant
  * Fixed issue with unit tests failure, when there was a Zend Framework installed as PEAR package
  * Fixed `advanced_search` performance scenario to fail, if the searched product doesn't exist
  * Fixed issue with non-escaped latest message link in admin backend
* GitHub requests:
  * [#48](https://github.com/magento/magento2/pull/48) -- fixed usage of a collection at the place, where just a single object was needed

2.0.0.0-dev20
=============
* Refactored ACL functionality:
  * Implementation is not bound to backend area anymore and moved to `Mage_Core` module
  * Covered backwards-incompatible changes with additional migration tool (`dev/tools/migration/Acl`)
* Implemented "move" layout directive and slightly modified behavior of "remove"
* A failure in DB cleanup by integration testing framework is articulated more clearly by throwing `Magento_Exception`
* Fixed security vulnerability of exploiting Magento "cookie restriction" feature
* Fixed caching mechanism of loading modules declaration to not cause additional performance overhead
* Adjusted include path in unit tests to use the original include path at the end, rather than at the beginning

2.0.0.0-dev19
=============
* Improvements:
  * Implemented "multi-file" scheduled import/export of customers, deleted legacy implementation
  * Ability to import amendments to complex product data, such as custom options
  * Ability to cleanup database before installation using CLI script (`dev/shell/install.php`)
  * Customer export feature performance optimizations
  * Ability to control `robots.txt` via backend (System -> Config -> Design -> Search Engine Robots)
  * Ability to create custom URL rewrites for CMS-pages
* Product editing and attribute set changes:
  * Ability to copy custom options from one product to another
  * Ability to create/change attribute set during product creation/editing
  * Ability to define default values for all system attributes
  * New "Minimal" attribute set which has only required system attributes
* "Google Sitemap" feature changes:
  * The feature is renamed to "XML Sitemap"
  * Reference to a XML sitemap file will be automatically added to `robots.txt` upon update. Controlled by "System -> Config -> Design -> Search Engine Robots", enabled by default
  * Automatic switch to multiple "sitemaps" when size exceeds Google limits
  * Support of images in sitemap
* Removed "HTML Sitemap" feature as such (not the one known as "Google Sitemap")
* Fixes:
  * Map of listed products in XML sitemap will list product last modification date, rather than current date
  * Incorrect timestamp of export file
  * Addressed WSI-compliance issues in SOAP API (V2)
  * Fixed incompatibility of Downloader tool with PHP 5.3
  * Fixed inconsistent behavior of importing duplicated rows in CSV files
  * Fixed message about successful registration not appearing if customer has previously logged out on the shopping cart page
  * Fixed minor configuration issues for "Cache on Delivery Payment" method
  * Fixed wrong order status in some cases when it is placed using PayPal with "Authorization" action
  * Applied Zend framework security hotfix against XML external entity injection via XMLRPC API
  * Fixed inappropriate displaying of credit card credentials to admin user after "reorder" action with Authorize.net and PayPal payment methods involved

2.0.0.0-dev18
=============
* Refactored ACL for the backend
  * ACL resources
    * Strict configuration format, validated by XSD schema
    * ACL configuration relocation from `app/code/<pool>/<namespace>/<module>/etc/adminhtml.xml` to `app/code/<pool>/<namespace>/<module>/etc/adminhtml/acl.xml`
    * Renamed ACL resource identifiers according to the format `<namespace>_<module>::<resource>` throughout the system
      * Backend menu configuration requires to specify ACL resource identifier in the new format
      * Explicit declaration of ACL resources in `app/code/<pool>/<namespace>/<module>/etc/system.xml` instead of implicit relation by XPath
    * Migration tool `dev/tools/migration/acl.php` to convert ACL configuration from 1.x to 2.x
  * Declaration of ACL resource/role/rule loaders through the area configuration
    * Module `Mage_Backend` declares loader for ACL resources in backend area
    * Module `Mage_User` declares loaders for ACL roles and rules (relations between roles and resources) in backend area
  * Implemented integrity and legacy tests for ACL
* Fixed issues:
  * Losing qty and visibility information when importing products
  * Impossibility to reload captcha on backend
  * Temporary excluded from execution integration test `Mage_Review_Model_Resource_Review_Product_CollectionTest::testGetResultingIds()` and corresponding fixture script, which cause occasional `segmentation fault` (exit code 139)
* Refactored methods with high cyclomatic complexity:
  * `Mage_Adminhtml_Block_System_Store_Edit_Form::_prepareForm()`
  * `Mage_Adminhtml_Block_System_Config_Form::initForm()`
  * `Mage_Adminhtml_Block_System_Config_Form::initFields()`
* GitHub requests:
  * [#32](https://github.com/magento/magento2/pull/32) -- fixed declaration of localization CSV files
  * [#35](https://github.com/magento/magento2/issues/35) -- removed non-used `Mage_Core_Block_Flush` block
  * [#41](https://github.com/magento/magento2/pull/41) -- implemented ability to extends `app/etc/local.xml` by specifying additional config file via `MAGE_LOCAL_CONFIG` environment variable

2.0.0.0-dev17
=============
* Implemented Magento Validator library in order to have clear solid mechanism and formal rules of input data validation
* Moved translations to module directories, so that it is much more convenient to manage module resources
* Updated inline translation mechanism to support locales inheritance
* Implemented ability to navigate through pending reviews with Prev/Next buttons, no need to switch to grid and back
* Fixed issues:
  * Unable to use shell-installer after changes in Backend area routing process
  * Incorrect redirect after entering wrong captcha on the "Forgot your user name or password?" backend page
  * Translation is absent for several strings in Sales module `guest/form.phtml` template
  * Exception during installation process, when `var` directory is not empty
  * Node `modules` is merged to all modules' config XML-files, although it must be merged to `config.xml` only
* GitHub requests:
  * [#39](https://github.com/magento/magento2/pull/39) -- added `composer.json`, which was announced at previous update, but mistakenly omitted from publishing

2.0.0.0-dev16
=============
* Implemented inheritance of locales. Inheritance is declared in `app/locale/<locale_name>/config.xml`
* Moved declaration of modules from `app/etc/modules/<module>.xml` to `app/code/<pool>/<namespace>/<module>/config.xml`
* Implemented ability to match URLs in format `protocol://base_url/area/module/controller/action` (as opposite to only `module/controller/action`), utilized this feature in backend (admin) area
* Added product attribute set "Minimal Attributes", which consists of required system attributes only
* Improved customers import:
  * Implemented "Delete" behavior for importing customers, customer addresses and financial data
  * Implemented "Custom" behavior, which allows to specify behavior for each item directly from the imported file
* Updated performance tests:
  * Enabled Product View, Category View, Add to Cart, Quick Search and Advanced Search scenarios
  * Added ability to specify configuration parameters per scenario and refactored bootstrap of performance tests
* Implemented `mage.js` for base JavaScript initialization of the application
* Implemented new JS translation mechanism. JavaScript translations are loaded by locale code stored in cookies
* Implemented unit tests for JavaScript widgets in Visual Design Editor
* Added jQuery plugins: Cookie, Metadata, Validation, Head JS
* Fixed issues:
  * Impossible to add configurable product to the cart
  * Impossible to apply Shopping Cart Price Rule with any conditions to cart with simple and virtual product
  * Memory leak in email templates
  * Impossible to place order with Multiple Addresses using 3D Secure
  * Required product attributes are not exported
  * "Forgot Your Password" link on checkout page inactive after captcha reloading
  * Validation of "Number of Symbols" field in Captcha configuration doesn't work
  * Other small fixes
* GitHub requests:
  * [#37](https://github.com/magento/magento2/pull/37) -- fixed particular case of "HEADERS ALREADY SENT" error in WYSIWYG thumbnail
  * [#39](https://github.com/magento/magento2/pull/39) -- added `composer.json` (actually, doesn't come with this update due to a mistake in publishing process)
  * [#40](https://github.com/magento/magento2/pull/40) -- fixed generation of "secret key" in backend URLs to honor `_forward` in controllers

2.0.0.0-dev15
=============
* Refactored backend (admin) menu generation:
  * Menu is separated from `adminhtml.xml` files into `menu.xml` files
  * Rendering menu became responsibility of `Mage_Backend` instead of `Mage_Adminhtml` module
  * Implemented XML-Schema for `menu.xml`
  * Actions with menu items defined in schema: add, remove, move, update, change parent and position
* Refactored customers import feature. New ability to provide import data in 3 files: master file (key customer information) + address file (customer id + address info) + financial file (customer id + reward points & store credit)
* Optimized memory consumption in integration tests:
  * Found and eliminated memory leaks in `Mage_Core_Model_App_Area`, `Mage_Core_Model_Layout`
  * Manually unset objects from PHPUnit test case object in `tearDown()` in integration tests. Garbage collector didn't purge them because of these references
  * Disabled running `integrity` test suite by default in integration tests
* Improvements in visual design editor JavaScript:
  * eliminated dependency of code on HTML-literals, reduced code coupling between templates and JavaScript files
  * implemented blocking unwanted JavaScript activity in visual design editor mode
* Various fixes in UX, code stability, modularity
* GitHub requests:
  * [#23](https://github.com/magento/magento2/pull/23) -- added `Mage_Customer_Block_Account_Navigation::removeLink()`

2.0.0.0-dev14
=============
* Implemented locale translation inheritance
* Implemented new format for exporting customer data
* Added initial Javascript code for globalization and localization
* Added initial Javascript unit tests
* Implemented file signature for urls of static files - better CDN support
* Implemented optional tracking of changes in view files fallback - cached by default, tracked in developer mode
* Introduced `@magentoDbIsolation` annotation in integration tests - isolates DB modifications made by tests
* Started refactoring of Visual Design Editor Javascript architecture
* GitHub requests:
  * [#25](https://github.com/magento/magento2/issues/25) Removed unused `Mage_Core_Block_Abstract::getHelper()` method
* Fixed:
  * "$_FILES array is empty" messages in exception log upon installation
  * Long attribute table aliases, that were producing errors at DB with lower identifier limitation than MySQL
  * Watermark opacity function did not work with ImageMagick
  * `Magento_Test_TestCase_ControllerAbstract::assertRedirect` was used in a wrong way
  * Inability to reorder a downloadable product
  * ACL tables aliases interference with other table aliases
* Several tests are made incomplete temporary, appropriate bugs to be fixed in the nearest future

2.0.0.0-dev13
=============
* Fixed various crashes of visual design editor
* Fixed some layouts that caused visual design editor toolbar disappearing, also fixed some confusing page type labels
* Eliminated "after commit callback" workaround from integration tests by implementing "transparent transactions" capability in integration testing framework
* Refactored admin authentication/authorization in RSS module. Removed program termination and covered the controllers with tests
* Removed HTML-report feature of copy-paste detector which never worked anyway (`dev/tests/static/framework/Inspection/CopyPasteDetector/html_report.xslt` and all related code)
* GitHub requests:
  * [#19](https://github.com/magento/magento2/pull/19) Implemented "soft" dependency between modules and performed several improvements in the related code, covered with tests

2.0.0.0-dev12
=============
* Implemented backend authentication independent of `Mage_Adminhtml` module. Authentication can be disabled
  * Authentication logic is moved to `Mage_Backend` module and being performed in controller instead of observer
  * `Mage_Adminhtml_Controller_Action` is changed to `Mage_Backend_Controller_ActionAbstract`, `Mage_Admin_Model_Session` is changed to `Mage_Backend_Model_Auth_Session`, `Mage_User_Model_Role` and `Mage_User_Model_Roles` classes are unified into one `Mage_User_Model_Role` class
  * Introduced `Mage_User` module for users and roles management
* Introduced support of minimized CSS and JS files: in production mode minimized file is used, if exists
* Implemented resize, rotate, crop and watermark functionality for ImageMagick adapter
* Fixed some issues:
  * Fixed absence of product without image, if ImageMagick is used
  * Fixed broken Downloadable product creation page, when developer mode is enabled
  * Fixed random failures of `Integrity_LayoutTest::testHandlesHierarchy` test
  * Fixed backup creation: media directory was excluded from the backup file when it should be included and vice-versa
  * Fixed broken product configuration page in Shopping Cart
  * Fixed incorrect work of "After number of attempts to login" functionality for CAPTCHA: captcha was not displayed after specified number of incorrect attempts
  * Fixed creation of User Role: resource access was set to 'Custom', when 'All' is selected
  * Fixed exception "Unable to locate skin file" at the end of installation
  * Fixed broken "Use Store Credit" functionality on checkout
  * Fixed lost MySQL connection, if it's not used for long time
  * Fixed ability to separate CDN server setup for static and media content
  * Other small fixes

2.0.0.0-dev11
=============
* Published performance tests (`dev/tests/performance`)
* Implemented support of ImageMagick library for processing images
* Distinguished "Page Fragments" and "Pages" in layout handles declaration
* Reduced performance drop caused by introducing containers
* Implemented `Magento_Data_Structure` library which is used to handle structure of layout elements
* Fixed some issues:
  * Fixed error on saving newsletter template
  * Fixed some checkout issues:
    * order is not placed if 3D Secure is used
    * transaction is not created if PayPal Standard is used
    * "Purchase Order Number" field is not displayed
    * failed checkout on concurrent user requests
  * Fixed incorrect shipment creation: shipping and tracking information was not saved
  * Fixed broken category page when "Use Flat Catalog Product" is enabled
  * Fixed incorrect applying of discount to a sub-product, if more two rules are being applied
  * Fixed broken "Edit" product link in Wishlist and Shopping Cart
  * Fixed broken installation when `pub/media` is not writable
  * Fixed resetting Design Theme configuration option when User-Agent Exception is added
  * Fixed error while running unit tests, when Zend Framework is installed with PEAR
  * Fixed incorrect processing of "before" and "after" layout instructions in case, when the instruction refers to a node, which is not processed yet
  * Fixed broken import/export functionality
  * Fixed broken Web Services pages in Admin Panel
  * Fixed broken creation of URL rewrite
  * Fixed error in Weee module caused broken view pages of configurable products with recurring profile or downloadable/bundle product
  * Fixed error on submitting XML Connect application
  * Fixed broken design when database is used as media storage
* Other small changes

2.0.0.0-dev10
=============
* Implemented configuration option that enables the page types hierarchy usage during the page generation
* Removed theme/view config files caching in favour of the view files map to be implemented in the future
* Fixed some issues:
  * Fixed persistent elements highlighting for the Visual Design Editor
  * Fixed instruction text absence for the "Cash On Delivery Payment" payment method
  * Fixed suggestion text for the 'urlEscape' method in the legacy test
  * Fixed "Manage Currency Symbols" system configuration page
  * Fixed currency validation for the PayPal Website Payments Standard
  * Fixed Web API for product to category assignment by SKU containing digits only
* Implemented new tests

2.0.0.0-dev09
=============
* Added theme inheritance ability
* Removed @group annotation usage in integration tests
* Introduced keeping of highlighting state while switching between pages in Visual Design Editor
* Fixed some issues:
  * Fixed producing a misleading message by phpcs and phpmd static tests in a case, when no test is failed, but the tools execution failed itself
  * Fixed automatic publication of images in case when image is changed, but CSS file is not
  * Fixed broken "Customer My Account My OAuth Applications" page type in Visual Design Editor
* Fetched updates from Magento 1.x up to April 30 2012. Refer to [Magento 1 release notes](http://www.magentocommerce.com/download/release_notes) for details

2.0.0.0-dev08
=============
* Introduced `Mage_Backend` module and relocated backend area routing model there (was `Mage_Core_Controller_Varien_Router_Admin`). The "adminhtml" area is also declared in the `Mage_Backend` module.
* Introduced declaration of application area in config.xml with the following requirements:
  * Must declare with a router class in `config/global/areas/<area_code>/routers/<router_code>/class`
  * May declare `config/global/areas/<area_code>/base_controller`, which would enforce any controllers that serve in this area, to be descendants of the specified class
* Refined styling of the visual design editor toolbar. Subtle improvements of toolbar usability.
* Fetched updates from Magento 1.x up to April 11 2012. Refer to [Magento 1 release notes](http://www.magentocommerce.com/download/release_notes) for details

2.0.0.0-dev07
=============
* Implemented a tool for migrating factory table names from 1.x to 2.x. The tool replaces table names by list of names associations
* Changed Unit tests suite running from usage AllTests.php in each directory to configuration in phpunit.xml.dist. Now all tests in `testsuite` directory are launched, there is no necessity to add new tests to the config
* Implemented in Visual Design Editor:
  * Containers highlighting
  * Dropping of elements
* Fixed some issues:
  * Fixed sorting of elements in Layout if element is added before its sibling
  * Fixed broken Customer Registration page on Front-End and Back-End
  * Fixed broken Order Create page on Back-End
  * Replaced usages of deprecated Mage_Customer_Model_Address_Abstract::getFormated() to Mage_Customer_Model_Address_Abstract::format()
  * Fixed elements' duplication on pages (downloadable, bundle product view)
* Fetched updates from Magento 1 up to April 6 2012. Refer to [Magento 1 release notes](http://www.magentocommerce.com/download/release_notes) for details

2.0.0.0-dev06
=============
* Introduced concept of containers and "page types" in layout.
  * Containers replace `Mage_Core_Block_Text_List` and `Mage_Page_Block_Html_Wrapper`
  * Widgets now utilize page types and containers instead of "handles" and "block references"
* Implemented first draft of visual design editor with the following capabilities
  * highlighting and dragging blocks and containers, toggling highlighting on/off
  * switching to arbitrary theme and skin
  * navigating to arbitrary page types (product view, order success page, etc...), so that they would be editable with visual design editor
* Refactored various places across the system in order to accommodate transition to containers, page types and visual design editor, which includes
  * Output in any frontend controller action using layout only
  * Output in any frontend controller specifies one and only one layout handle, which equals to its full action name. There can be other handles that extend it and they are determined by layout loading parameters, provided by controller.
  * No program termination (exit) on logging in admin user
  * Session cookie lifetime is set to 0 for frontend and backend. Session will exist until browser window is open, however backend session lifetime limitation does not depend on cookie lifetime anymore.
* Fixes:
  * Failures of tests in developer mode
  * `app/etc/local.xml` affected integration tests
* Addressed pull requests and issues from Github
* Fetched updates from Magento 1 up to March 2 2012. Refer to [Magento 1 release notes](http://www.magentocommerce.com/download/release_notes) for details.

2.0.0.0-dev05
=============
* Added jQuery library. It has not been made a main library yet, however all new features are developed using jQuery.
* Added support for new versions of testing tools - PHPUnit 3.6, PHPMD 1.3.0. Confirmed compatibility with latest PHPCS 1.3.2 and PHPCPD 1.3.5.
* Improved legacy tests:
  * Refactored Integrity_ClassesTest and Legacy_ClassesTest.
  * Implemented a tool for migrating factory names from 1.x to 2.x. The tool scans PHP-code and replaces the most "popular" cases.
  * Added tests for `//model` in config.xml files and `//*[@module]` in all xml files.
  * Implemented a test that verifies the absence of relocated directories.
  * Added a test against the obsolete Varien_Profiler.
* Bug fixes:
  * Fixed docblock for Mage_Core_Model_Design_Package.
  * Fixed static code analysis failures related to case-sensitivity.
  * Fixed several typos and minor mistakes.
  * Fixed integration tests' failures due to specifics of xpath library version.
* Imported fresh features and bug fixes from Magento 1.x.

2.0.0.0-dev04
=============
* Various code integrity fixes in different places:
  * Fixed obsolete references to classes
  * Fixed broken references to template and static view files
  * Fixed some minor occurrences of deprecated code
  * Code style minor fixes
* Various minor bugfixes
* Implemented "developer mode" in integration tests
* Added "rollback" scripts capability for data fixtures
* Removed deprecated methods and attributes from product type class
* Restructured code integrity tests:
  * Moved out part of the tests from integration into static tests
  * Introduced "Legacy" test suite in static tests. This test suite is not executed by default when running either phpunit directly or using the "batch tool"
  * Simplified and reorganized the "Exemplar" and self-assessment tests for static code analysis
* Covered previously made backwards-incompatible changes with legacy tests
* Changed storage of class map from a PHP-file with array into a better-performing text file with serialized array.
* Published `dev/tests/static` and `dev/tests/unit`

2.0.0.0-dev03
=============
* A test release just to verify deployment scripts

2.0.0.0-dev02
=============
Deprecated code & minor fixes update:
* Eliminated remnants of `htmlescape` implementation
* Eliminated usage of `pub/js/index.php` entry point (used to be `js/index.php`)
* Disbanded the shell root directory: moved scripts into `dev/shell` and classes into app
* Minor refactoring of data fixtures rollback capability in integration testing framework

2.0.0.0-dev01
=============
* Added initial version of Magento 2.x CE to public repository
