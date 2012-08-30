Update as of 8/30/2012
======================
* Fixes:
  * Fixed name, title, markup, styles at "Orders and Returns" homepage
  * Fixed displaying products in the shopping cart item block at the backend

Update as of 8/26/2012
======================
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

Update as of 8/15/2012
======================
* Refactored ACL functionality:
  * Implementation is not bound to backend area anymore and moved to `Mage_Core` module
  * Covered backwards-incompatible changes with additional migration tool (`dev/tools/migration/Acl`)
* Implemented "move" layout directive and slightly modified behavior of "remove"
* A failure in DB cleanup by integration testing framework is articulated more clearly by throwing `Magento_Exception`
* Fixed security vulnerability of exploiting Magento "cookie restriction" feature
* Fixed caching mechanism of loading modules declaration to not cause additional performance overhead
* Adjusted include path in unit tests to use the original include path at the end, rather than at the beginning

Update as of 8/9/2012
=====================
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

Update as of 8/2/2012
=====================
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

Update as of 7/26/2012
=====================
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

Update as of 7/19/2012
=====================
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

Update as of 7/3/2012
=====================
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

Update as of 6/20/2012
=====================
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

Update as of 6/7/2012
=====================
* Fixed various crashes of visual design editor
* Fixed some layouts that caused visual design editor toolbar disappearing, also fixed some confusing page type labels
* Eliminated "after commit callback" workaround from integration tests by implementing "transparent transactions" capability in integration testing framework
* Refactored admin authentication/authorization in RSS module. Removed program termination and covered the controllers with tests
* Removed HTML-report feature of copy-paste detector which never worked anyway (`dev/tests/static/framework/Inspection/CopyPasteDetector/html_report.xslt` and all related code)
* GitHub requests:
  * [#19](https://github.com/magento/magento2/pull/19) Implemented "soft" dependency between modules and performed several improvements in the related code, covered with tests

Update as of 5/31/2012
======================
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

Update as of 5/23/2012
======================
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
  * Fixed broken installation when "pub/media" is not writable
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

Update as of 5/09/2012
======================
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

Update as of 5/05/2012
======================
* Added theme inheritance ability
* Removed @group annotation usage in integration tests
* Introduced keeping of highlighting state while switching between pages in Visual Design Editor
* Fixed some issues:
  * Fixed producing a misleading message by phpcs and phpmd static tests in a case, when no test is failed, but the tools execution failed itself
  * Fixed automatic publication of images in case when image is changed, but CSS file is not
  * Fixed broken "Customer My Account My OAuth Applications" page type in Visual Design Editor
* Fetched updates from Magento 1.x up to April 30 2012. Refer to [Magento 1 release notes](http://www.magentocommerce.com/download/release_notes) for details

Update as of 4/26/2012
======================
* Introduced `Mage_Backend` module and relocated backend area routing model there (was `Mage_Core_Controller_Varien_Router_Admin`). The "adminhtml" area is also declared in the `Mage_Backend` module.
* Introduced declaration of application area in config.xml with the following requirements:
  * Must declare with a router class in `config/global/areas/<area_code>/routers/<router_code>/class`
  * May declare `config/global/areas/<area_code>/base_controller`, which would enforce any controllers that serve in this area, to be descendants of the specified class
* Refined styling of the visual design editor toolbar. Subtle improvements of toolbar usability.
* Fetched updates from Magento 1.x up to April 11 2012. Refer to [Magento 1 release notes](http://www.magentocommerce.com/download/release_notes) for details

Update as of 4/13/2012
======================

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

Update as of 3/26/2012
======================

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

Update as of 2/29/2012
======================

* Added jQuery to Magento 2. It has not been made a main library yet, however all new features are developed using jQuery.
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
  * Fixed integration test's failures due to specifics of xpath library version.
* Imported fresh features and bug fixes from Magento 1.x.

Additional Tests and Fixes
==========================

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
* Published dev/tests/static and dev/tests/unit
