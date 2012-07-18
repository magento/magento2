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
