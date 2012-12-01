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
* Added scripts that allow upgrading database from CE 1.7 (EE 1.12) to 2.x
* Replaced calendar UI component with jQuery calendar
* Removed store scope selector from backend customers management
* Renamed `pub/js` (was known as `js` in Magento 1.x) into `pub/lib`
* Restored back the public access to `pub/cron.php` entry point (in the previous patch it was denied by mistake)

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
  * [#51](https://github.com/magento/magento2/issues/51) -- fixed managing of scope-spefic values for Categories
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
* Implemented in Visual Desig Editor:
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
* eliminated remnants of `htmlescape` implementation
* eliminated usage of `pub/js/index.php` entry point (used to be `js/index.php`)
* disbanded the shell root directory: moved scripts into `dev/shell` and classes into app
* minor refactoring of data fixtures rollback capability in integration testing framework

2.0.0.0-dev01
=============
* Added initial version of Magento 2.x CE to public repository
