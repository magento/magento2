2.1.16
=============
* GitHub issues:
    * [#16653](https://github.com/magento/magento2/issues/16653) -- Not possible to create an invoice (fixed in [magento/magento2#17413](https://github.com/magento/magento2/pull/17413))
    * [#16655](https://github.com/magento/magento2/issues/16655) -- Block totalbar not used in invoice create and credit memo create screens (fixed in [magento/magento2#17413](https://github.com/magento/magento2/pull/17413))
    * [#4803](https://github.com/magento/magento2/issues/4803) -- Incorrect return value from Product Attribute Repository (fixed in [magento/magento2#15688](https://github.com/magento/magento2/pull/15688))
    * [#12250](https://github.com/magento/magento2/issues/12250) -- View.xml is inheriting image sizes from parent (so an optional field is replaced by the value of parent) (fixed in [magento/magento2#17439](https://github.com/magento/magento2/pull/17439))
    * [#13429](https://github.com/magento/magento2/issues/13429) -- Magento 2.2.2 password reset strength meter (fixed in [magento/magento2#17290](https://github.com/magento/magento2/pull/17290))
    * [#15028](https://github.com/magento/magento2/issues/15028) -- Configurable product addtocart with restAPI not working as expected (fixed in [magento/magento2#17476](https://github.com/magento/magento2/pull/17476))
    * [#17289](https://github.com/magento/magento2/issues/17289) -- Magento 2.2.5: Year-to-date dropdown in Stores>Configuration>General>Reports>Dashboard (fixed in [magento/magento2#17496](https://github.com/magento/magento2/pull/17496))
    * [#16555](https://github.com/magento/magento2/issues/16555) -- "Shipping address is not set" exception in Multishipping Checkout. (fixed in [magento/magento2#16783](https://github.com/magento/magento2/pull/16783))
    * [#14056](https://github.com/magento/magento2/issues/14056) -- Coupon API not working for guest user (fixed in [magento/magento2#16782](https://github.com/magento/magento2/pull/16782))
    * [#6305](https://github.com/magento/magento2/issues/6305) -- Can't save Customizable options (fixed in [magento/magento2#17609](https://github.com/magento/magento2/pull/17609))
    * [#16273](https://github.com/magento/magento2/issues/16273) -- Method $product->getUrlInStore() returning extremely long URLs, could be a bug (fixed in [magento/magento2#16310](https://github.com/magento/magento2/pull/16310))
    * [#16499](https://github.com/magento/magento2/issues/16499) -- User role issue with customer group (fixed in [magento/magento2#17629](https://github.com/magento/magento2/pull/17629))
    * [#13102](https://github.com/magento/magento2/issues/13102) -- review/product/listAjax/id/{{non existent id}/ (fixed in [magento/magento2#17632](https://github.com/magento/magento2/pull/17632))
    * [#17648](https://github.com/magento/magento2/issues/17648) -- UI validation rule for valid time am/pm doesn't work when js is minified (fixed in [magento/magento2#17689](https://github.com/magento/magento2/pull/17689))
    * [#17700](https://github.com/magento/magento2/issues/17700) -- Message list component: the message type is always error when parameters specified (fixed in [magento/magento2#17702](https://github.com/magento/magento2/pull/17702))
    * [#14248](https://github.com/magento/magento2/issues/14248) -- Transparent background becomes black for thumbnails of PNG into Wysiwyg editor... (fixed in [magento/magento2#17855](https://github.com/magento/magento2/pull/17855))
    * [#17851](https://github.com/magento/magento2/issues/17851) -- Wishlist icon cut on Shopping cart page in mobile view (fixed in [magento/magento2#17912](https://github.com/magento/magento2/pull/17912))
    * [#10687](https://github.com/magento/magento2/issues/10687) -- Product image roles randomly disappear (fixed in [magento/magento2#17553](https://github.com/magento/magento2/pull/17553))
    * [#8035](https://github.com/magento/magento2/issues/8035) -- Join extension attributes are not added to Order results (REST api) (fixed in [magento/magento2#16169](https://github.com/magento/magento2/pull/16169))
    * [#2146](https://github.com/magento/magento2/issues/2146) -- Countries dropdown is empty (fixed in [magento/magento2#17194](https://github.com/magento/magento2/pull/17194))
    * [#4547](https://github.com/magento/magento2/issues/4547) -- Support for new .tech TLD and others required (fixed in [magento/magento2#11576](https://github.com/magento/magento2/pull/11576))
* GitHub pull requests:
    * [magento/magento2#17413](https://github.com/magento/magento2/pull/17413) -- [Backport] [Fix #16655] Block totalbar not used in invoice create and credit memo create screens (by @dverkade)
    * [magento/magento2#17422](https://github.com/magento/magento2/pull/17422) -- [Backport] Resolved special character issue for sidebar (by @mage2pratik)
    * [magento/magento2#15688](https://github.com/magento/magento2/pull/15688) -- [Backport] Fix #4803: Incorrect return value from Product Attribute Repository (by @cream-julian)
    * [magento/magento2#17439](https://github.com/magento/magento2/pull/17439) -- [Backport] magento/magento2#12250: View.xml is inheriting image sizes from parennt (by @quisse)
    * [magento/magento2#17290](https://github.com/magento/magento2/pull/17290) -- [Backport] Fix bug Magento 2.2.2 password reset strength meter #13429 (by @jignesh-baldha)
    * [magento/magento2#17476](https://github.com/magento/magento2/pull/17476) -- [Backport] Convert to string $option->getValue, in order to be compared with other (by @mage2pratik)
    * [magento/magento2#17496](https://github.com/magento/magento2/pull/17496) -- [Backport] Magento 2.2.5: Year-to-date dropdown in Stores>Configuration>General>Reports>Dashboard #17289 (by @ronak2ram)
    * [magento/magento2#16783](https://github.com/magento/magento2/pull/16783) -- [Backport] Fix the issue with "Shipping address is not set" exception (by @dmytro-ch)
    * [magento/magento2#17366](https://github.com/magento/magento2/pull/17366) -- [Backport] Fixed some minor css issue (by @arnoudhgz)
    * [magento/magento2#16782](https://github.com/magento/magento2/pull/16782) -- [Backport] issue/14056 - Coupon API not working for guest user (by @gelanivishal)
    * [magento/magento2#17083](https://github.com/magento/magento2/pull/17083) -- [Backport] Make scope parameters of methods to save/delete config optional (by @mageprince)
    * [magento/magento2#17609](https://github.com/magento/magento2/pull/17609) -- [Backport] 6305 - Resolved product custom option title save issue (by @jignesh-baldha)
    * [magento/magento2#17613](https://github.com/magento/magento2/pull/17613) -- [Backport] Added template as argument to the store address renderer to allow custom formatting (by @TomashKhamlai)
    * [magento/magento2#16310](https://github.com/magento/magento2/pull/16310) -- #16273: [Backport] Fix bug in method getUrlInStore() of product model (by @vasilii-b)
    * [magento/magento2#17629](https://github.com/magento/magento2/pull/17629) -- [Backport] Solution for User role issue with customer group (by @jignesh-baldha)
    * [magento/magento2#17632](https://github.com/magento/magento2/pull/17632) -- [Backport] Fixed review list ajax if product not exist redirect to 404 page #13102 (by @mage2pratik)
    * [magento/magento2#17667](https://github.com/magento/magento2/pull/17667) -- [Backport] Added unit test for newsletter problem model (by @jignesh-baldha)
    * [magento/magento2#17684](https://github.com/magento/magento2/pull/17684) -- [Backport] Fix Custom Attribute Group can not translate in catalog/product page (by @dmytro-ch)
    * [magento/magento2#17689](https://github.com/magento/magento2/pull/17689) -- [Backport] Update time12h javascript validation rule to be compatible with js minify (by @dmytro-ch)
    * [magento/magento2#17682](https://github.com/magento/magento2/pull/17682) -- [Backport] CMS: Add missing unit tests for model classes (by @dmytro-ch)
    * [magento/magento2#17702](https://github.com/magento/magento2/pull/17702) -- [Backport] Message list component fix: the message type is always error when parameters specified (by @dmytro-ch)
    * [magento/magento2#17606](https://github.com/magento/magento2/pull/17606) -- [Backport] Catalog: Add unit tests for Cron classes (by @jignesh-baldha)
    * [magento/magento2#17774](https://github.com/magento/magento2/pull/17774) -- Fix for ProductLink - setterName was incorrectly being set (by @insanityinside)
    * [magento/magento2#17855](https://github.com/magento/magento2/pull/17855) -- [Backport] Fixes black background for png images in wysiwyg editors. (by @eduard13)
    * [magento/magento2#17839](https://github.com/magento/magento2/pull/17839) -- [Backport] [Search] Unit test for SynonymAnalyzer model. 2.1 back port. (by @furseyev)
    * [magento/magento2#17912](https://github.com/magento/magento2/pull/17912) -- [Backport] Resolved : Wishlist icon cut on Shopping cart page in mobile view #17851 #28 (by @hitesh-wagento)
    * [magento/magento2#17940](https://github.com/magento/magento2/pull/17940) -- [Backport] Sales: Add unit test for validator model class (by @dmytro-ch)
    * [magento/magento2#17883](https://github.com/magento/magento2/pull/17883) -- Update for pull request #17774 - now using SimpleDataObjectConverter: (by @insanityinside)
    * [magento/magento2#17553](https://github.com/magento/magento2/pull/17553) -- [Backport] Fix #10687 - Product image roles disappearing (by @eduard13)
    * [magento/magento2#16169](https://github.com/magento/magento2/pull/16169) -- [Backport] #8035 join extension attributes not added to orders (by @Scarraban)
    * [magento/magento2#17194](https://github.com/magento/magento2/pull/17194) -- [Backport] Fixes reverted for remove space when only one country in drop-down on both cart (by @nilesh2jcommerce)
    * [magento/magento2#11576](https://github.com/magento/magento2/pull/11576) -- Fix Support for new Email address domain #4547 (by @elachino)

2.1.15
=============
* GitHub issues:
    * [#13652](https://github.com/magento/magento2/issues/13652) -- Issue in product title with special chars in mini cart (fixed in [magento/magento2#14665](https://github.com/magento/magento2/pull/14665))
    * [#13010](https://github.com/magento/magento2/issues/13010) -- Write a Review page works on multistore for products that are not assigned to that store (fixed in [magento/magento2#14673](https://github.com/magento/magento2/pull/14673))
    * [#14465](https://github.com/magento/magento2/issues/14465) -- [Indexes] Product 'version_id' lost last 'auro_increment' value after MySQL restart. (fixed in [magento/magento2#14471](https://github.com/magento/magento2/pull/14471))
    * [#9666](https://github.com/magento/magento2/issues/9666) -- Magento 2.1.6 - Invoice PDF doesn't support Thai (fixed in [magento/magento2#14711](https://github.com/magento/magento2/pull/14711))
    * [#12323](https://github.com/magento/magento2/issues/12323) -- Magento 2.1.3 - Invoice and shipment PDF doesn't support Arabic (fixed in [magento/magento2#14711](https://github.com/magento/magento2/pull/14711))
    * [#12430](https://github.com/magento/magento2/issues/12430) -- While assigning prices to configurable products, prices aren's readable when using custom price symbol. (fixed in [magento/magento2#14902](https://github.com/magento/magento2/pull/14902))
    * [#12714](https://github.com/magento/magento2/issues/12714) -- Extra records are in exported CSV file for order (fixed in [magento/magento2#14903](https://github.com/magento/magento2/pull/14903))
    * [#14663](https://github.com/magento/magento2/issues/14663) -- Updating Customer through rest/all/V1/customers/:id resets group_id if group_id not passed in payload (fixed in [magento/magento2#14757](https://github.com/magento/magento2/pull/14757))
    * [#5768](https://github.com/magento/magento2/issues/5768) -- Magento 2.0.7 XML sitemap is not generated by schedule (fixed in [magento/magento2#15159](https://github.com/magento/magento2/pull/15159))
    * [#10210](https://github.com/magento/magento2/issues/10210) -- Transport variable can not be altered in email_invoice_set_template_vars_before Event (fixed in [magento/magento2#15038](https://github.com/magento/magento2/pull/15038) and [magento/magento2#16601](https://github.com/magento/magento2/pull/16601))
    * [#14692](https://github.com/magento/magento2/issues/14692) -- 'validate-grouped-qty' validation is meaningless (fixed in [magento/magento2#15407](https://github.com/magento/magento2/pull/15407))
    * [#13704](https://github.com/magento/magento2/issues/13704) -- Category\Collection::joinUrlRewrite should use the store set on the collection (fixed in [magento/magento2#13756](https://github.com/magento/magento2/pull/13756))
    * [#9580](https://github.com/magento/magento2/issues/9580) -- Quote Attribute trigger_recollect causes a timeout (fixed in [magento/magento2#15522](https://github.com/magento/magento2/pull/15522))
    * [#14941](https://github.com/magento/magento2/issues/14941) -- Unnecessary recalculation of product list pricing causes huge slowdowns (fixed in [magento/magento2#15445](https://github.com/magento/magento2/pull/15445))
    * [#13992](https://github.com/magento/magento2/issues/13992) -- Incorrect phpdoc should be Shipment\Item not Invoice\Item (fixed in [magento/magento2#15619](https://github.com/magento/magento2/pull/15619))
    * [#15601](https://github.com/magento/magento2/issues/15601) -- Wrong annotation in formatDateTime - lib/internal/Magento/Framework/Stdlib/DateTime/TimezoneInterface.php (fixed in [magento/magento2#15668](https://github.com/magento/magento2/pull/15668) and [magento/magento2#15669](https://github.com/magento/magento2/pull/15669))
    * [#7897](https://github.com/magento/magento2/issues/7897) -- Menu widget submenu alignment (fixed in [magento/magento2#15714](https://github.com/magento/magento2/pull/15714))
    * [#15354](https://github.com/magento/magento2/issues/15354) -- Refactor javascript code of button split widget call js component (fixed in [magento/magento2#15736](https://github.com/magento/magento2/pull/15736))
    * [#15192](https://github.com/magento/magento2/issues/15192) -- Module Manager module grid is not working Magento 2.2.4 (fixed in [magento/magento2#15756](https://github.com/magento/magento2/pull/15756))
    * [#15319](https://github.com/magento/magento2/issues/15319) -- misleading data-container in product list (fixed in [magento/magento2#15816](https://github.com/magento/magento2/pull/15816))
    * [#15590](https://github.com/magento/magento2/issues/15590) -- Typo in tests / setCateroryIds([]) (fixed in [magento/magento2#15814](https://github.com/magento/magento2/pull/15814))
    * [#15510](https://github.com/magento/magento2/issues/15510) -- First PDF download / export after login (fixed in [magento/magento2#15767](https://github.com/magento/magento2/pull/15767))
    * [#15608](https://github.com/magento/magento2/issues/15608) -- Styling select by changing less variables in Luma theme doesn't work as expected (fixed in [magento/magento2#15796](https://github.com/magento/magento2/pull/15796))
    * [#14249](https://github.com/magento/magento2/issues/14249) -- Priduct page price is using the hardcoded digits in js (fixed in [magento/magento2#15926](https://github.com/magento/magento2/pull/15926))
    * [#14089](https://github.com/magento/magento2/issues/14089) -- Malaysian (Malaysia) missing from locale list (fixed in [magento/magento2#15927](https://github.com/magento/magento2/pull/15927))
    * [#11477](https://github.com/magento/magento2/issues/11477) -- Magento REST API Schema (Swagger) is not compatible with Search Criteria (fixed in [magento/magento2#15945](https://github.com/magento/magento2/pull/15945))
    * [#6058](https://github.com/magento/magento2/issues/6058) -- IE11 user login email validation fails if field has leading or trailing space (fixed in [magento/magento2#15874](https://github.com/magento/magento2/pull/15874) and [magento/magento2#16297](https://github.com/magento/magento2/pull/16297) and [magento/magento2#16986](https://github.com/magento/magento2/pull/16986))
    * [#15323](https://github.com/magento/magento2/issues/15323) -- limiter float too generic (fixed in [magento/magento2#15880](https://github.com/magento/magento2/pull/15880))
    * [#14999](https://github.com/magento/magento2/issues/14999) -- Changing @tab-content__border variable has no effect in Blank theme (fixed in [magento/magento2#15917](https://github.com/magento/magento2/pull/15917))
    * [#13899](https://github.com/magento/magento2/issues/13899) -- Postal code (zip code) for Canada should allow postal codes without space (fixed in [magento/magento2#16031](https://github.com/magento/magento2/pull/16031))
    * [#8954](https://github.com/magento/magento2/issues/8954) -- Error While Trying To Load Quote Item Collection Using Magento\Quote\Model\ResourceModel\QuoteItem\Collection::getItems() (fixed in [magento/magento2#15829](https://github.com/magento/magento2/pull/15829))
    * [#15348](https://github.com/magento/magento2/issues/15348) -- Multiple Payment Methods Enabled is giving error in console "Found 3 Elements with non - unique Id" (fixed in [magento/magento2#15834](https://github.com/magento/magento2/pull/15834))
    * [#15832](https://github.com/magento/magento2/issues/15832) -- No button-primary__font-weight (fixed in [magento/magento2#16037](https://github.com/magento/magento2/pull/16037))
    * [#14747](https://github.com/magento/magento2/issues/14747) -- Newsletter subscription confirmation message does not display after clicking link in email (fixed in [magento/magento2#15860](https://github.com/magento/magento2/pull/15860))
    * [#12601](https://github.com/magento/magento2/issues/12601) -- A space between the category page and the main footer when applying specific settings (fixed in [magento/magento2#15727](https://github.com/magento/magento2/pull/15727))
    * [#15255](https://github.com/magento/magento2/issues/15255) -- Customer who exceeded max login failures not able to login even after reset password (fixed in [magento/magento2#16255](https://github.com/magento/magento2/pull/16255))
    * [#13415](https://github.com/magento/magento2/issues/13415) -- Duplicated elements id in checkout page (fixed in [magento/magento2#16264](https://github.com/magento/magento2/pull/16264))
    * [#15352](https://github.com/magento/magento2/issues/15352) -- Reformat the javascript code as per magento standards. (fixed in [magento/magento2#16270](https://github.com/magento/magento2/pull/16270))
    * [#13793](https://github.com/magento/magento2/issues/13793) -- Submitting search form (mini) with enter key fires event handlers bound by jquery twice (fixed in [magento/magento2#16281](https://github.com/magento/magento2/pull/16281))
    * [#15213](https://github.com/magento/magento2/issues/15213) -- Alignment & overlapping Issue on every Home page & category page of Hot Seller section (fixed in [magento/magento2#16287](https://github.com/magento/magento2/pull/16287))
    * [#7379](https://github.com/magento/magento2/issues/7379) -- Calendar widget (jQuery UI DatePicker) with numberOfMonths = 2 or more (fixed in [magento/magento2#16280](https://github.com/magento/magento2/pull/16280))
    * [#16079](https://github.com/magento/magento2/issues/16079) -- Need information about translating issue (Magento Swatches Js) (fixed in [magento/magento2#16229](https://github.com/magento/magento2/pull/16229))
    * [#8222](https://github.com/magento/magento2/issues/8222) -- Estimate Shipping and Tax Form not works due to js error in collapsible.js [proposed fix] (fixed in [magento/magento2#16491](https://github.com/magento/magento2/pull/16491))
    * [#7399](https://github.com/magento/magento2/issues/7399) -- Modal UI: clickableOverlay option doesn't work (fixed in [magento/magento2#16665](https://github.com/magento/magento2/pull/16665))
    * [#15940](https://github.com/magento/magento2/issues/15940) -- Wrong end of month at Reports for Europe/Berlin time zone if month contains 31 day (fixed in [magento/magento2#16585](https://github.com/magento/magento2/pull/16585))
    * [#12081](https://github.com/magento/magento2/issues/12081) -- Magento 2.2.0: Translations for 'Item in Cart' missing in mini cart. (fixed in [magento/magento2#16720](https://github.com/magento/magento2/pull/16720))
    * [#14351](https://github.com/magento/magento2/issues/14351) -- Product import doesn't change `Enable Qty Increments` field (fixed in [magento/magento2#14380](https://github.com/magento/magento2/pull/14380))
    * [#16378](https://github.com/magento/magento2/issues/16378) -- Wrong placeholder for password field in the checkout page (fixed in [magento/magento2#16526](https://github.com/magento/magento2/pull/16526))
    * [#15355](https://github.com/magento/magento2/issues/15355) -- Function is unnecessarily called multiple time (fixed in [magento/magento2#16761](https://github.com/magento/magento2/pull/16761))
    * [#16764](https://github.com/magento/magento2/issues/16764) -- Rating Star issue on Product detail Page.  (fixed in [magento/magento2#16839](https://github.com/magento/magento2/pull/16839))
    * [#15848](https://github.com/magento/magento2/issues/15848) -- no navigation-level0-item__hover__color (fixed in [magento/magento2#16797](https://github.com/magento/magento2/pull/16797))
    * [#13692](https://github.com/magento/magento2/issues/13692) -- In payment step of checkout I cannot unselect #billing-save-in-address-book checkbox in non-first payment method (fixed in [magento/magento2#16811](https://github.com/magento/magento2/pull/16811))
    * [#15467](https://github.com/magento/magento2/issues/15467) -- Cart does not load when Configuration product option is deleted and that option is in the cart (fixed in [magento/magento2#16812](https://github.com/magento/magento2/pull/16812))
    * [#16184](https://github.com/magento/magento2/issues/16184) -- Argument 1 passed to Magento\Sales\Model\Order\Payment must be an instance of Magento\Framework\DataObject, none given (fixed in [magento/magento2#16801](https://github.com/magento/magento2/pull/16801))
    * [#5316](https://github.com/magento/magento2/issues/5316) -- [2.1.0] HTML minification problem with php tag with a comment and no space at the end (fixed in [magento/magento2#16917](https://github.com/magento/magento2/pull/16917))
    * [#12860](https://github.com/magento/magento2/issues/12860) -- Sort by Product Name doesn't work with Ancor and available filters (fixed in [magento/magento2#16945](https://github.com/magento/magento2/pull/16945))
    * [#16174](https://github.com/magento/magento2/issues/16174) -- Admin tabs order not working properly (fixed in [magento/magento2#16920](https://github.com/magento/magento2/pull/16920))
    * [#2956](https://github.com/magento/magento2/issues/2956) -- Unable to render page when 'meta title' page config param is set (fixed in [magento/magento2#16948](https://github.com/magento/magento2/pull/16948))
    * [#15935](https://github.com/magento/magento2/issues/15935) -- Mass delete deletes all products (fixed in [magento/magento2#16702](https://github.com/magento/magento2/pull/16702))
    * [#12320](https://github.com/magento/magento2/issues/12320) -- Newsletter subscribe button title wrapped (fixed in [magento/magento2#17022](https://github.com/magento/magento2/pull/17022))
    * [#13988](https://github.com/magento/magento2/issues/13988) -- Mini search field looses focus after its JavaScript is initialized (fixed in [magento/magento2#17086](https://github.com/magento/magento2/pull/17086))
    * [#13006](https://github.com/magento/magento2/issues/13006) -- Drop down values are not showing in catalog product grid magento2 (fixed in [magento/magento2#17088](https://github.com/magento/magento2/pull/17088))
    * [#13595](https://github.com/magento/magento2/issues/13595) -- loadCache for Block Magento\Theme\Block\Html\Footer dont work (fixed in [magento/magento2#17092](https://github.com/magento/magento2/pull/17092))
    * [#16529](https://github.com/magento/magento2/issues/16529) -- Rewriting product listing widget block breaks its template rendering. (fixed in [magento/magento2#17111](https://github.com/magento/magento2/pull/17111))
    * [#13769](https://github.com/magento/magento2/issues/13769) -- Order Email Sender (fixed in [magento/magento2#17087](https://github.com/magento/magento2/pull/17087))
    * [#15356](https://github.com/magento/magento2/issues/15356) -- Refactore javascript for module URL rewrite (fixed in [magento/magento2#16992](https://github.com/magento/magento2/pull/16992))
    * [#11512](https://github.com/magento/magento2/issues/11512) -- Incorrect use of 503 status code (fixed in [magento/magento2#17241](https://github.com/magento/magento2/pull/17241))
    * [#11140](https://github.com/magento/magento2/issues/11140) -- Going to '/admin' while using storecodes in url and a different adminhtml url will throw exception (fixed in [magento/magento2#17243](https://github.com/magento/magento2/pull/17243))
    * [#11540](https://github.com/magento/magento2/issues/11540) -- Magento sets iso invalid language code in html header (fixed in [magento/magento2#17212](https://github.com/magento/magento2/pull/17212))
    * [#9919](https://github.com/magento/magento2/issues/9919) -- Pattern Validation via UI Component Fails to Interpret String as RegEx Pattern (fixed in [magento/magento2#17213](https://github.com/magento/magento2/pull/17213))
    * [#14593](https://github.com/magento/magento2/issues/14593) -- Press Esc Key on modal generate a jquery UI error (fixed in [magento/magento2#17223](https://github.com/magento/magento2/pull/17223))
    * [#14476](https://github.com/magento/magento2/issues/14476) -- Mobile device style groups incorrect order in _responsive.less (fixed in [magento/magento2#17240](https://github.com/magento/magento2/pull/17240))
    * [#16243](https://github.com/magento/magento2/issues/16243) -- Integration test ProcessCronQueueObserverTest.php succeeds regardless of magento config fixture (fixed in [magento/magento2#17192](https://github.com/magento/magento2/pull/17192))
    * [#15308](https://github.com/magento/magento2/issues/15308) -- extraneous margins on product list and product list items (fixed in [magento/magento2#17379](https://github.com/magento/magento2/pull/17379))
    * [#15660](https://github.com/magento/magento2/issues/15660) -- Wrong order amount on dashboard on Last orders listing when having more than one website with different currencies (fixed in [magento/magento2#15677](https://github.com/magento/magento2/pull/15677))
    * [#13768](https://github.com/magento/magento2/issues/13768) -- Expired backend password - Attention: Something went wrong (fixed in [magento/magento2#17091](https://github.com/magento/magento2/pull/17091))
* GitHub pull requests:
    * [magento/magento2#14665](https://github.com/magento/magento2/pull/14665) -- [Backport 2.1 of PR-13802] Mini cart - fix issue in product title with special chars. (by @ampulos)
    * [magento/magento2#14673](https://github.com/magento/magento2/pull/14673) -- [Backport] Fix issue #13010. Check if product is assigned to current website (by @afirlejczyk)
    * [magento/magento2#14680](https://github.com/magento/magento2/pull/14680) -- [Backport] Checkout page - Fix tooltip position on mobile devices (by @ihor-sviziev)
    * [magento/magento2#14471](https://github.com/magento/magento2/pull/14471) -- magento/magento2#14465 Fix empty changelog tables after MySQL restart. (by @swnsma)
    * [magento/magento2#14711](https://github.com/magento/magento2/pull/14711) -- added GNU Free Font to be used by sales PDFs (by @rossmc)
    * [magento/magento2#14736](https://github.com/magento/magento2/pull/14736) -- [backport] magento/magento2#14669: Css class "empty" is always present on minicart dropdown (by @Karlasa)
    * [magento/magento2#14738](https://github.com/magento/magento2/pull/14738) -- [backport] #14716 Fix - minicart label fixed size issue (by @Karlasa)
    * [magento/magento2#14770](https://github.com/magento/magento2/pull/14770) -- [Backport] Add expanded documentation to AdapterInterface::update (by @navarr)
    * [magento/magento2#14791](https://github.com/magento/magento2/pull/14791) -- Update Readme file for magento2 repository (by @sidolov)
    * [magento/magento2#14845](https://github.com/magento/magento2/pull/14845) -- Updated readme.md file 2.1-develop (by @sidolov)
    * [magento/magento2#14894](https://github.com/magento/magento2/pull/14894) -- [Backport] Fix aggregations use statements and return values (by @rogyar)
    * [magento/magento2#14901](https://github.com/magento/magento2/pull/14901) -- [Backport] Refactoring: remove unuseful temporary variable (by @rogyar)
    * [magento/magento2#14899](https://github.com/magento/magento2/pull/14899) -- [Backport] Disable add to cart button when redirect to cart enabled (by @rogyar)
    * [magento/magento2#14911](https://github.com/magento/magento2/pull/14911) -- [Backport] Switch updatecart qty input validators to dynamic (by @rogyar)
    * [magento/magento2#14902](https://github.com/magento/magento2/pull/14902) -- [Backport] Prices aren't readable when using custom price symbol (by @rogyar)
    * [magento/magento2#14903](https://github.com/magento/magento2/pull/14903) -- [Backport] Pass parameter for export button url (by @rogyar)
    * [magento/magento2#14922](https://github.com/magento/magento2/pull/14922) -- [Backport] Customer observer name typo fix (by @rogyar)
    * [magento/magento2#14940](https://github.com/magento/magento2/pull/14940) -- [Backport] Fix faulty admin spinner animation (by @rogyar)
    * [magento/magento2#14757](https://github.com/magento/magento2/pull/14757) -- Preserve user group id when using /V1/customers/:customerId (PUT) (by @ferrazzuk)
    * [magento/magento2#15025](https://github.com/magento/magento2/pull/15025) -- [Backport] Minicart should require dropdownDialog (by @rogyar)
    * [magento/magento2#15093](https://github.com/magento/magento2/pull/15093) -- [Backport] Changed return type of addToCartPostParams to array (by @rogyar)
    * [magento/magento2#15099](https://github.com/magento/magento2/pull/15099) -- [Backport] Removed extra spaces from language file (by @sergiy-v)
    * [magento/magento2#15105](https://github.com/magento/magento2/pull/15105) -- [Backport] "Add Block Names to Hints" config setting to represent what it actually does (by @sergiy-v)
    * [magento/magento2#15108](https://github.com/magento/magento2/pull/15108) -- [Backport] Fix typo in less button definition  (by @sergiy-v)
    * [magento/magento2#15109](https://github.com/magento/magento2/pull/15109) -- [Backport] Fixed typos in .less files (by @sergiy-v)
    * [magento/magento2#15066](https://github.com/magento/magento2/pull/15066) -- [Backport] Add statement to 'beforeSave' method to allow app:config:import (by @rogyar)
    * [magento/magento2#15102](https://github.com/magento/magento2/pull/15102) -- [Backport] Move customer.account.dashboard.info.extra block to contact information (by @sergiy-v)
    * [magento/magento2#15106](https://github.com/magento/magento2/pull/15106) -- [Backport] use "Module_Name::template/path" format instead of using template/path (by @sergiy-v)
    * [magento/magento2#15138](https://github.com/magento/magento2/pull/15138) -- [Backport] Add concrete type hints for product and category resources (by @rogyar)
    * [magento/magento2#15146](https://github.com/magento/magento2/pull/15146) -- [Backport] Fix typo in input type variable name (by @dmytro-ch)
    * [magento/magento2#15091](https://github.com/magento/magento2/pull/15091) -- [Backport] Fix infinite checkout loader on a script error (by @rogyar)
    * [magento/magento2#15101](https://github.com/magento/magento2/pull/15101) -- [Backport] FIX for issue#14855 - Adding an * to do a customer search (by @sergiy-v)
    * [magento/magento2#15104](https://github.com/magento/magento2/pull/15104) -- [Backport] Change 'Update'-button visibility on change qty event (by @sergiy-v)
    * [magento/magento2#15094](https://github.com/magento/magento2/pull/15094) -- [Backport] Duplicate Order Confirmation Emails for PayPal Express checkout order (by @rogyar)
    * [magento/magento2#15103](https://github.com/magento/magento2/pull/15103) -- [Backport] Removed extra close tag (by @sergiy-v)
    * [magento/magento2#15169](https://github.com/magento/magento2/pull/15169) -- Swatches module file was missing in 2.1 (by @kaushik-chavda)
    * [magento/magento2#15183](https://github.com/magento/magento2/pull/15183) -- [Backport] Corrected param in docblock (by @rogyar)
    * [magento/magento2#15184](https://github.com/magento/magento2/pull/15184) -- [Backport] Removed unused class declaration and code (by @dmytro-ch)
    * [magento/magento2#15157](https://github.com/magento/magento2/pull/15157) -- [Backport] Fix typo in doc for updateSpecificCoupons (by @rogyar)
    * [magento/magento2#15176](https://github.com/magento/magento2/pull/15176) -- Fixed format of purchase date in order grid again. (by @hostep)
    * [magento/magento2#15203](https://github.com/magento/magento2/pull/15203) -- [Backport 2.1] Fix for displaying a negative price for a custom option (by @dverkade)
    * [magento/magento2#15240](https://github.com/magento/magento2/pull/15240) -- Remove unused namespace from ui export (by @yuriyDne)
    * [magento/magento2#15221](https://github.com/magento/magento2/pull/15221) -- [Backport] Translate action Label (by @rogyar)
    * [magento/magento2#15222](https://github.com/magento/magento2/pull/15222) -- [Backport] Fixed datepicker problem when using non en-US locale. (by @rogyar)
    * [magento/magento2#15159](https://github.com/magento/magento2/pull/15159) -- [Backport] Default schedule config for sitemap_generate job added (by @rogyar)
    * [magento/magento2#15235](https://github.com/magento/magento2/pull/15235) -- [Backport] Fixed double space typo (by @VitaliyBoyko)
    * [magento/magento2#15237](https://github.com/magento/magento2/pull/15237) -- [Backport] Changed constructor typo in Javascript class (by @VitaliyBoyko)
    * [magento/magento2#15233](https://github.com/magento/magento2/pull/15233) -- [Backport] Fix typo in design rule hint message (by @VitaliyBoyko)
    * [magento/magento2#15234](https://github.com/magento/magento2/pull/15234) -- [Backport] Add missing translations in Magento_UI (by @VitaliyBoyko)
    * [magento/magento2#15238](https://github.com/magento/magento2/pull/15238) -- [Backport] Fixed php notice when invalid ui_component config is used (by @VitaliyBoyko)
    * [magento/magento2#15038](https://github.com/magento/magento2/pull/15038) -- [2.1-develop][Backport] Transport variable can not be altered in email_invoice_set_template_vars_before Event (by @gwharton)
    * [magento/magento2#15298](https://github.com/magento/magento2/pull/15298) -- [Backport] Fix typos in variable names (by @dmytro-ch)
    * [magento/magento2#15324](https://github.com/magento/magento2/pull/15324) -- [Backport] Added hyphenation cutting edge to cutting-edge (by @rogyar)
    * [magento/magento2#15299](https://github.com/magento/magento2/pull/15299) -- [Backport] Fix typos in PHPDocs and comments (by @dmytro-ch)
    * [magento/magento2#15375](https://github.com/magento/magento2/pull/15375) -- [Backport] Fix typo in securityCheckers array (by @VitaliyBoyko)
    * [magento/magento2#15363](https://github.com/magento/magento2/pull/15363) -- [Backport] Alignment Array assignment (by @VitaliyBoyko)
    * [magento/magento2#15385](https://github.com/magento/magento2/pull/15385) -- [Backport] Unused variable removed (by @VitaliyBoyko)
    * [magento/magento2#15380](https://github.com/magento/magento2/pull/15380) -- [Backport] Fix incorrect phpdoc return type (by @VitaliyBoyko)
    * [magento/magento2#15377](https://github.com/magento/magento2/pull/15377) -- [Backport] Typo in SSL port number (by @VitaliyBoyko)
    * [magento/magento2#15394](https://github.com/magento/magento2/pull/15394) -- [Backport] Remove non-existing argument (by @dmytro-ch)
    * [magento/magento2#15396](https://github.com/magento/magento2/pull/15396) -- [Backport] Fixed typo mistake in function comment (by @dmytro-ch)
    * [magento/magento2#15309](https://github.com/magento/magento2/pull/15309) -- [Backport] Eliminate usage of "else" statements (by @rogyar)
    * [magento/magento2#15412](https://github.com/magento/magento2/pull/15412) -- [Backport-2.1] Fixed typo in MagentoUi abstract.js (by @VitaliyBoyko)
    * [magento/magento2#15392](https://github.com/magento/magento2/pull/15392) -- [Backport] Add 'const' type support to layout arguments (by @IgorVitol)
    * [magento/magento2#15318](https://github.com/magento/magento2/pull/15318) -- [Backport] Allow configuring min and max dates for date picker component (by @rogyar)
    * [magento/magento2#15407](https://github.com/magento/magento2/pull/15407) -- [Backport] Fix to allow use decimals less then 1 in subproducts qty (by @rogyar)
    * [magento/magento2#15436](https://github.com/magento/magento2/pull/15436) -- [Backport] Removed redundant else statement (by @rogyar)
    * [magento/magento2#15444](https://github.com/magento/magento2/pull/15444) -- [Backport] Added language translation for message string (by @Yogeshks)
    * [magento/magento2#15465](https://github.com/magento/magento2/pull/15465) -- [Backport] typo correction (by @mzeis)
    * [magento/magento2#13756](https://github.com/magento/magento2/pull/13756) -- [backport 2.1] Category\Collection::joinUrlRewrite should use the store set on the collection (by @alepane21)
    * [magento/magento2#15548](https://github.com/magento/magento2/pull/15548) -- [Backport 2.1]Fix typos in Multishipping and User module (by @VitaliyBoyko)
    * [magento/magento2#15445](https://github.com/magento/magento2/pull/15445) -- [Backport] Fix unnecessary recalculation of product list pricing (by @JeroenVanLeusden)
    * [magento/magento2#15522](https://github.com/magento/magento2/pull/15522) -- [Backlog] Add resetting of triggerRecollection flag (by @rogyar)
    * [magento/magento2#15563](https://github.com/magento/magento2/pull/15563) -- [Backport] Correct functions return statement (by @rogyar)
    * [magento/magento2#15619](https://github.com/magento/magento2/pull/15619) -- Fix incorrect type hinting in PHPDocs (by @dmytro-ch)
    * [magento/magento2#15616](https://github.com/magento/magento2/pull/15616) -- [Backport] Removed comma(,) from translate attribute (by @dmytro-ch)
    * [magento/magento2#15648](https://github.com/magento/magento2/pull/15648) -- [Backport] Fixed typo error (by @vgelani)
    * [magento/magento2#15668](https://github.com/magento/magento2/pull/15668) -- [Backport] set correct annotation (by @vgelani)
    * [magento/magento2#15657](https://github.com/magento/magento2/pull/15657) -- [Backport] Fixed set template syntax issue (by @gelanivishal)
    * [magento/magento2#15669](https://github.com/magento/magento2/pull/15669) -- [Backport] Wrong annotation in _toOptionArray - magento/framework/Data/ (by @gelanivishal)
    * [magento/magento2#15700](https://github.com/magento/magento2/pull/15700) -- [Backport] Fix HTML syntax in report.phtml error template (by @dmytro-ch)
    * [magento/magento2#15702](https://github.com/magento/magento2/pull/15702) -- [Backport] Remove extra space and format the code in translation file (by @dmytro-ch)
    * [magento/magento2#15708](https://github.com/magento/magento2/pull/15708) -- [Backport] Typo correction (by @dmytro-ch)
    * [magento/magento2#15710](https://github.com/magento/magento2/pull/15710) -- [Backport] Fix dynamical assigned property as it wasn't assigned to an existing one (by @dmytro-ch)
    * [magento/magento2#15711](https://github.com/magento/magento2/pull/15711) -- [Backport] Use stored value of method instead of calling same method again. (by @dmytro-ch)
    * [magento/magento2#15714](https://github.com/magento/magento2/pull/15714) -- [Backport] [Resolved : Menu widget submenu alignment #7897] (by @dmytro-ch)
    * [magento/magento2#15716](https://github.com/magento/magento2/pull/15716) -- [BACKPORT 2.1 #15695] Fixed a couple of typos (by @dverkade)
    * [magento/magento2#15719](https://github.com/magento/magento2/pull/15719) -- [Backport 2.1] Fixed return type of wishlist's getImageData in DocBlock (by @rogyar)
    * [magento/magento2#15724](https://github.com/magento/magento2/pull/15724) -- [Backport] Moved css from media #TODO (by @gelanivishal)
    * [magento/magento2#15736](https://github.com/magento/magento2/pull/15736) -- [Backwardport] Refactor javascript code of  button split widget (by @vijay-wagento)
    * [magento/magento2#15725](https://github.com/magento/magento2/pull/15725) -- [Backport] Updated font-size variable and standardize #ToDo UI (by @gelanivishal)
    * [magento/magento2#15739](https://github.com/magento/magento2/pull/15739) -- [Backport] fix: support multiple minisearch widget instances (by @vijay-wagento)
    * [magento/magento2#15805](https://github.com/magento/magento2/pull/15805) -- [Backport] Make necessary space. #5 (by @chirag-wagento)
    * [magento/magento2#15810](https://github.com/magento/magento2/pull/15810) -- [Backport] fix: support multiple minisearch widget instances (by @DanielRuf)
    * [magento/magento2#15573](https://github.com/magento/magento2/pull/15573) -- [Backport] Docblock typo fixes (by @rogyar)
    * [magento/magento2#15693](https://github.com/magento/magento2/pull/15693) -- [Backport 2.1] Fix minor issues in ui export converter classes (by @dmytro-ch)
    * [magento/magento2#15756](https://github.com/magento/magento2/pull/15756) -- [Backport] Error 500 in Module Manager (by @vijay-wagento)
    * [magento/magento2#15814](https://github.com/magento/magento2/pull/15814) -- [Backport] fix typo for setCateroryIds (by @viral-wagento)
    * [magento/magento2#15816](https://github.com/magento/magento2/pull/15816) -- [BackPort]15319 : misleading data-container in product list (by @viral-wagento)
    * [magento/magento2#15817](https://github.com/magento/magento2/pull/15817) -- [Backport] Fix typo in Image::open exception message (by @rahul-kachhadiya)
    * [magento/magento2#15767](https://github.com/magento/magento2/pull/15767) -- [Backport] FIX fo rissue #15510 - First PDF download / export after login (by @sanjay-wagento)
    * [magento/magento2#15796](https://github.com/magento/magento2/pull/15796) -- [Backport] [Resolved : Styling select by changing less variables in Luma theme (by @hitesh-wagento)
    * [magento/magento2#15776](https://github.com/magento/magento2/pull/15776) -- [Backport] Added additional headers for avoiding customer data caching (by @rogyar)
    * [magento/magento2#15801](https://github.com/magento/magento2/pull/15801) -- [Backport] Prevent multiple add-to-cart initializations in case of ajax loaded product listing (by @viral-wagento)
    * [magento/magento2#15797](https://github.com/magento/magento2/pull/15797) -- [Backport] Removed unnecessary css. #3 (by @chirag-wagento)
    * [magento/magento2#15841](https://github.com/magento/magento2/pull/15841) -- [Backport] Fix for issue 911 found on MSI project - Cannot read property source_ #15 (by @chirag-wagento)
    * [magento/magento2#15855](https://github.com/magento/magento2/pull/15855) -- [Backport 2.1] Fixed return type hinting in DocBlocks for Wishlist module (by @rogyar)
    * [magento/magento2#15290](https://github.com/magento/magento2/pull/15290) -- [Backport] Fix typo in database column comment (by @VitaliyBoyko)
    * [magento/magento2#15866](https://github.com/magento/magento2/pull/15866) -- [Backport] Update webapi.xml to fix typo (by @dmytro-ch)
    * [magento/magento2#15887](https://github.com/magento/magento2/pull/15887) -- [Backport-2.1] Added language translation in template files (by @rahul-kachhadiya)
    * [magento/magento2#15920](https://github.com/magento/magento2/pull/15920) -- [Backport] chore: remove unused less import (by @DanielRuf)
    * [magento/magento2#15924](https://github.com/magento/magento2/pull/15924) -- [Backport] Added spanish Bolivia locale to allowedLocales list (by @dmytro-ch)
    * [magento/magento2#15925](https://github.com/magento/magento2/pull/15925) -- [Backport] Format code (by @dmytro-ch)
    * [magento/magento2#15926](https://github.com/magento/magento2/pull/15926) -- [Backport] precision for price overriding by js (by @dmytro-ch)
    * [magento/magento2#15927](https://github.com/magento/magento2/pull/15927) -- [Backport]  [#14089] Add Malaysian Locale Code (by @dmytro-ch)
    * [magento/magento2#15933](https://github.com/magento/magento2/pull/15933) -- Do not display anchor if admin submenu has no children (by @rogyar)
    * [magento/magento2#15945](https://github.com/magento/magento2/pull/15945) -- [Backport] ISSUE-11477 - fixed Swagger response for searchCriteria - added zero  (by @vgelani)
    * [magento/magento2#15943](https://github.com/magento/magento2/pull/15943) -- [Backport] Wrong annotation in _toOptionArray : lib\internal\Magento\Framework\D (by @vgelani)
    * [magento/magento2#15949](https://github.com/magento/magento2/pull/15949) -- [Backport]No need to pass method parameter as method definition does not requir (by @saurabh-parekh)
    * [magento/magento2#15643](https://github.com/magento/magento2/pull/15643) -- [Backport] Fixed Purchased Order Form button should visible properly (by @vgelani)
    * [magento/magento2#15874](https://github.com/magento/magento2/pull/15874) -- [Backport-2.1] Trim username on customer account login page (by @dankhrapiyush)
    * [magento/magento2#15880](https://github.com/magento/magento2/pull/15880) -- [Backport] [Resolved : limiter float too generic] (by @hitesh-wagento)
    * [magento/magento2#15915](https://github.com/magento/magento2/pull/15915) -- [Backport] Fixes in catalog component blocks [2.1-develop] (by @chirag-wagento)
    * [magento/magento2#15917](https://github.com/magento/magento2/pull/15917) -- [Backport] [Resolved : Changing @tab-content__border variable has no effect in B (by @hitesh-wagento)
    * [magento/magento2#16026](https://github.com/magento/magento2/pull/16026) -- Fixed a minor styling issue in page footer in order to align the columns indentations (by @dmytro-ch)
    * [magento/magento2#16024](https://github.com/magento/magento2/pull/16024) -- [Backport 2.1] Wishlist: Remove unnecessary parameter from invoking toHtml() method (by @rogyar)
    * [magento/magento2#16031](https://github.com/magento/magento2/pull/16031) -- [Backport] #13899 Solve Canada Zip Code pattern  (by @hitesh-wagento)
    * [magento/magento2#16043](https://github.com/magento/magento2/pull/16043) -- [Backport] fix for dropdown toggle icon in cart (by @chirag-wagento)
    * [magento/magento2#15829](https://github.com/magento/magento2/pull/15829) -- [Backport] Resolve Error While Trying To Load Quote Item Collection Using Magent #2 (by @neeta-wagento)
    * [magento/magento2#15834](https://github.com/magento/magento2/pull/15834) -- [BackPort] Resolve Knockout non-unique elements id in console error (by @viral-wagento)
    * [magento/magento2#15862](https://github.com/magento/magento2/pull/15862) -- [Backport] Move buttons definition to separate file (by @rahul-kachhadiya)
    * [magento/magento2#16067](https://github.com/magento/magento2/pull/16067) -- [Backport 2.1] Added unit test for captcha string resolver (by @rogyar)
    * [magento/magento2#16081](https://github.com/magento/magento2/pull/16081) -- Adding support for variadic arguments fro method in generated proxy c (by @vgelani)
    * [magento/magento2#16037](https://github.com/magento/magento2/pull/16037) -- [Backport] Fix issue #15832 (by @chirag-wagento)
    * [magento/magento2#16102](https://github.com/magento/magento2/pull/16102) -- [Backport] fixed word typo (by @hitesh-wagento)
    * [magento/magento2#16130](https://github.com/magento/magento2/pull/16130) -- [Backport] Disabling sorting in glob and scandir functions for better performance (by @lfluvisotto)
    * [magento/magento2#16111](https://github.com/magento/magento2/pull/16111) -- [Backport]  Correct typo correction js files (by @hitesh-wagento)
    * [magento/magento2#16039](https://github.com/magento/magento2/pull/16039) -- [Backport] bugfix checkout page cart icon color (by @chirag-wagento)
    * [magento/magento2#16162](https://github.com/magento/magento2/pull/16162) -- [Backport 2.1] Captcha: Added unit test for CheckRegisterCheckoutObserver (by @rogyar)
    * [magento/magento2#15863](https://github.com/magento/magento2/pull/15863) -- [Backport] Refactored javascript code of admin notification modal popup (by @rahul-kachhadiya)
    * [magento/magento2#15236](https://github.com/magento/magento2/pull/15236) -- [Backport] Add price calculation improvement for product option value price (by @VitaliyBoyko)
    * [magento/magento2#15287](https://github.com/magento/magento2/pull/15287) -- [Backport] Handle empty or incorrect lines in a language CSV (by @VitaliyBoyko)
    * [magento/magento2#15289](https://github.com/magento/magento2/pull/15289) -- [Backport] Naming collision in Javascript ui registry (backend) (by @VitaliyBoyko)
    * [magento/magento2#15722](https://github.com/magento/magento2/pull/15722) -- [Backport] Fix Magento_ImportExport not supporting unicode characters in column names (by @tdgroot)
    * [magento/magento2#15699](https://github.com/magento/magento2/pull/15699) -- [Backport] Variant product image in sidebar wishlist block (by @dmytro-ch)
    * [magento/magento2#15821](https://github.com/magento/magento2/pull/15821) -- [Backport] #14063 - Wrong invoice prefix in multistore setup due to default stor (by @sanjay-wagento)
    * [magento/magento2#15860](https://github.com/magento/magento2/pull/15860) -- [Backport] ISSUE-14747 Newsletter subscription confirmation message does not dis (by @rahul-kachhadiya)
    * [magento/magento2#15727](https://github.com/magento/magento2/pull/15727) -- [Backport] Feature space between category page (by @sanjay-wagento)
    * [magento/magento2#16226](https://github.com/magento/magento2/pull/16226) -- Navigation dropdown caret icon. (by @tejash-wagento)
    * [magento/magento2#16255](https://github.com/magento/magento2/pull/16255) -- magento/magento2#15255 unlock customer after password reset (by @vgelani)
    * [magento/magento2#16294](https://github.com/magento/magento2/pull/16294) -- Removed duplicate line and added comment on variable (by @vgelani)
    * [magento/magento2#16270](https://github.com/magento/magento2/pull/16270) -- Refactor validate code in Tax module (by @gelanivishal)
    * [magento/magento2#16264](https://github.com/magento/magento2/pull/16264) -- [Backport] Fix duplicate element id issue. (by @chirag-wagento)
    * [magento/magento2#16281](https://github.com/magento/magento2/pull/16281) -- [Backport] Submitting search form (mini) with enter key fires event handlers bound by jquery twice (by @vgelani)
    * [magento/magento2#16297](https://github.com/magento/magento2/pull/16297) -- [Backport-2.1] Trim email address in customer account create and login form (by @dankhrapiyush)
    * [magento/magento2#16319](https://github.com/magento/magento2/pull/16319) -- [Backport] Correct sentence in comment section in class file. (by @NamrataChangani)
    * [magento/magento2#16325](https://github.com/magento/magento2/pull/16325) -- [Backport] Fixed typo error (by @vgelani)
    * [magento/magento2#16287](https://github.com/magento/magento2/pull/16287) -- [Backport] Solve overlapping Issue on  category page. (by @chirag-wagento)
    * [magento/magento2#16347](https://github.com/magento/magento2/pull/16347) -- [Backport 2.1] Captcha: Added integration test for checking admin login attempts cleanup (by @rogyar)
    * [magento/magento2#16359](https://github.com/magento/magento2/pull/16359) --  [Backport] array_push(...) calls behaving as '$array[] = ...', $array[] = works faster than invoking functions in PHP (by @lfluvisotto)
    * [magento/magento2#16366](https://github.com/magento/magento2/pull/16366) -- [Backport] PHPDoc (by @lfluvisotto)
    * [magento/magento2#16280](https://github.com/magento/magento2/pull/16280) -- MAGETWO-61209: Backport - Fixed issue #7379 with mage/calendar when setting `numberOfM (by @vasilii-b)
    * [magento/magento2#16352](https://github.com/magento/magento2/pull/16352) -- [Backport] Invoice grid shows wrong shipping & handling for partial items invoice. It shows order's shipping & handling instead if invoiced shipping& handling charge (by @gelanivishal)
    * [magento/magento2#16403](https://github.com/magento/magento2/pull/16403) -- [Backport 2.1] Captcha: Added integration tests for checking customer login attempts cleanup (by @rogyar)
    * [magento/magento2#16467](https://github.com/magento/magento2/pull/16467) -- [Backport] Fix $useCache for container child blocks (by @gelanivishal)
    * [magento/magento2#16475](https://github.com/magento/magento2/pull/16475) -- [Backport] Declare module namespace before template path name(Magento_Sales::order/info.phtml). (by @gelanivishal)
    * [magento/magento2#16229](https://github.com/magento/magento2/pull/16229) -- [Backport] #16079 translation possibility for moreButtonText (by @Karlasa)
    * [magento/magento2#16392](https://github.com/magento/magento2/pull/16392) -- [Backport]Fixes updating wishlist item if an item object is passed instead its id. (by @eduard13)
    * [magento/magento2#16491](https://github.com/magento/magento2/pull/16491) -- Fix for #8222 (by @gelanivishal)
    * [magento/magento2#16547](https://github.com/magento/magento2/pull/16547) -- Correct return type of methods and typo correction (by @gelanivishal)
    * [magento/magento2#16577](https://github.com/magento/magento2/pull/16577) -- [Backport] Declare module namespace before template path name (by @mageprince)
    * [magento/magento2#16586](https://github.com/magento/magento2/pull/16586) -- [Backport] Declare module namespace before template path name(Magento_Sales::order/creditmemo.phtml). (by @mageprince)
    * [magento/magento2#16493](https://github.com/magento/magento2/pull/16493) -- [Backport] Prevent layout cache corruption in edge case (by @gelanivishal)
    * [magento/magento2#16601](https://github.com/magento/magento2/pull/16601) -- [2.1-develop][BackPort] Fixed backwards incompatible change to Transport variable event parameters (by @gwharton)
    * [magento/magento2#16634](https://github.com/magento/magento2/pull/16634) -- [Backport] Updated SynonymGroup.xml (by @sanganinamrata)
    * [magento/magento2#16643](https://github.com/magento/magento2/pull/16643) -- [Backport] Variable as a method parameter might be overridden by the loop (by @lfluvisotto)
    * [magento/magento2#16665](https://github.com/magento/magento2/pull/16665) -- [Backport] 7399-clickableOverlay-less-fix - added pointer-events rule to .modal- (by @mageprince)
    * [magento/magento2#16667](https://github.com/magento/magento2/pull/16667) -- [Backport] Improve retrieval of first array element (by @mageprince)
    * [magento/magento2#16671](https://github.com/magento/magento2/pull/16671) -- [Backport] Removed unused class from forms less file. (by @mageprince)
    * [magento/magento2#16585](https://github.com/magento/magento2/pull/16585) -- [Backport] Remove the timezone from the date when retrieving the current month from a UTC timestamp. (by @mageprince)
    * [magento/magento2#16632](https://github.com/magento/magento2/pull/16632) -- [Backport] Small refactoring to better code readability (by @ronak2ram)
    * [magento/magento2#16720](https://github.com/magento/magento2/pull/16720) -- [Backport] Fix for #12081: missing translations in the js-translations.json (by @sanganinamrata)
    * [magento/magento2#16735](https://github.com/magento/magento2/pull/16735) -- [backport] #16559 fix icon color variable naming  (by @Karlasa)
    * [magento/magento2#16731](https://github.com/magento/magento2/pull/16731) -- [Backport] Removed extra code (by @gelanivishal)
    * [magento/magento2#16550](https://github.com/magento/magento2/pull/16550) -- [Backport #16458] Add missing showInStore attributes (by @aschrammel)
    * [magento/magento2#16759](https://github.com/magento/magento2/pull/16759) -- [Backport] Declare module namespace before template path in Magento_Theme,  Magento_Newsletter and Magento_Tax (by @mageprince)
    * [magento/magento2#16760](https://github.com/magento/magento2/pull/16760) -- [Backport] Declare module namespace before template path in Magento_Sales and Magento_Paypal (by @mageprince)
    * [magento/magento2#14380](https://github.com/magento/magento2/pull/14380) -- [Backport 2.1] Issue 14351: Product import doesn't change `Enable Qty Increments` field (by @simpleadm)
    * [magento/magento2#16526](https://github.com/magento/magento2/pull/16526) -- [Backport : Changed password placeholder text in checkout page] (by @hitesh-wagento)
    * [magento/magento2#16761](https://github.com/magento/magento2/pull/16761) -- [Backport] Fix function unnecessarily called multiple time (by @gelanivishal)
    * [magento/magento2#16755](https://github.com/magento/magento2/pull/16755) -- [backport] #16716 fix _utilities.less font-size issue (by @Karlasa)
    * [magento/magento2#16825](https://github.com/magento/magento2/pull/16825) -- [Backport] Fixed type hints and docs for Downloadable Samples block (by @ronak2ram)
    * [magento/magento2#16836](https://github.com/magento/magento2/pull/16836) -- [Backport] Fixed typo in SynonymGroupRepositoryInterface (by @mage2pratik)
    * [magento/magento2#16832](https://github.com/magento/magento2/pull/16832) -- [Backport] Fixed set template syntax issue (by @gelanivishal)
    * [magento/magento2#16842](https://github.com/magento/magento2/pull/16842) -- [Backport] chore: remove extraneous cursor property (by @gelanivishal)
    * [magento/magento2#16839](https://github.com/magento/magento2/pull/16839) -- [backport] fix #16764 Rating Star issue on Product detail Page. (by @Karlasa)
    * [magento/magento2#16846](https://github.com/magento/magento2/pull/16846) -- [Backport] Create ability to set is_visible_on_front to order status history comment (by @ronak2ram)
    * [magento/magento2#16770](https://github.com/magento/magento2/pull/16770) -- [Backport] Added translation function for Magento_Braintree module's template file. (by @sanganinamrata)
    * [magento/magento2#16802](https://github.com/magento/magento2/pull/16802) -- [Backport] magento/magento2#16685 Updated security issues details (by @quisse)
    * [magento/magento2#16828](https://github.com/magento/magento2/pull/16828) -- [Backport] FIX for apparently random API failures while using array types (by @ronak2ram)
    * [magento/magento2#16835](https://github.com/magento/magento2/pull/16835) -- [Backport] Smallest codestyle fix in Option/Type/Text.php (by @mage2pratik)
    * [magento/magento2#16797](https://github.com/magento/magento2/pull/16797) -- [Backport] Resolved : no navigation-level0-item__hover__color #15848 (by @hitesh-wagento)
    * [magento/magento2#16364](https://github.com/magento/magento2/pull/16364) -- [Backport] Fix case mismatch call (class/method) (by @lfluvisotto)
    * [magento/magento2#16811](https://github.com/magento/magento2/pull/16811) -- [Backport] FIXED - appended payment code to ID field to make it unique (by @mage2pratik)
    * [magento/magento2#16830](https://github.com/magento/magento2/pull/16830) -- [Backport] Fix responsive tables showing broken heading (by @ronak2ram)
    * [magento/magento2#16877](https://github.com/magento/magento2/pull/16877) -- [Backport 2.1] Captcha: Added unit test for CheckGuestCheckoutObserver (by @rogyar)
    * [magento/magento2#16801](https://github.com/magento/magento2/pull/16801) -- [Backport] magento/magento2#16184: Fix type error in payment void method (by @gelanivishal)
    * [magento/magento2#16812](https://github.com/magento/magento2/pull/16812) -- [Backport] Issue 15467 where a configuration sku gets deleted but is still saved (by @mage2pratik)
    * [magento/magento2#16917](https://github.com/magento/magento2/pull/16917) -- [Backport] Issue 5316 (by @ronak2ram)
    * [magento/magento2#16923](https://github.com/magento/magento2/pull/16923) -- [Backport] Fix docBlock for hasInvoices(), hasShipments(), hasCreditmemos() (by @eduard13)
    * [magento/magento2#16920](https://github.com/magento/magento2/pull/16920) -- [Backport] Admin tabs order not working properly (by @mage2pratik)
    * [magento/magento2#16945](https://github.com/magento/magento2/pull/16945) -- [Backport] Fix Sort by Product Name (by @ihor-sviziev)
    * [magento/magento2#16948](https://github.com/magento/magento2/pull/16948) -- [Backport] Fix meta title property (by @ronak2ram)
    * [magento/magento2#16986](https://github.com/magento/magento2/pull/16986) -- [Backport] Trim issue on customer confirmation form (by @gelanivishal)
    * [magento/magento2#17003](https://github.com/magento/magento2/pull/17003) -- [Backport] Remove unused comments from _initDiscount() function (by @mageprince)
    * [magento/magento2#17018](https://github.com/magento/magento2/pull/17018) -- [Backport] Wrong namespace defined in compare.phtml (by @ronak2ram)
    * [magento/magento2#17015](https://github.com/magento/magento2/pull/17015) -- [Backport] Fixes white color coding standard. (by @chirag-wagento)
    * [magento/magento2#16702](https://github.com/magento/magento2/pull/16702) -- [Backport] MAGETWO-91411: Delete action in grid could be sent multiple times (by @novikor)
    * [magento/magento2#17022](https://github.com/magento/magento2/pull/17022) -- [Backport] Newsletter Label is broking on chinese Language like ??. (by @dasharath-wagento)
    * [magento/magento2#17057](https://github.com/magento/magento2/pull/17057) -- [backport] removed _responsive.less import from gallery.less (by @Karlasa)
    * [magento/magento2#17084](https://github.com/magento/magento2/pull/17084) -- [Backport] [FIX] Remove not used and empty template (by @mageprince)
    * [magento/magento2#17086](https://github.com/magento/magento2/pull/17086) -- [Backport] Act better on existing input focus instead of removing it (by @mageprince)
    * [magento/magento2#17085](https://github.com/magento/magento2/pull/17085) -- [Backport] [FIX] Remove not used variable in template (by @mageprince)
    * [magento/magento2#16809](https://github.com/magento/magento2/pull/16809) -- [Backport] Remove double semicolon from the style sheets. (by @gelanivishal)
    * [magento/magento2#17029](https://github.com/magento/magento2/pull/17029) -- [Backport] Fixes Black color coding standard. (by @chirag-wagento)
    * [magento/magento2#17088](https://github.com/magento/magento2/pull/17088) -- [Backport] Solved this issue : Drop down values are not showing in catalog produ (by @mageprince)
    * [magento/magento2#17089](https://github.com/magento/magento2/pull/17089) -- [Backport] Fixed comparison with 0 bug for TableRate shipping carrier (by @mageprince)
    * [magento/magento2#17092](https://github.com/magento/magento2/pull/17092) -- [Backport] Remove forced setting of cache_lifetime to false in constructor and set default cache_lifetime to 3600 (by @mageprince)
    * [magento/magento2#17111](https://github.com/magento/magento2/pull/17111) -- [Backport] Fixed widget template rendering issue while rewriting widget block (by @gelanivishal)
    * [magento/magento2#17087](https://github.com/magento/magento2/pull/17087) -- [Backport] Issues 13769. Fix wrong info about sent email in order sender. (by @mageprince)
    * [magento/magento2#17105](https://github.com/magento/magento2/pull/17105) -- [Backport] Fix negative basket total due to shipping tax residue (by @mage2pratik)
    * [magento/magento2#17139](https://github.com/magento/magento2/pull/17139) -- [Backport] Fixed a couple of spelling mistakes (by @mage2pratik)
    * [magento/magento2#16992](https://github.com/magento/magento2/pull/16992) -- [Backport] Refactor JavsScript for UrlRewrite module edit page. (by @chirag-wagento)
    * [magento/magento2#17090](https://github.com/magento/magento2/pull/17090) -- [Backport] Inconsistent Redirect in Admin Notification Controller (by @mageprince)
    * [magento/magento2#17159](https://github.com/magento/magento2/pull/17159) -- [Backport] Add Confirm Modal Width (by @gelanivishal)
    * [magento/magento2#17160](https://github.com/magento/magento2/pull/17160) -- [Backport] Improve "Invalid country code" error message on tax import (by @gelanivishal)
    * [magento/magento2#17136](https://github.com/magento/magento2/pull/17136) -- [Backport] Remove commented code (by @mage2pratik)
    * [magento/magento2#17157](https://github.com/magento/magento2/pull/17157) -- [Backport] Removed double occurrence of keywords in sentences. (by @gelanivishal)
    * [magento/magento2#17237](https://github.com/magento/magento2/pull/17237) -- [Backport] Remove commented code (by @tiagosampaio)
    * [magento/magento2#17239](https://github.com/magento/magento2/pull/17239) -- [Backport] Fix misprint ('_requesetd' > '_requested') (by @tiagosampaio)
    * [magento/magento2#16898](https://github.com/magento/magento2/pull/16898) -- [Backport] Prevent running SQL query on every item in the database when the quote is empty (by @mage2pratik)
    * [magento/magento2#17182](https://github.com/magento/magento2/pull/17182) -- [Backport] DOBISSUE date format changed after customer tries to register with sa (by @gelanivishal)
    * [magento/magento2#17260](https://github.com/magento/magento2/pull/17260) -- [Backport] Using interface instead of model directly (by @mage2pratik)
    * [magento/magento2#17241](https://github.com/magento/magento2/pull/17241) -- [Backport] Modify Report processor to return 500 (by @mageprince)
    * [magento/magento2#17242](https://github.com/magento/magento2/pull/17242) -- [Backport] Add VAT number to email source variables (by @mageprince)
    * [magento/magento2#17155](https://github.com/magento/magento2/pull/17155) -- [Backport] Set proper text-aligh for the <th> element of the Subtotal column in the Creditmemo email (by @TomashKhamlai)
    * [magento/magento2#17200](https://github.com/magento/magento2/pull/17200) -- [Backport] Fixed a grammatical error on the vault tooltip (by @mage2pratik)
    * [magento/magento2#17224](https://github.com/magento/magento2/pull/17224) -- [Backport] Credit memo email template file: fixing incorrect object type error (by @gelanivishal)
    * [magento/magento2#17243](https://github.com/magento/magento2/pull/17243) -- [Backport] [ISSUE-11140][BUGFIX] Skip store code admin from being detected in ca (by @mageprince)
    * [magento/magento2#17247](https://github.com/magento/magento2/pull/17247) -- [Backport] FIXED: FTP user and password strings urldecoded (by @mage2pratik)
    * [magento/magento2#17202](https://github.com/magento/magento2/pull/17202) -- [Backport] Update template.js (by @mage2pratik)
    * [magento/magento2#17216](https://github.com/magento/magento2/pull/17216) -- [Backport] Categories > Left menu > Item title space fix (by @mage2pratik)
    * [magento/magento2#17218](https://github.com/magento/magento2/pull/17218) -- [Backport] Broken Responsive Layout on Top page (by @mage2pratik)
    * [magento/magento2#17211](https://github.com/magento/magento2/pull/17211) -- [Backport] Fixed ability to set field config from layout xml #11302 (by @mageprince)
    * [magento/magento2#17212](https://github.com/magento/magento2/pull/17212) -- [Backport] Magento sets ISO invalid language code (by @mageprince)
    * [magento/magento2#17213](https://github.com/magento/magento2/pull/17213) -- [Backport 2.1] Fix "pattern" UI Component validation (by @mageprince)
    * [magento/magento2#17223](https://github.com/magento/magento2/pull/17223) -- [Backport] Fix for #14593 (second try #16431) (by @gelanivishal)
    * [magento/magento2#17240](https://github.com/magento/magento2/pull/17240) -- [Backport] Resolved : Mobile device style groups incorrect order (by @tiagosampaio)
    * [magento/magento2#17172](https://github.com/magento/magento2/pull/17172) -- Declare module namespace before template path name (by @mage2pratik)
    * [magento/magento2#17192](https://github.com/magento/magento2/pull/17192) -- [Backport] Filter test result collection with the cron job code defined in the c (by @gelanivishal)
    * [magento/magento2#17253](https://github.com/magento/magento2/pull/17253) -- [Backport] fix: change "My Dashboard" to "My Account", fixes #16007 (by @gelanivishal)
    * [magento/magento2#16619](https://github.com/magento/magento2/pull/16619) -- [Backport] Login with wishlist raise report after logout (by @ronak2ram)
    * [magento/magento2#16911](https://github.com/magento/magento2/pull/16911) -- [Backport] Fixed extends and removed unnecessary variables (by @gelanivishal)
    * [magento/magento2#17179](https://github.com/magento/magento2/pull/17179) -- [Backport] Fix newsletter subscription behaviour for registered customer.  (by @mage2pratik)
    * [magento/magento2#17335](https://github.com/magento/magento2/pull/17335) -- fix: remove unused ID (by @DanielRuf)
    * [magento/magento2#17108](https://github.com/magento/magento2/pull/17108) -- [Backport] Use constant time string comparison in FormKey validator (by @gelanivishal)
    * [magento/magento2#17379](https://github.com/magento/magento2/pull/17379) -- [Backport] #15308 removed extraneous margin (by @gelanivishal)
    * [magento/magento2#15677](https://github.com/magento/magento2/pull/15677) -- [Backport] Wrong Last orders amount on dashboard #15660 (by @ankurvr)
    * [magento/magento2#17091](https://github.com/magento/magento2/pull/17091) -- [Backport] Issue-13768 Fixed error messages on admin user account page after redirect for force password change (by @mageprince)

2.1.14
=============
* GitHub issues:
    * [#7723](https://github.com/magento/magento2/issues/7723) -- Catalog rule contains-condition not saving multiple selection in 2.1.2 (fixed in [magento/magento2#13546](https://github.com/magento/magento2/pull/13546))
    * [#13214](https://github.com/magento/magento2/issues/13214) -- Not a correct displaying for Robots.txt (fixed in [magento/magento2#13550](https://github.com/magento/magento2/pull/13550))
    * [#13315](https://github.com/magento/magento2/issues/13315) -- Mobile "Payment Methods" step looks bad on mobile (fixed in [magento/magento2#13980](https://github.com/magento/magento2/pull/13980))
    * [#13474](https://github.com/magento/magento2/issues/13474) -- [2.1.10] Swagger not working for multistore installs?  (fixed in [magento/magento2#13486](https://github.com/magento/magento2/pull/13486))
    * [#4173](https://github.com/magento/magento2/issues/4173) -- Cron schedule bug (fixed in [magento/magento2#14096](https://github.com/magento/magento2/pull/14096))
    * [#5808](https://github.com/magento/magento2/issues/5808) -- [2.1.0] Problem on mobile when catalog gallery allowfullscreen is false (fixed in [magento/magento2#14098](https://github.com/magento/magento2/pull/14098))
    * [#6694](https://github.com/magento/magento2/issues/6694) -- Override zip_codes.xml (fixed in [magento/magento2#14117](https://github.com/magento/magento2/pull/14117))
    * [#10559](https://github.com/magento/magento2/issues/10559) -- Extending swatch functionality using javascript mixins does not work in Safari and MS Edge (fixed in [magento/magento2#12928](https://github.com/magento/magento2/pull/12928))
    * [#3489](https://github.com/magento/magento2/issues/3489) -- CURL Json POST (fixed in [magento/magento2#14151](https://github.com/magento/magento2/pull/14151))
    * [#5463](https://github.com/magento/magento2/issues/5463) -- The ability to store passwords using different hashing algorithms is limited (fixed in [magento/magento2#13886](https://github.com/magento/magento2/pull/13886))
    * [#3882](https://github.com/magento/magento2/issues/3882) -- An XML comment node as parameter in widget.xml fails with fatal error (fixed in [magento/magento2#14219](https://github.com/magento/magento2/pull/14219))
    * [#1931](https://github.com/magento/magento2/issues/1931) -- Can't cancel removal of a block or container in layout by setting remove attribute value to false (fixed in [magento/magento2#14198](https://github.com/magento/magento2/pull/14198))
    * [#7403](https://github.com/magento/magento2/issues/7403) -- JS Translation Regex leads to unexpected results and untranslatable strings (fixed in [magento/magento2#14349](https://github.com/magento/magento2/pull/14349))
    * [#7816](https://github.com/magento/magento2/issues/7816) -- Customer_account.xml file abused (fixed in [magento/magento2#14323](https://github.com/magento/magento2/pull/14323))
    * [#10700](https://github.com/magento/magento2/issues/10700) -- Magento 2 Admin panel show loading on each page (fixed in [magento/magento2#14417](https://github.com/magento/magento2/pull/14417))
    * [#11930](https://github.com/magento/magento2/issues/11930) -- setup:di:compile's generated cache files inaccessible by the web-server user (fixed in [magento/magento2#14417](https://github.com/magento/magento2/pull/14417))
    * [#14572](https://github.com/magento/magento2/issues/14572) -- Specify the table when adding field to filter for the collection Eav/Model/ResourceModel/Entity/Attribute/Option/Collection.php (fixed in [magento/magento2#14596](https://github.com/magento/magento2/pull/14596))
* GitHub pull requests:
    * [magento/magento2#13949](https://github.com/magento/magento2/pull/13949) -- Fix misnamed namespace (by @Ethan3600)
    * [magento/magento2#13545](https://github.com/magento/magento2/pull/13545) -- Backport of PR-5028 for Magento 2.1: Load jquery using requirejs to p (by @hostep)
    * [magento/magento2#13546](https://github.com/magento/magento2/pull/13546) -- Backport of PR-8246 for Magento 2.1: Fixes #7723 - saving multi selec (by @hostep)
    * [magento/magento2#13550](https://github.com/magento/magento2/pull/13550) -- Backport of MAGETWO-84006 for Magento 2.1: Fix robots.txt content typ (by @hostep)
    * [magento/magento2#13896](https://github.com/magento/magento2/pull/13896) -- MAGETWO-59112 Backport 2.1.x  (by @Ctucker9233)
    * [magento/magento2#13812](https://github.com/magento/magento2/pull/13812) -- [Backport 2.1] Add RewriteBase directive template in .htaccess file into pub/static folder (by @ccasciotti)
    * [magento/magento2#13658](https://github.com/magento/magento2/pull/13658) -- [Backport 2.1-develop] Show redirect_to_base config in store scope (by @JeroenVanLeusden)
    * [magento/magento2#13980](https://github.com/magento/magento2/pull/13980) -- Backport of PR-13777. Mobile 'Payments methods' step looks bad on mobile (by @Frodigo)
    * [magento/magento2#13987](https://github.com/magento/magento2/pull/13987) -- Backport of PR-13750 for Magento 2.1: Less clean up (by @Karlasa)
    * [magento/magento2#14022](https://github.com/magento/magento2/pull/14022) -- fix catalog_rule_promo_catalog_edit.xml layout (by @Karlasa)
    * [magento/magento2#13806](https://github.com/magento/magento2/pull/13806) -- [Backport 2.1] Add quoting for base path in DI compile command (by @simpleadm)
    * [magento/magento2#13486](https://github.com/magento/magento2/pull/13486) -- [Backport 2.1-develop] Change the store code in Swagger based on a param (by @JeroenVanLeusden)
    * [magento/magento2#14096](https://github.com/magento/magento2/pull/14096) -- [Backport 2.1] Schedule generation was broken (by @simpleadm)
    * [magento/magento2#14098](https://github.com/magento/magento2/pull/14098) -- [Backport 2.1] MAGETWO-64250 Problem on mobile when catalog gallery allowfullscreen is false (by @simpleadm)
    * [magento/magento2#14115](https://github.com/magento/magento2/pull/14115) -- [Backport 2.1] MAGETWO-71697: Fix possible bug when saving address with empty street line (by @simpleadm)
    * [magento/magento2#14117](https://github.com/magento/magento2/pull/14117) -- [Backport 2.1]  MAGETWO-59258: Override module-directory/etc/zip_codes.xml only the last code of a country gets include (by @simpleadm)
    * [magento/magento2#12928](https://github.com/magento/magento2/pull/12928) -- Issues #10559 - Extend swatch using mixins (M2.1) (by @srenon)
    * [magento/magento2#14151](https://github.com/magento/magento2/pull/14151) -- [Backport 2.1] 8373: Fix CURL Json POST (by @simpleadm)
    * [magento/magento2#13886](https://github.com/magento/magento2/pull/13886) -- #5463 - Use specified hashing algo in \Magento\Framework\Encryption\Encryptor::getHash (by @k4emic)
    * [magento/magento2#14168](https://github.com/magento/magento2/pull/14168) -- [Backport 2.1] Added mage/translate component to customers's ajax login (by @ccasciotti)
    * [magento/magento2#13654](https://github.com/magento/magento2/pull/13654) -- [Backport 2.1-develop] Update Store getConfig() to respect valid false return value (by @JeroenVanLeusden)
    * [magento/magento2#14219](https://github.com/magento/magento2/pull/14219) -- Backport of PR-8772 for Magento 2.1: magento/magento2#3882 (by @hostep)
    * [magento/magento2#14198](https://github.com/magento/magento2/pull/14198) -- [Backport] Can't cancel removal of a block or container in layout by setting remove attribute value to false (by @quisse)
    * [magento/magento2#14349](https://github.com/magento/magento2/pull/14349) -- Backport of PR-10445 for Magento 2.1: Fix JS translation search (by @hostep)
    * [magento/magento2#14332](https://github.com/magento/magento2/pull/14332) -- Backport: Fix for broken navigation menu on IE11 #14230 (by @sergiy-v)
    * [magento/magento2#14323](https://github.com/magento/magento2/pull/14323) -- #7816: Customer_account.xml file abused (2.1) (by @mikewhitby)
    * [magento/magento2#14417](https://github.com/magento/magento2/pull/14417) -- [BACKPORT 2.1] Removed cache backend option which explicitly set file permissions (by @xtremeperf)
    * [magento/magento2#14436](https://github.com/magento/magento2/pull/14436) -- Fix HTML tags in meta description (by @vseager)
    * [magento/magento2#14480](https://github.com/magento/magento2/pull/14480) -- [Backport 2.1] Return status in console commands (by @simpleadm)
    * [magento/magento2#14497](https://github.com/magento/magento2/pull/14497) -- [backport] fix for button color in email template  (by @Karlasa)
    * [magento/magento2#14348](https://github.com/magento/magento2/pull/14348) -- [Backport 2.1] Add json and xml support to the post method in socket client (by @simpleadm)
    * [magento/magento2#14479](https://github.com/magento/magento2/pull/14479) -- [Backport 2.1] Configurable product price options by store (by @simpleadm)
    * [magento/magento2#14505](https://github.com/magento/magento2/pull/14505) -- [Backport] Check if store id is not null instead of empty (by @quisse)
    * [magento/magento2#14524](https://github.com/magento/magento2/pull/14524) -- [backport] fix translation issue with rating stars (by @Karlasa)
    * [magento/magento2#14596](https://github.com/magento/magento2/pull/14596) -- Specify the table when adding field to filter (by @PierreLeMaguer)

2.1.13
=============
* GitHub issues:
    * [#9869](https://github.com/magento/magento2/issues/9869) -- datetime type product attribute showing current date (fixed in [magento/magento2#12033](https://github.com/magento/magento2/pull/12033))
    * [#10765](https://github.com/magento/magento2/issues/10765) -- Export data from grid not adding custom rendered data magento2 (fixed in [magento/magento2#12373](https://github.com/magento/magento2/pull/12373))
    * [#9410](https://github.com/magento/magento2/issues/9410) -- Impossible to add swatch options via Service Contracts if there is no existing swatch option for attribute (fixed in [magento/magento2#12043](https://github.com/magento/magento2/pull/12043))
    * [#10707](https://github.com/magento/magento2/issues/10707) -- Create attribute option via API for swatch attribute fails (fixed in [magento/magento2#12043](https://github.com/magento/magento2/pull/12043))
    * [#10737](https://github.com/magento/magento2/issues/10737) -- Can't import attribute option over API if option is 'visual swatch' (fixed in [magento/magento2#12043](https://github.com/magento/magento2/pull/12043))
    * [#11032](https://github.com/magento/magento2/issues/11032) -- Unable to add new options to swatch attribute (fixed in [magento/magento2#12043](https://github.com/magento/magento2/pull/12043))
    * [#10210](https://github.com/magento/magento2/issues/10210) -- Transport variable can not be altered in email_invoice_set_template_vars_before Event (fixed in [magento/magento2#12135](https://github.com/magento/magento2/pull/12135))
    * [#11341](https://github.com/magento/magento2/issues/11341) -- Attribute category_ids issue (fixed in [magento/magento2#11807](https://github.com/magento/magento2/pull/11807))
    * [#11825](https://github.com/magento/magento2/issues/11825) -- 2.1.9 Item not added to the Wishlist if the user is not logged at the moment he click on the button to add it. (fixed in [magento/magento2#12041](https://github.com/magento/magento2/pull/12041))
    * [#11908](https://github.com/magento/magento2/issues/11908) -- Adding to wishlist doesn't work when not logged in (fixed in [magento/magento2#12041](https://github.com/magento/magento2/pull/12041))
    * [#9768](https://github.com/magento/magento2/issues/9768) -- Admin dashboard Most Viewed Products Tab only gives default attribute set's products (fixed in [magento/magento2#12137](https://github.com/magento/magento2/pull/12137))
    * [#11409](https://github.com/magento/magento2/issues/11409) -- Too many password reset requests even when disabled in settings (fixed in [magento/magento2#11436](https://github.com/magento/magento2/pull/11436))
    * [#8009](https://github.com/magento/magento2/issues/8009) -- Magento 2.1.3 out of stock associated products to configurable are not full page cache cleaned (fixed in [magento/magento2#12548](https://github.com/magento/magento2/pull/12548))
    * [#12268](https://github.com/magento/magento2/issues/12268) -- Gallery issues on configurable product page (fixed in [magento/magento2#12558](https://github.com/magento/magento2/pull/12558))
    * [#8069](https://github.com/magento/magento2/issues/8069) -- Saving Category with existing image causes an exception (fixed in [magento/magento2#12368](https://github.com/magento/magento2/pull/12368))
    * [#6770](https://github.com/magento/magento2/issues/6770) -- M2.1.1 : Re-saving a product attribute with a different name than it's code results in an error (fixed in [magento/magento2#11618](https://github.com/magento/magento2/pull/11618))
    * [#12627](https://github.com/magento/magento2/issues/12627) -- Referer is not added to login url in checkout config (fixed in [magento/magento2#12629](https://github.com/magento/magento2/pull/12629))
    * [#8415](https://github.com/magento/magento2/issues/8415) -- Content Block Administration fails when I delete more than one record (fixed in [magento/magento2#12840](https://github.com/magento/magento2/pull/12840))
    * [#9243](https://github.com/magento/magento2/issues/9243) -- Upgrade ZF components. Zend_Service (fixed in [magento/magento2#12958](https://github.com/magento/magento2/pull/12958))
    * [#10812](https://github.com/magento/magento2/issues/10812) -- htaccess Options override (fixed in [magento/magento2#12959](https://github.com/magento/magento2/pull/12959))
    * [#7441](https://github.com/magento/magento2/issues/7441) -- Configurable attribute options are not sorted (fixed in [magento/magento2#12962](https://github.com/magento/magento2/pull/12962))
    * [#10682](https://github.com/magento/magento2/issues/10682) -- Meta description and keywords transform to html entities for non latin/cyrilic characters in category and product pages (fixed in [magento/magento2#12956](https://github.com/magento/magento2/pull/12956))
    * [#9969](https://github.com/magento/magento2/issues/9969) -- Cancel order and restore quote methods increase stocks twice (fixed in [magento/magento2#12952](https://github.com/magento/magento2/pull/12952))
    * [#2156](https://github.com/magento/magento2/issues/2156) -- Why does \Magento\Translation\Model\Js\DataProvider use \Magento\Framework\Phrase\Renderer\Translate, not \Magento\Framework\Phrase\Renderer\Composite? (fixed in [magento/magento2#12954](https://github.com/magento/magento2/pull/12954))
    * [#12967](https://github.com/magento/magento2/issues/12967) -- Undeclared dependency magento/zendframework1 by magento/framework (fixed in [magento/magento2#12991](https://github.com/magento/magento2/pull/12991))
    * [#12393](https://github.com/magento/magento2/issues/12393) -- Attribute with "Catalog Input Type for Store Owner" equal "Fixed Product Tax" for Multi-store (fixed in [magento/magento2#13020](https://github.com/magento/magento2/pull/13020))
    * [#10168](https://github.com/magento/magento2/issues/10168) -- Coupon codes not showing in invoice (fixed in [magento/magento2#13261](https://github.com/magento/magento2/pull/13261))
    * [#8621](https://github.com/magento/magento2/issues/8621) -- M2.1 Multishipping Checkout step New Address - Old State is saved when country is changed (fixed in [magento/magento2#13367](https://github.com/magento/magento2/pull/13367))
    * [#10738](https://github.com/magento/magento2/issues/10738) -- Empty attribute label is displayed on product page when other language used. (fixed in [magento/magento2#13532](https://github.com/magento/magento2/pull/13532))
    * [#6207](https://github.com/magento/magento2/issues/6207) -- Checkbox IDs for Terms and Conditions should be unique in Checkout (fixed in [magento/magento2#13543](https://github.com/magento/magento2/pull/13543))
    * [#10565](https://github.com/magento/magento2/issues/10565) -- Magento ver. 2.1.8 New Product with Custom attribute set not working (fixed in [magento/magento2#13549](https://github.com/magento/magento2/pull/13549))
    * [#6457](https://github.com/magento/magento2/issues/6457) -- Expired special_price is still shown for configurable products when no variant is selected (fixed in [magento/magento2#13490](https://github.com/magento/magento2/pull/13490))
    * [#6729](https://github.com/magento/magento2/issues/6729) -- Configurable product old price with taxes displayed wrong (fixed in [magento/magento2#13490](https://github.com/magento/magento2/pull/13490))
    * [#7362](https://github.com/magento/magento2/issues/7362) -- Special price vigency for configurable childs (simple products associated) doesn´t work (fixed in [magento/magento2#13490](https://github.com/magento/magento2/pull/13490))
* GitHub pull requests:
    * [magento/magento2#12033](https://github.com/magento/magento2/pull/12033) -- Backport 2.1-develop] Fix datetime type product that show current date when is empty in grids (by @enriquei4)
    * [magento/magento2#12373](https://github.com/magento/magento2/pull/12373) -- #10765 Export data from grid not adding custom rendered data magento2 (by @Zefiryn)
    * [magento/magento2#12043](https://github.com/magento/magento2/pull/12043) -- [Backport 2.1] Add swatch option: Prevent loosing data and default value if data is not populated via adminhtml (by @gomencal)
    * [magento/magento2#12135](https://github.com/magento/magento2/pull/12135) -- 10210: Transport variable can not be altered in email_invoice_set_template_vars_before Event (backport MAGETWO-69482 to 2.1). (by @RomaKis)
    * [magento/magento2#11807](https://github.com/magento/magento2/pull/11807) -- [backport 2.1] Attribute category_ids issue #11389 (by @manuelson)
    * [magento/magento2#12246](https://github.com/magento/magento2/pull/12246) -- Clear `mage-cache-sessid` cookie on Ajax Login (by @pmclain)
    * [magento/magento2#12041](https://github.com/magento/magento2/pull/12041) -- [Backport 2.1] #11825: Generate new FormKey and replace for oldRequestParams Wishlist (by @osrecio)
    * [magento/magento2#12137](https://github.com/magento/magento2/pull/12137) -- 9768: Admin dashboard Most Viewed Products Tab only gives default attribute set's products (backport for 2.1) (by @RomaKis)
    * [magento/magento2#12519](https://github.com/magento/magento2/pull/12519) -- Duplicate array key (by @lfluvisotto)
    * [magento/magento2#11860](https://github.com/magento/magento2/pull/11860) -- [Backport 2.1-develop] CMS Page - Force validate layout update xml in production mode when saving CMS Page - Handle layout update xml validation exceptions (by @adrian-martinez-interactiv4)
    * [magento/magento2#12522](https://github.com/magento/magento2/pull/12522) -- PR#12466 [BACKPORT 2.1] (by @atishgoswami)
    * [magento/magento2#12321](https://github.com/magento/magento2/pull/12321) -- Trying to get data from non existent products (by @angelo983)
    * [magento/magento2#11436](https://github.com/magento/magento2/pull/11436) -- [Backport 2.1-develop] #11409: Too many password reset requests even when disabled in settings (by @adrian-martinez-interactiv4)
    * [magento/magento2#12548](https://github.com/magento/magento2/pull/12548) -- Fixes #8009 (by @ajpevers)
    * [magento/magento2#12050](https://github.com/magento/magento2/pull/12050) -- [2.1] - Add command to view mview state and queue (by @convenient)
    * [magento/magento2#12558](https://github.com/magento/magento2/pull/12558) -- [Backport-2.1] Added namespace to product videos fotorama events (by @roma84)
    * [magento/magento2#12579](https://github.com/magento/magento2/pull/12579) -- [Backport 2.1-develop] Fix swagger-ui on instances of Magento running on a non-standard port (by @JeroenVanLeusden)
    * [magento/magento2#12368](https://github.com/magento/magento2/pull/12368) -- [Backport for 2.1 of #9904] #8069: Saving Category with existing imag… (by @nemesis-back)
    * [magento/magento2#11618](https://github.com/magento/magento2/pull/11618) -- Re saving product attribute [backport 2.1] (by @raumatbel)
    * [magento/magento2#12611](https://github.com/magento/magento2/pull/12611) -- Backport #4958 to 2.1 (by @slackerzz)
    * [magento/magento2#12629](https://github.com/magento/magento2/pull/12629) -- [2.1-develop] Add customer login url from Customer Url model to checkout config so … (by @quisse)
    * [magento/magento2#12840](https://github.com/magento/magento2/pull/12840) -- Backport PR8418 - Fatal error on cms block grid delete (by @duckchip)
    * [magento/magento2#12930](https://github.com/magento/magento2/pull/12930) -- Fix wishlist item getBuyRequest with no options (by @jameshalsall)
    * [magento/magento2#12959](https://github.com/magento/magento2/pull/12959) -- [Backport to 2.1-develop] Fix #10812: htaccess Options override (by @dverkade)
    * [magento/magento2#12958](https://github.com/magento/magento2/pull/12958) -- [Backport to 2.1-develop] Fix #9243 - Upgrade ZF components. Zend_Service (by @dverkade)
    * [magento/magento2#12956](https://github.com/magento/magento2/pull/12956) -- [Backport to 2.1-develop] Fix #10682: Meta description and keywords transform to html entities (by @dverkade)
    * [magento/magento2#12962](https://github.com/magento/magento2/pull/12962) -- [Backport to 2.1-develop] Fix configurable attribute options not being sorted (by @wardcapp)
    * [magento/magento2#12952](https://github.com/magento/magento2/pull/12952) -- [Backport #12668 into 2.1-develop] Fix for reverting stock twice for cancelled orders (by @dverkade)
    * [magento/magento2#12954](https://github.com/magento/magento2/pull/12954) -- [Backport to 2.1-develop] Fix #2156 Js\Dataprovider uses the RendererInterface. (by @dverkade)
    * [magento/magento2#12991](https://github.com/magento/magento2/pull/12991) -- [2.1.x] Fix undeclared dependency magento/zendframework1 by magento/framework (by @ihor-sviziev)
    * [magento/magento2#13020](https://github.com/magento/magento2/pull/13020) -- [Backport to 2.1-develop] Attribute with "Catalog Input Type for Store Owner" equal "Fixed Product Tax" for Multi-store (by @dverkade)
    * [magento/magento2#13261](https://github.com/magento/magento2/pull/13261) -- Backport 2.1 for MAGETWO-80428 (by @PieterCappelle)
    * [magento/magento2#13367](https://github.com/magento/magento2/pull/13367) -- [Backport 2.1] In checkout->multishipping-> new addres clean region when select country without dropdown for states  (by @enriquei4)
    * [magento/magento2#13489](https://github.com/magento/magento2/pull/13489) -- [Backport 2.1] #9247 fixed layout handle for cms page (by @simpleadm)
    * [magento/magento2#13532](https://github.com/magento/magento2/pull/13532) -- Backport of PR-11169 for Magento 2.1: Fixed issue #10738: Empty attribute label is displayed on product pag (by @hostep)
    * [magento/magento2#13543](https://github.com/magento/magento2/pull/13543) -- Backport of MAGETWO-69379 for Magento 2.1: use payment method name to (by @hostep)
    * [magento/magento2#13549](https://github.com/magento/magento2/pull/13549) -- Backport of MAGETWO-80198 for Magento 2.1: Fix issue #10565 #10575 (by @hostep)
    * [magento/magento2#13490](https://github.com/magento/magento2/pull/13490) -- [Backport 2.1] #9796 configurable product price options provider (by @simpleadm)
    * [magento/magento2#13916](https://github.com/magento/magento2/pull/13916) -- Pass Expected Data Type in backgroundColor Call (2.1) (by @northernco)

2.1.11
=============
* GitHub issues:
    * [#10441](https://github.com/magento/magento2/issues/10441) -- State/Province Not displayed after edit Billing Address on Sales Orders - Backend Admin. (fixed in [#11378](https://github.com/magento/magento2/pull/11378))
    * [#11328](https://github.com/magento/magento2/issues/11328) -- app:config:dump adds extra space every time in multiline array value (fixed in [#11451](https://github.com/magento/magento2/pull/11451))
    * [#7591](https://github.com/magento/magento2/issues/7591) -- PayPal module, "didgit" misspelling (fixed in [#11674](https://github.com/magento/magento2/pull/11674))
    * [#10301](https://github.com/magento/magento2/issues/10301) -- Customer review report search Bug in 2.1.x, 2.2 (fixed in [#11523](https://github.com/magento/magento2/pull/11523))
    * [#7927](https://github.com/magento/magento2/issues/7927) -- Dashboard graph has broken y-axis range (fixed in [#11753](https://github.com/magento/magento2/pull/11753))
    * [#11586](https://github.com/magento/magento2/issues/11586) -- Cron install / remove via command messes up stderr 2>&1 entries (fixed in [#11590](https://github.com/magento/magento2/pull/11590))
    * [#11322](https://github.com/magento/magento2/issues/11322) -- User.ini files specify 768M - Docs recommend at least 1G (fixed in [#11761](https://github.com/magento/magento2/pull/11761))
    * [#9007](https://github.com/magento/magento2/issues/9007) -- Get MAGETWO-52856 into Magento 2.1.x (fixed in [#11640](https://github.com/magento/magento2/pull/11640))
    * [#6891](https://github.com/magento/magento2/issues/6891) -- Add-to-cart checkbox still visible when $canItemsAddToCart = false (fixed in [#11611](https://github.com/magento/magento2/pull/11611))
    * [#11729](https://github.com/magento/magento2/issues/11729) -- Exported Excel with negative number can't be opened by MS Office (fixed in [#11758](https://github.com/magento/magento2/pull/11758))
    * [#4808](https://github.com/magento/magento2/issues/4808) -- The price of product custom option can't be set to 0. (fixed in [#11844](https://github.com/magento/magento2/pull/11844))
    * [#7640](https://github.com/magento/magento2/issues/7640) -- X-Magento-Tags header containing whitespaces causes exception (fixed in [#11848](https://github.com/magento/magento2/pull/11848))
    * [#10185](https://github.com/magento/magento2/issues/10185) -- New Orders are not in Order grid after data migration from M 1.7.0.2 to M 2.1.7 (fixed in [#11932](https://github.com/magento/magento2/pull/11932))
    * [#8799](https://github.com/magento/magento2/issues/8799) -- Image brackground  (fixed in [#11890](https://github.com/magento/magento2/pull/11890))
    * [#11898](https://github.com/magento/magento2/issues/11898) -- Zip code Netherlands should allow zipcode without space (fixed in [#11960](https://github.com/magento/magento2/pull/11960))
    * [#7995](https://github.com/magento/magento2/issues/7995) -- If you leave as default, shipping lines disappear (fixed in [#12022](https://github.com/magento/magento2/pull/12022))
    * [#5439](https://github.com/magento/magento2/issues/5439) -- Newsletter subscription (fixed in [#11316](https://github.com/magento/magento2/pull/11316))
    * [#8846](https://github.com/magento/magento2/issues/8846) -- Attribute option value uniqueness is not checked if created via REST Api (fixed in [#11786](https://github.com/magento/magento2/pull/11786))
    * [#11996](https://github.com/magento/magento2/issues/11996) -- Magento 2 Store Code validation regex: doesn't support uppercase letters in store code (fixed in [#12040](https://github.com/magento/magento2/pull/12040))
    * [#7903](https://github.com/magento/magento2/issues/7903) -- Datepicker does not scroll (fixed in [#12045](https://github.com/magento/magento2/pull/12045))
    * [#11697](https://github.com/magento/magento2/issues/11697) -- Theme: Added html node to page xml root, cause validation error (fixed in [#11861](https://github.com/magento/magento2/pull/11861))
    * [#11022](https://github.com/magento/magento2/issues/11022) -- GET v1/products/attribute-sets/sets/list inconsistent return result (fixed in [#11432](https://github.com/magento/magento2/pull/11432))
    * [#10032](https://github.com/magento/magento2/issues/10032) -- Download back-up .tgz always takes the latest that's created (fixed in [#11596](https://github.com/magento/magento2/pull/11596))
    * [#9830](https://github.com/magento/magento2/issues/9830) -- Null order in Magento\Sales\Block\Order\PrintShipment.php (fixed in [#11631](https://github.com/magento/magento2/pull/11631))
    * [#10530](https://github.com/magento/magento2/issues/10530) -- Print order error on magento 2.1.8 (fixed in [#11631](https://github.com/magento/magento2/pull/11631))
    * [#6597](https://github.com/magento/magento2/issues/6597) -- Sales email subject "&" turns to   (fixed in [#12115](https://github.com/magento/magento2/pull/12115))
    * [#8094](https://github.com/magento/magento2/issues/8094) -- Special characters in store name converted to numerical character references in email subject (fixed in [#12115](https://github.com/magento/magento2/pull/12115))
    * [#10767](https://github.com/magento/magento2/issues/10767) -- Race condition causing duplicate orders with double-clicks on Braintree "Pay" button (fixed in [#11901](https://github.com/magento/magento2/pull/11901))
    * [#8172](https://github.com/magento/magento2/issues/8172) -- Free shipping coupon not working with Table Rates shipping - Sorry, no quotes are available for this order. (fixed in [#11919](https://github.com/magento/magento2/pull/11919))
    * [#8089](https://github.com/magento/magento2/issues/8089) -- Cart Price Rules based on Shipping Method can't be applied in basket (fixed in [#11919](https://github.com/magento/magento2/pull/11919))
    * [#10507](https://github.com/magento/magento2/issues/10507) -- Free shipping not being properly applied in CE 2.1.8 (fixed in [#11919](https://github.com/magento/magento2/pull/11919))
    * [#3596](https://github.com/magento/magento2/issues/3596) -- Notice: Undefined index: value in /app/code/Magento/Backend/Block/Widget/Grid/Column/Filter/Select.php on line 72 (fixed in [#12284](https://github.com/magento/magento2/pull/12284))
    * [#10347](https://github.com/magento/magento2/issues/10347) -- Wrong order tax amounts displayed when using specific tax configuration (fixed in [#11593](https://github.com/magento/magento2/pull/11593))
* GitHub pull requests:
    * [#11378](https://github.com/magento/magento2/pull/11378) -- Save region correctly to save sales address from admin [backport] (by @raumatbel)
    * [#11451](https://github.com/magento/magento2/pull/11451) -- [Backport 2.1-develop] #11328 : app:config:dump adds extra space every time in multiline array value (by @adrian-martinez-interactiv4)
    * [#11674](https://github.com/magento/magento2/pull/11674) -- [TASK] Removed Typo in Paypal Module didgit => digit (by @lewisvoncken)
    * [#11678](https://github.com/magento/magento2/pull/11678) -- [BACKPORT 2.1] [TASK] Moved Customer Groups Menu Item from Other sett… (by @lewisvoncken)
    * [#11523](https://github.com/magento/magento2/pull/11523) -- [Backport 2.1-develop] Fix Filter Customer Report Review (by @osrecio)
    * [#11753](https://github.com/magento/magento2/pull/11753) -- [Backport 2.1-develop] Dashboard Fix Y Axis for range  (by @osrecio)
    * [#11590](https://github.com/magento/magento2/pull/11590) -- [Backport 2.1-develop] #11586 Fix duplicated crontab 2>&1 expression (by @adrian-martinez-interactiv4)
    * [#11761](https://github.com/magento/magento2/pull/11761) -- [BACKPORT 2.1] [TASK] Incorrect minimum memory_limit references have … (by @lewisvoncken)
    * [#11640](https://github.com/magento/magento2/pull/11640) -- 9007: Get MAGETWO-52856 into Magento 2.1.x (by @nmalevanec)
    * [#11611](https://github.com/magento/magento2/pull/11611) -- FR#6891_21 Add-to-cart checkbox still visible when  = false [Backport 2.1 develop] (by @mrodespin)
    * [#11606](https://github.com/magento/magento2/pull/11606) -- [Backport 2.1-develop] Fix AcountManagementTest unit test fail randomly (by @adrian-martinez-interactiv4)
    * [#11758](https://github.com/magento/magento2/pull/11758) -- Fix #11729 - negative value in excel export[M2.1] (by @hauso)
    * [#11728](https://github.com/magento/magento2/pull/11728) -- Check variable existence in prepareOptionIds(array) in EavAttribute.php (by @angelo983)
    * [#11844](https://github.com/magento/magento2/pull/11844) -- Save the price 0 as price in custom options [backport 2.1] (by @raumatbel)
    * [#11848](https://github.com/magento/magento2/pull/11848) -- [2.1-develop] X-Magento-Tags header containing whitespaces causes exception (by @ihor-sviziev)
    * [#11932](https://github.com/magento/magento2/pull/11932) -- [2.1-develop] Order grid - Sort by Purchase Date Desc by default (by @ihor-sviziev)
    * [#11804](https://github.com/magento/magento2/pull/11804) -- [Backport 2.1-develop] #8236 FIX CMS blocks (by @thiagolima-bm)
    * [#11890](https://github.com/magento/magento2/pull/11890) -- Save background color correctly in images. [backport 2.1] (by @raumatbel)
    * [#11920](https://github.com/magento/magento2/pull/11920) -- [BACKPORT 2.1] [TASK] Add resetPassword call to the webapi (by @lewisvoncken)
    * [#11960](https://github.com/magento/magento2/pull/11960) -- [Backport 2.1] #11898 - Change NL PostCode Pattern (by @osrecio)
    * [#11621](https://github.com/magento/magento2/pull/11621) -- Check attribute unique between same fields in magento commerce [backport 2.1] (by @raumatbel)
    * [#12022](https://github.com/magento/magento2/pull/12022) -- Add validation for number of street lines [Backport 2.1] (by @crissanclick)
    * [#11316](https://github.com/magento/magento2/pull/11316) -- [Backport 2.1-develop] Send email to subscribers only when are new (by @osrecio)
    * [#11786](https://github.com/magento/magento2/pull/11786) -- fix #8846 [Backport 2.1-develop]: avoid duplicated attribute option values (by @gomencal)
    * [#12040](https://github.com/magento/magento2/pull/12040) -- [backport 2.1]  Magento 2 Store Code validation regex: doesn't support uppercase letters in store code (by @manuelson)
    * [#12045](https://github.com/magento/magento2/pull/12045) -- #7903 correct the position of the datepicker when you scroll (by @lionelalvarez)
    * [#11861](https://github.com/magento/magento2/pull/11861) -- [Backport 2.1-develop] #11697 Theme: Added html node to page xml root, cause validation error (by @adrian-martinez-interactiv4)
    * [#12092](https://github.com/magento/magento2/pull/12092) -- Fix "Undefined variable: responseAjax" notice when trying to save a shipment package (backport fix to 2.1) (by @lazyguru)
    * [#11432](https://github.com/magento/magento2/pull/11432) -- FIX #11022 in 2.1-develop: Filter Groups of search criteria parameter have not been included for further processing (by @davidverholen)
    * [#11596](https://github.com/magento/magento2/pull/11596) -- Fix issue #10032 - Download back-up .tgz always takes the latest that's created (2.1-develop) (by @PieterCappelle)
    * [#11631](https://github.com/magento/magento2/pull/11631) -- Fixed order items list for order printing (by @rogyar)
    * [#11739](https://github.com/magento/magento2/pull/11739) -- [Backport 2.1-develop] Remove hardcoding for Magento_Backend::admin in ACL tree (by @navarr)
    * [#12115](https://github.com/magento/magento2/pull/12115) -- Fix issue with special characters in email subject (by @ihor-sviziev)
    * [#11901](https://github.com/magento/magento2/pull/11901) -- [Backport 2.1-develop] #10767 Race condition causing duplicate orders with double-clicks on Braintree "Pay" button (by @tr33m4n)
    * [#11919](https://github.com/magento/magento2/pull/11919) -- [BACKPORT 2.1] [BUGFIX] Add FreeShipping to the Items when SalesRule uses (by @lewisvoncken)
    * [#12106](https://github.com/magento/magento2/pull/12106) -- update button.phtml overcomplicated translation phrase.  (by @ChuckyK)
    * [#12284](https://github.com/magento/magento2/pull/12284) -- Issue: 3596. Resolve Notice with undefined index 'value' (by @madonzy)
    * [#11593](https://github.com/magento/magento2/pull/11593) -- Fix issue #10347 - Wrong order tax amounts displayed when using specific tax configuration (2.1-develop) (by @PieterCappelle)

2.1.10
=============
* GitHub issues:
    * [#6718](https://github.com/magento/magento2/issues/6718) -- Custom composer modules break Component Manager (fixed in [#9692](https://github.com/magento/magento2/pull/9692))
    * [#4170](https://github.com/magento/magento2/issues/4170) -- Magento2 Mini Cart Items Issue (fixed in [#10050](https://github.com/magento/magento2/pull/10050))
    * [#5377](https://github.com/magento/magento2/issues/5377) -- "No items" in minicart in 2.1 (fixed in [#10050](https://github.com/magento/magento2/pull/10050))
    * [#6999](https://github.com/magento/magento2/issues/6999) -- Performance: getConfigurableAttributes cache is broken (fixed in [#9809](https://github.com/magento/magento2/pull/9809))
    * [#6882](https://github.com/magento/magento2/issues/6882) -- Minicart empty if FPC disabled in Magneto 2.1.1 (fixed in [#10050](https://github.com/magento/magento2/pull/10050))
    * [#4731](https://github.com/magento/magento2/issues/4731) -- developer mode throws an exception, but production mode is good (fixed in [#9718](https://github.com/magento/magento2/pull/9718))
    * [#7827](https://github.com/magento/magento2/issues/7827) -- DOM schema validation error (fixed in [#9718](https://github.com/magento/magento2/pull/9718))
    * [#3872](https://github.com/magento/magento2/issues/3872) -- Slash as category URL suffix gives 404 error on all category pages (fixed in [#10164](https://github.com/magento/magento2/pull/10164))
    * [#4660](https://github.com/magento/magento2/issues/4660) -- Multiple URLs causes duplicated content (fixed in [#10164](https://github.com/magento/magento2/pull/10164))
    * [#4876](https://github.com/magento/magento2/issues/4876) -- Product URL Suffix "/" results in 404 error (fixed in [#10164](https://github.com/magento/magento2/pull/10164))
    * [#8264](https://github.com/magento/magento2/issues/8264) -- Custom URL Rewrite where the request path ends with a forward slash is not matched (fixed in [#10164](https://github.com/magento/magento2/pull/10164))
    * [#1980](https://github.com/magento/magento2/issues/1980) -- Product attributes' labels are not translated on product edit page (fixed in [#10184](https://github.com/magento/magento2/pull/10184))
    * [#6818](https://github.com/magento/magento2/issues/6818) -- PageCache gives error "Uncaught TypeError: element.prop is not a function" when there is an iframe (fixed in [#10218](https://github.com/magento/magento2/pull/10218))
    * [#6175](https://github.com/magento/magento2/issues/6175) -- Unable to generate unsecure URL if current URL is secure (fixed in [#10188](https://github.com/magento/magento2/pull/10188))
    * [#5651](https://github.com/magento/magento2/issues/5651) -- Purchase date on admin screen is always *:07:00 (fixed in [#10260](https://github.com/magento/magento2/pull/10260))
    * [#9619](https://github.com/magento/magento2/issues/9619) -- Impossible to create Text Swatch 0 (Zero) (fixed in [#10282](https://github.com/magento/magento2/pull/10282))
    * [#10266](https://github.com/magento/magento2/issues/10266) -- Product Attributes - Size 0 (fixed in [#10282](https://github.com/magento/magento2/pull/10282))
    * [#6622](https://github.com/magento/magento2/issues/6622) -- String wont translate: "Please enter a valid number in this field." (fixed in [#10745](https://github.com/magento/magento2/pull/10745))
    * [#4883](https://github.com/magento/magento2/issues/4883) -- Not translated "Please enter a valid email address (Ex: johndoe@domain.com)." (fixed in [#10747](https://github.com/magento/magento2/pull/10747))
    * [#5883](https://github.com/magento/magento2/issues/5883) -- Untranslatable string "Minimum length of this field must be equal..." (fixed in [#10747](https://github.com/magento/magento2/pull/10747))
    * [#5861](https://github.com/magento/magento2/issues/5861) -- [Magento 2.1.0] Translation (fixed in [#10747](https://github.com/magento/magento2/pull/10747))
    * [#5820](https://github.com/magento/magento2/issues/5820) -- js validation messages translation not working in customer account (fixed in [#10747](https://github.com/magento/magento2/pull/10747))
    * [#5509](https://github.com/magento/magento2/issues/5509) -- Translate messages on password strength (fixed in [#10747](https://github.com/magento/magento2/pull/10747))
    * [#6022](https://github.com/magento/magento2/issues/6022) -- Translation Issue on Magento 2.1v (fixed in [#10747](https://github.com/magento/magento2/pull/10747))
    * [#5995](https://github.com/magento/magento2/issues/5995) -- JS translation not working for some fields (fixed in [#10747](https://github.com/magento/magento2/pull/10747))
    * [#7525](https://github.com/magento/magento2/issues/7525) -- Magento 2.1.0 Js Translations Not Working (fixed in [#10747](https://github.com/magento/magento2/pull/10747))
    * [#9967](https://github.com/magento/magento2/issues/9967) -- Some messages in Customer Account Create not translated (fixed in [#10747](https://github.com/magento/magento2/pull/10747))
    * [#5519](https://github.com/magento/magento2/issues/5519) -- Getting PHP Fatal Error on  getPrice()  (fixed in [#10750](https://github.com/magento/magento2/pull/10750))
    * [#10206](https://github.com/magento/magento2/issues/10206) -- Getting PHP Fatal Error on getPrice() (fixed in [#10750](https://github.com/magento/magento2/pull/10750))
    * [#4387](https://github.com/magento/magento2/issues/4387) -- News From Date and Design Active From is set when setting Special Price for product. (fixed in [#10751](https://github.com/magento/magento2/pull/10751))
    * [#7448](https://github.com/magento/magento2/issues/7448) -- Can't remove "Set Product as New From" value (fixed in [#10751](https://github.com/magento/magento2/pull/10751))
    * [#3754](https://github.com/magento/magento2/issues/3754) -- Must override at least one static content file or custom theme static content won't deploy  (fixed in [#10753](https://github.com/magento/magento2/pull/10753))
    * [#4725](https://github.com/magento/magento2/issues/4725) -- Static files are not generated for custom theme (fixed in [#10753](https://github.com/magento/magento2/pull/10753))
    * [#7569](https://github.com/magento/magento2/issues/7569) -- Theme with no static files won't get deployed (fixed in [#10753](https://github.com/magento/magento2/pull/10753))
    * [#7311](https://github.com/magento/magento2/issues/7311) -- Vimeo videos in product gallery do not work over https (fixed in [#10748](https://github.com/magento/magento2/pull/10748))
    * [#8574](https://github.com/magento/magento2/issues/8574) -- Product Gallery Vimeo Videos Don't Play Over  HTTPS (fixed in [#10748](https://github.com/magento/magento2/pull/10748))
    * [#6081](https://github.com/magento/magento2/issues/6081) -- Broken HTML in base template file (fixed in [#10934](https://github.com/magento/magento2/pull/10934))
    * [#10510](https://github.com/magento/magento2/issues/10510) -- Magento 2.1.8 w/Sample Data is not responsive in categories with text containers (fixed in [#10929](https://github.com/magento/magento2/pull/10929))
    * [#10738](https://github.com/magento/magento2/issues/10738) -- Empty attribute label is displayed on product page when other language used. (fixed in [#10932](https://github.com/magento/magento2/pull/10932))
    * [#10417](https://github.com/magento/magento2/issues/10417) -- Wysywig editor shows broken image icons (fixed in [#11309](https://github.com/magento/magento2/pull/11309))
    * [#10007](https://github.com/magento/magento2/issues/10007) -- ProductAlert: Product alerts not showing in admin side product edit page (fixed in [#11448](https://github.com/magento/magento2/pull/11448))
    * [#10795](https://github.com/magento/magento2/issues/10795) -- Shipping method radios have duplicate IDs on cart page (fixed in [#11456](https://github.com/magento/magento2/pull/11456))
    * [#10231](https://github.com/magento/magento2/issues/10231) -- Custom URL Rewrite Not working (fixed in [#11469](https://github.com/magento/magento2/pull/11469))
    * [#11207](https://github.com/magento/magento2/issues/11207) -- Shipment API won't append comment to email (fixed in [#11386](https://github.com/magento/magento2/pull/11386))
* GitHub pull requests:
    * [#9692](https://github.com/magento/magento2/pull/9692) -- Backport of MAGETWO-59256 for 2.1: Custom composer modules break Component Manager #6718 (by @JTimNolan)
    * [#9809](https://github.com/magento/magento2/pull/9809) -- Fix issue #6999: Configurable attribute cache was never hit (by @thlassche)
    * [#10050](https://github.com/magento/magento2/pull/10050) -- [2.1-backport] Customer-data is not updates after login when full page cache disabled (by @ihor-sviziev)
    * [#10075](https://github.com/magento/magento2/pull/10075) -- Fix date format in adminhtml order grid (by @alessandroniciforo)
    * [#9718](https://github.com/magento/magento2/pull/9718) -- ported fix from 237e54d - MAGETWO-55684: Fix XSD schema (by @pixelhed)
    * [#10159](https://github.com/magento/magento2/pull/10159) -- Fix labels tranlation on category page (by @fernandofauth)
    * [#10164](https://github.com/magento/magento2/pull/10164) -- [2.1-backport] Fix trailing slash used in url rewrites (by @ihor-sviziev)
    * [#10184](https://github.com/magento/magento2/pull/10184) -- Fixed: Product attributes labels are not translated on product edit page (by @fernandofauth)
    * [#10211](https://github.com/magento/magento2/pull/10211) -- Add clarification about deprecated methods in Abstract model (by @ihor-sviziev)
    * [#10218](https://github.com/magento/magento2/pull/10218) -- Backport 1b55a64 to 2.1 - Fixes #6818 (by @ajpevers)
    * [#10188](https://github.com/magento/magento2/pull/10188) -- magento/magento2:#6175 Fixed Unable to generate unsecure URL if current URL is secure (by @arshadpkm)
    * [#10260](https://github.com/magento/magento2/pull/10260) -- Fix order date format in Orders Grid (by @ihor-sviziev)
    * [#10282](https://github.com/magento/magento2/pull/10282) -- 2.1 - Allow to use text swatch 0 (by @ihor-sviziev)
    * [#10482](https://github.com/magento/magento2/pull/10482) -- Updated root composer.json file with current release (by @okorshenko)
    * [#10569](https://github.com/magento/magento2/pull/10569) -- Fix for url_rewrite on page delete via api (by @avdb)
    * [#10695](https://github.com/magento/magento2/pull/10695) -- Fix checking active carrier against store (by @bardkalbakk)
    * [#10714](https://github.com/magento/magento2/pull/10714) -- Bugfix - Multiple filter_url_params  (by @bardkalbakk)
    * [#10745](https://github.com/magento/magento2/pull/10745) -- Backport of PR-5725 for Magento 2.1 - Fix translations issues in... (by @hostep)
    * [#10747](https://github.com/magento/magento2/pull/10747) -- Backport of MAGETWO-55900 for Magento 2.1: [GitHub] Translate message… (by @hostep)
    * [#10750](https://github.com/magento/magento2/pull/10750) -- Backport of MAGETWO-65607 for Magento 2.1: [GitHub][PR] Check return … (by @hostep)
    * [#10751](https://github.com/magento/magento2/pull/10751) -- Backport of MAGETWO-52577 for Magento 2.1: [GitHub] Set Product as Ne… (by @hostep)
    * [#10557](https://github.com/magento/magento2/pull/10557) -- [BUGFIX] Flat Category reindexList of AllChildren if the url_key of t… (by @lewisvoncken)
    * [#10753](https://github.com/magento/magento2/pull/10753) -- Backport of MAGETWO-52102 for Magento 2.1: [Github] Custom theme stat… (by @hostep)
    * [#10749](https://github.com/magento/magento2/pull/10749) -- Backport PR-9713 & PR-9711 for Magento 2.1 - Google Analytics fixes when Cookie Restrictions is enabled (by @hostep)
    * [#10748](https://github.com/magento/magento2/pull/10748) -- Backport PR-7919 for Magento 2.1 - Using Dynamic Protocol Concatination (by @hostep)
    * [#10934](https://github.com/magento/magento2/pull/10934) -- [Backport] Fixed unclosed span tag (by @Igloczek)
    * [#10929](https://github.com/magento/magento2/pull/10929) -- #10510 - fix RWD with installed Sample Data (by @szafran89)
    * [#10932](https://github.com/magento/magento2/pull/10932) -- Backport #10739 - fix for translated attribute label comparison. (by @Januszpl)
    * [#11201](https://github.com/magento/magento2/pull/11201) -- Delete CallExit function for After plugin logic execution 2.1-develop [BackPort] (by @osrecio)
    * [#11309](https://github.com/magento/magento2/pull/11309) -- [2.1-Develop] Fix #10417 (by @PieterCappelle)
    * [#11448](https://github.com/magento/magento2/pull/11448) -- Show product alerts in admin product detail [backport 2.1] (by @raumatbel)
    * [#10975](https://github.com/magento/magento2/pull/10975) -- Checkout page could hang for Javascript error (by @angelo983)
    * [#11456](https://github.com/magento/magento2/pull/11456) -- Added carrier code to ID to distinguish shipping methods [backport 2.1] (by @peterjaap)
    * [#11506](https://github.com/magento/magento2/pull/11506) -- [Backport-2.1] Retain additional cron history by default (by @mpchadwick)
    * [#11361](https://github.com/magento/magento2/pull/11361) -- [Backport 2.1-develop] cron:install and cron:remove commands, support to manage multiple instances in the same crontab, based on installation directory (by @adrian-martinez-interactiv4)
    * [#11386](https://github.com/magento/magento2/pull/11386) -- [Backport 2.1] Append shipment comment to shipment if appendComment is true (by @JeroenVanLeusden)
    * [#11469](https://github.com/magento/magento2/pull/11469) -- FR#10231_21 Custom URL Rewrite Not working (by @mrodespin)    

2.1.8
=============
* GitHub issues:
    * [#5627](https://github.com/magento/magento2/issues/5627) -- main.CRITICAL: Broken reference (Magento CE v2.1) (fixed in [#9092](https://github.com/magento/magento2/pull/9092))
    * [#4232](https://github.com/magento/magento2/issues/4232) -- UTF-8 special character issue in widgets (fixed in [#9333](https://github.com/magento/magento2/pull/9333))
    * [#4427](https://github.com/magento/magento2/issues/4427) -- SEO/HEAD - Meta title is null when breadcrumb section is removed via XML (fixed in [#9324](https://github.com/magento/magento2/pull/9324))
    * [#4868](https://github.com/magento/magento2/issues/4868) -- Checkout page very large and quite slow. (fixed in [#9364](https://github.com/magento/magento2/pull/9364) and [#9365](https://github.com/magento/magento2/pull/9365))
    * [#6997](https://github.com/magento/magento2/issues/6997) -- Remove unneeded region definitions from the /checkout page. (fixed in [#9364](https://github.com/magento/magento2/pull/9364))
    * [#6451](https://github.com/magento/magento2/issues/6451) -- Login Popup broken on iPad portrait (fixed in [#9396](https://github.com/magento/magento2/pull/9396))
    * [#7497](https://github.com/magento/magento2/issues/7497) -- Shipping method radios become disabled when checkout page refreshed (fixed in [#9485](https://github.com/magento/magento2/pull/9485))
    * [#4828](https://github.com/magento/magento2/issues/4828) -- Show/hide Editor not working sometimes (fixed in [#9499](https://github.com/magento/magento2/pull/9499))
    * [#6222](https://github.com/magento/magento2/issues/6222) -- [2.1.0] Sometimes WYSIWYG editor does not show. (fixed in [#9499](https://github.com/magento/magento2/pull/9499))
    * [#6815](https://github.com/magento/magento2/issues/6815) -- wysiwyg Editor problem (fixed in [#9499](https://github.com/magento/magento2/pull/9499))
    * [#6866](https://github.com/magento/magento2/issues/6866) -- Products in wishlist show $0.00 price (fixed in [#9571](https://github.com/magento/magento2/pull/9571))
    * [#8607](https://github.com/magento/magento2/issues/8607) -- Interface constructor if present will break Magento compilation (fixed in [#9524](https://github.com/magento/magento2/pull/9524))
    * [#5352](https://github.com/magento/magento2/issues/5352) -- Magento 2.1 email logo image function does not work (fixed in [#9590](https://github.com/magento/magento2/pull/9590))
    * [#5916](https://github.com/magento/magento2/issues/5916) -- Magento 2.1 transactional email uploaded logo not showing in admin. (fixed in [#9590](https://github.com/magento/magento2/pull/9590))
    * [#5633](https://github.com/magento/magento2/issues/5633) -- Magento 2.1 fails to load email_logo.png (fixed in [#9590](https://github.com/magento/magento2/pull/9590))
    * [#6420](https://github.com/magento/magento2/issues/6420) -- New order email header logo not showing correctly v2.1 (fixed in [#9590](https://github.com/magento/magento2/pull/9590))
    * [#6275](https://github.com/magento/magento2/issues/6275) -- Transactional Email Logo Not Getting Updated (fixed in [#9590](https://github.com/magento/magento2/pull/9590))
    * [#6502](https://github.com/magento/magento2/issues/6502) -- Can't save Logo Image to Transactional Emails (fixed in [#9590](https://github.com/magento/magento2/pull/9590))
    * [#7985](https://github.com/magento/magento2/issues/7985) -- Logo email (fixed in [#9590](https://github.com/magento/magento2/pull/9590))
    * [#7853](https://github.com/magento/magento2/issues/7853) -- Transactional email logo wrong location (fixed in [#9590](https://github.com/magento/magento2/pull/9590))
    * [#8728](https://github.com/magento/magento2/issues/8728) -- Transactional Emails Logo (fixed in [#9590](https://github.com/magento/magento2/pull/9590))
    * [#8626](https://github.com/magento/magento2/issues/8626) -- Magento 2.1.2 - 2.1.4 email logo image function does not work (fixed in [#9590](https://github.com/magento/magento2/pull/9590))
    * [#8489](https://github.com/magento/magento2/issues/8489) -- Magento 2.1.4 - Asking Why Email Logo Never been fixed on all Magento releases (fixed in [#9590](https://github.com/magento/magento2/pull/9590))
    * [#8961](https://github.com/magento/magento2/issues/8961) -- email logo error (fixed in [#9590](https://github.com/magento/magento2/pull/9590))
    * [#9118](https://github.com/magento/magento2/issues/9118) -- transactional email (fixed in [#9590](https://github.com/magento/magento2/pull/9590))
    * [#9428](https://github.com/magento/magento2/issues/9428) -- 2.1.6 Receive 500 error when want export Low Stock Report. (fixed in [#9487](https://github.com/magento/magento2/pull/9487))
    * [#3640](https://github.com/magento/magento2/issues/3640) -- CartItemInterface cannot add extension_attributes (fixed in [#9647](https://github.com/magento/magento2/pull/9647))
    * [#9646](https://github.com/magento/magento2/issues/9646) -- CartTotalRepository cannot handle extension attributes in quote addresses (fixed in [#9647](https://github.com/magento/magento2/pull/9647))
    * [#700](https://github.com/magento/magento2/issues/700) -- suggestion: revise WYSIWYG editor (fixed in [#9655](https://github.com/magento/magento2/pull/9655))
    * [#2312](https://github.com/magento/magento2/issues/2312) -- Media Browser loses PNG transparency for the thumbnails (fixed in [#9662](https://github.com/magento/magento2/pull/9662))
    * [#5401](https://github.com/magento/magento2/issues/5401) -- Transparency of .png image gone (fixed in [#9662](https://github.com/magento/magento2/pull/9662))
    * [#7149](https://github.com/magento/magento2/issues/7149) -- Admin WYSIWYG upgrade to latest Tiny MCE 4.* (fixed in [#9655](https://github.com/magento/magento2/pull/9655))
    * [#8874](https://github.com/magento/magento2/issues/8874) -- tinyMCE is disabled (fixed in [#9655](https://github.com/magento/magento2/pull/9655))
    * [#9518](https://github.com/magento/magento2/issues/9518) -- Chrome version 58 causes problems with selections in the tinymce editor (fixed in [#9655](https://github.com/magento/magento2/pull/9655))
    * [#7959](https://github.com/magento/magento2/issues/7959) -- JS error on product page Cannot read property 'oldPrice' of undefined (fixed in [#9776](https://github.com/magento/magento2/pull/9776))
    * [#9679](https://github.com/magento/magento2/issues/9679) -- Translation for layered navigation attribute option not working (fixed in [#9704](https://github.com/magento/magento2/pull/9704))
    * [#6746](https://github.com/magento/magento2/issues/6746) -- Magento 2.1.1 Problem with change currency (fixed in [#9841](https://github.com/magento/magento2/pull/9841))
    * [#9562](https://github.com/magento/magento2/issues/9562) -- ItemZone on product detail is not set correctly when chaning products via related/upsell products list (fixed in [#9841](https://github.com/magento/magento2/pull/9841))
    * [#7279](https://github.com/magento/magento2/issues/7279) -- Bill-to Name and Ship-to Name trancated to 20 characters in backend (fixed in [#10011](https://github.com/magento/magento2/pull/10011))
    * [#9139](https://github.com/magento/magento2/issues/9139) -- Unable to set negative product's quantity (fixed in [#9770](https://github.com/magento/magento2/pull/9770))
* GitHub pull requests:
    * [#9092](https://github.com/magento/magento2/pull/9092) -- Issue #5627: main.CRITICAL: Broken reference (Magento CE v2.1) (by @malachy-mcconnnell)
    * [#8880](https://github.com/magento/magento2/pull/8880) -- Update design_config_form.xml (by @WaPoNe)
    * [#9332](https://github.com/magento/magento2/pull/9332) -- Backport of MAGETWO-54401 for Magento 2.1 - Unable to click "Insert image" twice (by @hostep)
    * [#9333](https://github.com/magento/magento2/pull/9333) -- Backport of MAGETWO-52850 for Magento 2.1 - [GitHub] UTF-8 special character issue in widgets #4232 (by @hostep)
    * [#9324](https://github.com/magento/magento2/pull/9324) -- Page meta title fix in case breadcrumb section is removed via XML (by @latenights)
    * [#9364](https://github.com/magento/magento2/pull/9364) -- Backport of MAGETWO-59685 for Magento 2.1 - Checkout pages very slow … (by @hostep)
    * [#9376](https://github.com/magento/magento2/pull/9376) -- Fix a bug resulting in incorrect offsets with dynamic row drag-n-drop functionality (by @navarr)
    * [#9365](https://github.com/magento/magento2/pull/9365) -- Backport of MAGETWO-60351 for Magento 2.1 - Unnecessary disabled paym… (by @hostep)
    * [#9396](https://github.com/magento/magento2/pull/9396) -- [2.1-backport] Fix Login Popup broken on iPad portrait (by @ihor-sviziev)
    * [#9485](https://github.com/magento/magento2/pull/9485) -- Shipping method radios become disabled when checkout page refreshed (by @rachkulik)
    * [#9500](https://github.com/magento/magento2/pull/9500) -- Backport of MAGETWO-54798 For Magento 2.1: One page checkout - Street Address should highlight red when data is missing (by @hostep)
    * [#9499](https://github.com/magento/magento2/pull/9499) -- Backport of MAGETWO-57675 for Magento 2.1: WYSIWYG editor does not show. #6222 #4828 #6815 (by @hostep)
    * [#9571](https://github.com/magento/magento2/pull/9571) -- Backport of MAGETWO-59512 for Magento 2.1: Products in wishlist show $0.00 price #6866 (by @hostep)
    * [#9524](https://github.com/magento/magento2/pull/9524) -- magento/magento2#8607: Interface constructor if present will break Magento compilation (by @LoganayakiK)
    * [#9590](https://github.com/magento/magento2/pull/9590) -- Backport of MAGETWO-53010 for Magento 2.1: Saving a custom transactional email logo, failed. (by @hostep)
    * [#9487](https://github.com/magento/magento2/pull/9487) -- magento/magento2#9428: 2.1.6 Fixed 500 error while getting Low Stock Reports (by @mikebox)
    * [#9653](https://github.com/magento/magento2/pull/9653) -- Allow X-Forwarded-For to have multiple values [2.1 backport] (by @kassner)
    * [#9647](https://github.com/magento/magento2/pull/9647) -- Fix for #9646 (by @ekuusela)
    * [#9662](https://github.com/magento/magento2/pull/9662) -- Keep transparency when resizing images [2.1 backport] (by @kassner)
    * [#9661](https://github.com/magento/magento2/pull/9661) -- Add configurations for change email templates [2.1 backport] (by @kassner)
    * [#9660](https://github.com/magento/magento2/pull/9660) -- Do not di:compile tests/ folder [2.1 backport] (by @kassner)
    * [#9655](https://github.com/magento/magento2/pull/9655) -- Backport of MAGETWO-69152: Removed workaround for old Webkit bug in t… (by @hostep)
    * [#9776](https://github.com/magento/magento2/pull/9776) -- #7959 - Fix for JS error on Swatch Renderer for undefined oldPrice (by @dreamworkers)
    * [#9601](https://github.com/magento/magento2/pull/9601) -- Do not hardcode product link types [2.1 backport] (by @kassner)
    * [#9704](https://github.com/magento/magento2/pull/9704) -- Fixes regression bug introduced in Magento 2.1.6 where the layered navigation options are sometimes being cached using the wrong store id. (by @hostep)
    * [#9841](https://github.com/magento/magento2/pull/9841) -- Backport of MAGETWO-59089 for Magento 2.1: Magento 2.1.1 Problem with change currency (by @hostep)
    * [#10011](https://github.com/magento/magento2/pull/10011) -- Backport 7279 to 2.1 (by @lazyguru)
    * [#9770](https://github.com/magento/magento2/pull/9770) -- #9139 Unable to set negative product's quantity fixes commit. (by @poongud)

2.1.1
=============
To get detailed information about changes in Magento 2.1.1, please visit [Magento Community Edition (CE) Release Notes](http://devdocs.magento.com/guides/v2.1/release-notes/ReleaseNotes2.1.1CE.html "Magento Community Edition (CE) Release Notes")

2.1.0
=============
To get detailed information about changes in Magento 2.1.0, please visit [Magento Community Edition (CE) Release Notes](http://devdocs.magento.com/guides/v2.1/release-notes/ReleaseNotes2.1.0CE.html "Magento Community Edition (CE) Release Notes")

2.0.0
=============
* Fixed bugs:
    * Fixed an issue where discount to the shipping amount was not applied during invoice creation
    * Fixed an issue where inline translations did not work correctly for phrases with special characters
    * Eliminated multiple escaping in the inline translation pop-up
    * Fixed an issue where searching in Billing Agreements grid in Admin resulted in an SQL
    * Fixed the Refresh Lifetime Statistics functionality in Reports
    * Increased the limit of cookies per domain, according to following recommendations https://tools.ietf.org/html/rfc6265#section-6.1
    * Fixed filtering by date in grids
    * Fixed an issue where Totals were not calculated correctly if discount was applied when placing an order from Admin
    * Fixed filtering of online customers by session Start Time
    * Fixed an issue where it was impossible to register a customer on the storefront if an attribute with file type was required
    * Fixed the issues in the Custom Admin URL with https functionality
    * Fixed an issue where the category storefront URL did not include its parents
    * Fixed an issue where Product API did not work properly for not default store code
    * Fixed issues with adding images for a first product in a new attribute set
    * Fixed an issue where it was impossible to create a credit memo for the order with downloadable product and sales rule applied
    * Fixed an issue where it was impossible to manually add products to a configurable product
    * Fixed an issue with custom timezone

2.0.0-rc2
=============
* Fixed bugs:
    * Fixed an issue where video didn’t play on iPad and iPhone
    * Fixed an issue where Admin panel was not accessible if port was used in URL
    * Fixed an issue where database name could not be changed after fail during installation process
    * Fixed an issue where bundle items quantities could not be saved when editing a bundle product in a wishlist on the storefront
    * Fixed PHP issue which appeared during Text Swatch product attribute creation
    * Fixed Mini Shopping Cart re-sizing after removing the product
    * Fixed an issue with negative subtotal during PayPal checkout
    * Fixed inconsistent credit card validation
    * Fixed an issue where the Product Gallery did not completely overlay the bottom layer in the full-screen view
    * Fixed an issue where the Product Gallery could not be easily opened in the full-screen view
    * Fixed an issue where the “+” icon was displayed on video preview hover
    * Fixed an issue where video preview was visible under video player
    * Fixed an issue where 'Admin' was not a required field for the new Swatch
    * Fixed an issue where shipping and billing country information was not transmitted to PayPal
    * Fixed an issue with the attribute in configurable products
    * Fixed a category page load time
    * Removed space outside the visible area in Admin
    * Fixed an issue where Magento was stuck in the maintenance mode, if a backup was created when disabling modules via Web Setup Wizard
    * Fixed session response to be in JSON
    * Fixed an issue where an out of stock product was displayed on the storefront
    * Fixed an error which appeared during product import with replace behavior
    * Fixed an issue were URL rewrites in catalog categories were wrong after URL key for a store view was changed or a category was moved
    * Fixed an issue where JSON was received instead of normal page when trying to delete a category after reset
    * Fixed an issue where product API with "all" store code did not work
    * Fixed the misleading system message about invalid indexers
    * Fixed an issue where a bundle product created using Web API was not visible on the storefront
    * Fixed an issue where it was impossible to save more than one configuration for a configurable product with the text swatch attribute
    * Fixed an issue with the absence of a proper indication about why an image could not be deleted not deleted when it was used in one of the store views
    * Fixed an issue with data modification in export result file
    * Fixed an issue with the incorrect behavior of the required check box custom
    * Fixed an issue where an exception was thrown when trying to install Magento having previously installed and uninstalled it
    * Fixed an issue where changing the layout of a CMS page caused its design theme to change to Magento Blank
    * Fixed an issue where CMS pages API did not work with store code
    * Fixed an issue where CMS blocks API did not work for multiple websites
    * Fixed an issue where XSS Payload could been saved into Admin Panel
    * Fixed an issue where an error caused by adding a new swatch attribute persisted after deleting the attribute
    * Fixed PHP notice which appeared during text swatch product attribute creation
    * Fixed JS error on credit memo view grid during export to CSV
    * Fixed an issue where a user was redirected to a blank page when canceling checkout with PayPal Express in Website Payments Pro Hosted Solution
    * Fixed an issue where it was impossible to checkout if Persistent Shopping Cart is enabled
    * Fixed an issue where it was impossible to complete the Braintree PayPal Checkout if Street line 2 is empty
    * Fixed an issue with XSS Payload in website's translation table
    * Fixed an issue where payment functionality matrix section was suitable only for AbstractMethod specializations, leaving other payment methods without valuable information
* GitHub issues and requests:
    * [#2276](https://github.com/magento/magento2/issues/2276) -- Notice: getimagesize(): Read error! in app/code/Magento/Catalog/Model/Product/Image.php on line 949
    * [#2128](https://github.com/magento/magento2/issues/2128) -- wrong filename on products list

2.0.0-rc
=============
* Performance improvements:
    * Refactored observer classes to satisfy the single-responsibility requirement in order to minimize the time on observer object loading
    * Improved performance of catalog advanced search
    * Improved performance of catalog quick search
    * Various micro-optimizations of the Magento framework
    * Optimized stores initialization and data loading
    * Improved the CSS and JavaScript minification mechanism
    * Sales rules (cart promotions) performance optimization
    * Improved performance of table rendering
    * Improved sample data performance
* Payment methods improvements:
    * Implemented the eWay online payment gateway using both Client side encryption and Responsive shared page APIs.
    * Implemented PayPal best practices for PayPal Express Checkout
    * Improved the UI for the Braintree payment method
* Checkout improvements:
    * Implemented persistence of entered customer’s data on Checkout flow
    * Implemented persistence of customer Shopping Cart
    * Improved Terms and Conditions settings
    * Improved error handling mechanism on Checkout flow
    * Improved the collect totals mechanism
    * Improved the checkout credit card form design
* Product improvements:
    * Added the ability to manage the list of variations/configurations using the wizard or manually
    * Added the ability to manage the list of variations based on removing or adding a new attribute
    * Added the ability to notify the user during product information update or management
    * Added the ability to notify the user during the change of the product template set when saving a product
    * Added the ability to update product data during mass action update
    * Added the "remove attribute" button to each attribute on the second step of the configurable product attributes creation
    * Removed grouped price from product creation functionality
    * Discounts logic is based on the selected option of a configurable product
* CMS improvements:
   * Added a sticky header and controls to data grids
   * Added the support for multiple select in data grid filters
   * Added the ability to change column width in data grids
   * Added the ability of inline and bulk inline editing in data grids
* WebApi Framework improvements:
    * Added the support for store codes in API calls
    * Added the ability to update the Magento system to a particular version of Magento 
    * Added the ability to enable/disable modules for Magento application
    * Added the ability to use maintenance mode
    * Introduced the common interface for Webapi payload processors
    * Moved the Search API from the Search module to the Search Framework library
* Framework improvements:
    * Refactored observer classes to implement the same interface
    * Added HHVM compatibility with the `intl` extension
    * Added the support for  PHP 7
    * Improved catalog image generation
    * Added the ability to store Magento code in the `vendor` directory
    * Added support for the URN  schema in configuration files
    * Implemented a new component for Model Windows to simplify input/modification data in pop-us
    * Implemented the gallery widget with MVP functionality in Global JS Widgets Library
    * Included the migration tool to Magento CLI
    * Added inline editing in data grids
    * Updated data grids mass actions
    * Added  the export  to data grids
    * Implemented the full text search in data grids in Admin
    * Applied finalized new data grid to Customer List
    * Removed the DesignEditor module and related code
    * Added Swagger REST API schema generation for automatic API documentation creation
    * Added the ability to generate a page that reports all REST APIs in the system
    * Added the Webapidoc module to generate on-the-fly API documentation for a particular Magento instance
    * Added the support for inline translation in Magento UI components
    * New data grid component applied on Sales data grid
    * Unified database resource connections interface
    * Implemented a mechanism for rendering escaped string
    * Added the ability to extend any Magento JS Component after it is loaded on the page and before it is executed
    * Added the @remove and @display attributes to handle block and container appearance
    * Added the ability to send the purge requests for multiple servers
    * Removed deprecated code from modules and Magento/Framework
    * Implemented the independent template hints for the storefront and the Admin panel
* Setup
    * Improved the wording in the Web Installation Wizard UI
    * Updated the extensions styles in the Web Installation Wizard
    * Added the ability to control access to the setup tool
    * Added the Install Components functionality for Web Installation Wizard
    * Updated styles 
* Sample Data:
    * Improved sample data installation UX
    * Updated sample data with Product Heros, color swatches, MAP and rule based product relations
    * Improved sample data upgrade flow
    * Added the ability to log errors and set the error flag during sample data installation
* Various improvements:
    * Added integration with NewRelic
    * Added dashboard for Platinum integration partners
    * Improved downloadable products UI in the Admin panel in order to provide the same experience with all other product types
    * Implemented email templates responsiveness and localization
    * Implemented WebApi to retrieve store information, country list and currency information
    * Implemented discount coupon generation, search, and multi-actions APIs
    * Added the ability to declare filter components inside the data grid column definition
    * Added the console command `catalog:image:resize`
    * Consolidated the algorithms for populating system packages for upgrade and other tasks
    * Implemented various accessibility improvements
    * Improved UX for Tax Rule Management
    * The Luma theme became default storefront theme
    * Refactored grid store selectors for their unification
    * Refactored Magento UI Library to use the 'lib-' prefix in all library mixins
    * Refactored styles to eliminate log file errors after static files deployment is executed
    * Improved user experience on the backup pages of Component Manager
    * Increased JS and PHP code coverage with unit tests
    * Implemented the product attribute swatches functionality
* GitHub issues and requests:
    * [#1397](https://github.com/magento/magento2/pull/1397) -- Allow multiple caplitalized words (like typical vendor names) in ACL resource IDs
    * [#1231](https://github.com/magento/magento2/pull/1231) -- Update Cm_Cache_Backend_Redis to v1.8 and Credis_Client to v1.5
    * [#1375](https://github.com/magento/magento2/pull/1375) -- Allow phrases to contain more than nine numeric placeholders
    * [#1454](https://github.com/magento/magento2/pull/1454) -- Permissions not set correctly #1453
    * [#1410](https://github.com/magento/magento2/pull/1410) -- Allow custom config files with virtual types only by adding generic schema locator
    * [#1416](https://github.com/magento/magento2/pull/1416) -- Add abstract method execute() to \Magento\Framework\App\Action\Action
    * [#1406](https://github.com/magento/magento2/pull/1406) -- Fixes issue with reading store config for store with code of 'default'
    * [#1447](https://github.com/magento/magento2/pull/1447) -- Missing strings for Javascript Translations
    * [#1465](https://github.com/magento/magento2/pull/1465) -- Fix typo: itno => into
    * [#1476](https://github.com/magento/magento2/pull/1476) -- fix typo in dispatched event
    * [#1516](https://github.com/magento/magento2/pull/1516) -- Typo in addAction function: _.findIdnex should be .findIndex
    * [#1533](https://github.com/magento/magento2/pull/1533) -- Updated the broken dev doc links in the README.md
    * [#1469](https://github.com/magento/magento2/pull/1469) -- Remove dependency of date renderer on global state locale
    * [#1462](https://github.com/magento/magento2/pull/1462) -- Product collection - Add url rewrite from different website
    * [#1422](https://github.com/magento/magento2/pull/1422) -- Improve \Magento\Framework\Api\SortOrder
    * [#1528](https://github.com/magento/magento2/pull/1528) -- Really hide Pdf totals with zero amounts
    * [#1641](https://github.com/magento/magento2/pull/1641) -- Update create-admin-account.phtml
    * [#1440](https://github.com/magento/magento2/pull/1440) -- Update Console Tool Usage for Cache and Index Operations
    * [#1523](https://github.com/magento/magento2/pull/1523) -- Permissions not set correctly
    * [#1517](https://github.com/magento/magento2/pull/1517) -- add router.php for php Built-in webserver
    * [#1654](https://github.com/magento/magento2/pull/1654) -- Update filter.phtml
    * [#1602](https://github.com/magento/magento2/pull/1602) -- Improve product export performance
    * [#1062](https://github.com/magento/magento2/pull/1062) -- Add check to see if PHP > 5.6 and always_populate_raw_post_data = -1
    * [#1496](https://github.com/magento/magento2/pull/1496) -- Add "Not Specified" as a gender option when customer does not specify gender
    * [#1664](https://github.com/magento/magento2/pull/1664) -- AbstractPdf::_getTotalsList - fix return comment
    * [#1502](https://github.com/magento/magento2/pull/1502) -- Loosened Regex on GB postcodes
    * [#1801](https://github.com/magento/magento2/pull/1801) -- Enable translation for gender
    * [#1835](https://github.com/magento/magento2/pull/1835) -- Added exception to event data
    * [#1854](https://github.com/magento/magento2/pull/1854) -- Added missing @method annotation for setWebsiteIds
    * [#1818](https://github.com/magento/magento2/pull/1818) -- use return value instead of reference parameter
    * [#1206](https://github.com/magento/magento2/pull/1206) -- Allow modules to live outside of app/code directory
    * [#1869](https://github.com/magento/magento2/pull/1869) -- Typo in function name fixed (stove->store)
    * [#1792](https://github.com/magento/magento2/pull/1792) -- Fix invalid @method phpdoc to prevent prophecy mocking error
    * [#1483](https://github.com/magento/magento2/issues/1483) -- admin external extjs.com requests acknowledged bug CS needs update
    * [#1489](https://github.com/magento/magento2/issues/1489) -- Russia is eligible PayPal merchant country but absent in Magento 2.0 "Merchant Country" dropdown
    * [#1461](https://github.com/magento/magento2/issues/1461) -- Cart Items are not deleted after success checkout
    * [#1452](https://github.com/magento/magento2/issues/1452) -- First two orders with sample data fail
    * [#1458](https://github.com/magento/magento2/issues/1458) -- window.checkout is undefined if minicart is removed
    * [#1443](https://github.com/magento/magento2/issues/1443) -- GET /V1/carts/mine/items is returning "cartId is a required field"
    * [#1442](https://github.com/magento/magento2/issues/1442) -- Running 'log:clean' through cli results in an error: 'Area code not set'
    * [#1435](https://github.com/magento/magento2/issues/1435) -- Fatal error: Class 'Magento\Framework\HTTP\Client_Curl' not found
    * [#1432](https://github.com/magento/magento2/issues/1432) -- Doesn't work sorting in the search list.
    * [#460](https://github.com/magento/magento2/issues/460) -- Optimization on Weee tax calculation
    * [#647](https://github.com/magento/magento2/issues/647) -- Template path hints behavior
    * [#771](https://github.com/magento/magento2/issues/771) -- Fatal error when calling execute()
    * [#896](https://github.com/magento/magento2/issues/896) -- i18n generator generates dictionary with duplicated phrases
    * [#930](https://github.com/magento/magento2/issues/930) -- Flushing cache fails to wipe view_preprocessed dir
    * [#933](https://github.com/magento/magento2/issues/933) -- Admin fields that use WYSIWYG don't pass the js validation
    * [#939](https://github.com/magento/magento2/issues/939) -- Inline Translation adds <span> within <head>
    * [#941](https://github.com/magento/magento2/issues/941) -- [Question] How to get the currency code and symbol of an AbstractPrice?
    * [#1159](https://github.com/magento/magento2/issues/1159) -- Warning: The email and password is visible in front-end
    * [#1167](https://github.com/magento/magento2/issues/1167) -- Magento_Log: creating new record with wrong store_id every each refresh page for Adminhtml
    * [#1192](https://github.com/magento/magento2/issues/1192) -- Error in monetary value Brazil
    * [#1367](https://github.com/magento/magento2/issues/1367) -- String class name issue for php7
    * [#1242](https://github.com/magento/magento2/issues/1242) -- eclipse pdt validator error
    * [#1279](https://github.com/magento/magento2/issues/1279) -- related products not able to add to cart
    * [#1423](https://github.com/magento/magento2/issue/1423) -- Magento\Email\Model\Template\Filter Comment vs Code
    * [#1418](https://github.com/magento/magento2/issue/1418) -- Items in minicart are not cleared after successful placing an order
    * [#1408](https://github.com/magento/magento2/issue/1408) -- Error command cli setup:static-content:deploy
    * [#1396](https://github.com/magento/magento2/issue/1396) -- Products are not shown in category right after import
    
1.0.0-beta
=============
* Framework improvements:
    * Improved the way the return type of a method is derived during WSDL generation
    * Added the ability to retrieve a list of available endpoints for a given Magento instance
* Search improvements:
    * Introduced the Search field in scope of Enhanced Data Grids on CMS
    * Introduced the Search Indexer interface and XML declaration
    * Introduced the Search module API to support the search functionality
    * Product attributes have different weight by default
    * Implemented per store full text index
    * Search API moved from the Catalog to the Search module
* Various improvements:
    * Payment gateway infrastructure improvements
    * Removed the outdated GoogleShopping module
    * Implemented the integration with Braintree payment gateway
    * Moved the Authorize.net payment method back to CE
    * Updated processing of the fraud status for orders
    * Added data attributes for checkout sections
    * The enhanced grid component is applied to the Product grid and the whole Sales module
    * Added the ability to create account during and after the checkout process is complete
    * Enabled coupon code in URL for empty carts
    * Added new icons to the admin-icons font
    * Improved the configurable product creation flow to be consistent with other product types creation
    * Eliminated email markup duplication and simplified email content management by including footer/header content and styles from a single place for email templates
* Fixed bugs:
    * Fixed an issue where there was no successful message about VAT validation
    * Fixed an issue where it was not possible to change State/Province in customer address
    * Fixed an issue where the "Array to string conversion" notice appeared during order creation if custom address attribute existed
    * Fixed an issue in API service where a customer was created even when there was a validation error
    * Fixed an issue where a new custom theme added to the file system, was not accessible in Admin
    * Fixed an issue where it was impossible to save a customer record in Admin after selecting to add a new address and then deleting the address fields
    * Fixed an issue where it was impossible to add to cart by SKU more, than one product at a time on the storefront
    * Fixed an issue where it was impossible to place an order on the storefront using PayPal Payments Pro
    * Fixed an issue where checkout was performed without applying existing and active catalog rules
    * Fixed an issue where category displayed outdated prices until page cache was manually reset or timed out
    * Fixed with the wrong label for catalog price rules processing dropdown
    * Fixed the copyright text on the storefront
    * Fixed an issue where the Create new Customer Account form was broken on the storefront
    * Fixed an issue where CMS page was saved automatically once a widget is added to the page content
    * Fixed an issue where a user was redirected to the Orders page after clicking the Get Payment Update in Admin
    * Fixed a comment in the \Magento\Framework\App\Http::launch() method
    * Fixed an issue where editable multi-select fields were not always displayed on the new tax rule creation page
    * Fixed a JavaScript error which blocked guest checkout if JavaScript bundling was enabled
    * Fixed an issue where the Cookie Restriction Mode block was not displayed on the storefront
    * Fixed an issue where the Tax Rate edit form showed an empty selected value instead of *
    * Fixed an issue for Internet Explorer v.11 where the Remove button in the mini shopping cart did not work
    * Fixed an issue where the Email field was duplicated on the send invitation form
    * Fixed an issue where the product images where not displayed on a storefront product page for simple products
    * Fixed the error handling for PHP settings check in Setup Wizard which was caused by timeout
    * Fixed an issue where a credit card type was undefined in iframe request
    * Fixed the iframe payment method flow
    * Fixed an issue where it was possible to place an order with the empty Purchase Order Number required field
    * Fixed an issue where a loader was not displayed in mini shopping cart
    * Fixed an issue where autocomplete was enabled on credit card form
    * Fixed issues with PayPal conflict resolution
    * Fixed cache collisions for static view files cache
    * Fixed an issue where an admin user with limited access to only Content could not access CMS pages in Admin
    * Fixed an issue where "Privacy and Cookie Policy" CMS page content was unreadable
    * Fixed an issue where shopping cart became empty if a customer configured an item to be the same as already existing in the shopping cart
    * Fixed an issue where Maestro credit card did not pass validation
    * Fixed an issue where a custom order status appeared in the Order State drop down
    * Fixed an issue where it was impossible to add products to a package and create a shipping label
    * Fixed the incorrect text message in the Products Requiring Attention grid for product with enabled Qty Increments
    * Fixed an issue where the thumbnail image option was not applied correctly for grouped products
    * Fixed an issue where the Newsletter Subscribers grid was not displayed
    * Fixed an issue where the order number was absent on the success page for PayPal Advanced Checkout
    * Fixed non-working sales tax report
    * Fixed an issue where sales reports with empty rows did not work
    * Fixed the broken layout on the shipping checkout step for the Blank theme
    * Fixed an issue where the Phone Number hint was displayed outside of the visible screen when adding a new address
    * Fixed the style for the Add button on certain pages in Admin
    * Fixed an issue where it was impossible to confirm signing of a billing agreement during checkout
    * Fixed an issue with missing events subscriptions
    * Fixed the incorrect message displayed when trying to delete a product from mini shopping cart
    * Fixed an issue where it was possible to share a wish list after a short-term session was expired when the Persistent Shopping Cart functionality was enabled
    * Fixed an issue where the "optional" placeholder was displayed for the Password field when logging in during checkout
    * Fixed an issue where placing an order with the same parameters was becoming slower on each iteration
    * Fixed an issue where the controls on the Review & Payments page were disabled in case PayPal transaction had been declined during checkout
    * Fixed an issue where the terms and conditions links were always displayed during checkout
    * Fixed an issue where customer address was not saved during checkout
    * Fixed an issue where tax information was shown in order summary for a guest customer
    * Fixed an issue with currency in different locales
    * Fixed an issue where tax information was not displayed in mini shopping cart
    * Fixed an issue with the Display Full Tax Summary configuration setting
    * Fixed an issue with Advanced Fraud Protection in the Braintree payment method
    * Fixed an issue with tax and FPT information missing in the order summary on the storefront
    * Fixed the auto load error after running setup:di:compile
    * Fixed an issue with publishing files in production mode
    * Fixed a cron re-indexing issue
    * Fixed an issue with JS minification errors which appeared when adding products to cart
    * Fixed the broken Reset Password link in a welcome email for a customer created in Admin
    * Fixed an issue where WSDL generated for any Web API service had an invalid element
    * Fixed the records displaying order in the Sales grids
    * Fixed an issue with invalid path parsing in exclude list during bundle collecting
    * Fixed an issue where there was no success message after place order via Payflow Link on mobile device
    * Fixed an issue where it was not possible to place order as a guest via Authorize.net Direct Post
    * Fixed an issue where it was not possible to place order via Express Checkout with enabled Terms and Conditions
    * Fixed an issue where the last transaction ID was displayed for order placed within PayPal Payflow Link on the storefront
    * Fixed an issue where it was not possible to perform products mass update in Internet Explorer
    * Fixed an issue where product did not appear on front after category change and indexers set to update on schedule
    * Fixed an issue where special chars in custom options were replaced with HTML entities
    * Fixed the invalid title of the Update Attributes page in Admin
    * Fixed performance issue in the storefront search
    * Fixed the _initCustomer() method in the Customer module controllers
    * Fixed an issue where the customer_save_after_data_object event dispatched a few times
    * Fixed an issue where it was not possible to create customer account from order success page
    * Fixed an issue where styles were missing on the storefront
    * Fixed the "No such entity with cartId = " error on One Page Checkout if online payment was used
    * Fixed an issue where flush of one type of cache made other flushed
    * Fixed sales orders grid for orders placed using PayPal when the PayPal module was disabled
    * Fixed an issue where loader hanged out if admin did not specify shipping address or method for order
    * Fixed an irrelevant note in Payflow Pro section on One Page Checkout
    * Fixed an issue where an HTML tag was displayed in the Instructions field of the bank transfer payment form during checkout on the storefront
    * Fixed an issue where it was not possible to place order as a guest via Authorize.net Direct Post
    * Fixed an issue where the Refund and Refund Offline buttons styles on the Credit Memo page
    * Fixed an issue where Modal window for "Sign In" was not closed when clicking outside
    * Fixed an issue where the Compare Products functionality was accessible in a store with the responsive Blank theme applied when browsing using Iphone6
    * Fixed incorrect relative paths in LESS files
    * Fixed an issue where it was not possible to save newsletter subscription information of a customer in Admin
    * Fixed an issue where order number was missing on PayPal (Payflow) Express Checkout solutions
    * Fixed an issue where Product quantity was still displayed in the mini shopping cart after Place Order on storefront
    * Fixed indexer failure with re-index on schedule
    * Fixed catalog price rule calculation for Group price
    * Fixed an issue where specific product tabs were accumulated on switching between product templates
    * Fixed an issue where grid actions in custom options dialog did not work
    * Fixed an issue where page cache was not invalidated when products were changed via mass action
    * Fixed an issue where catalog page was not updated after product returned to stock again if Varnish was enabled
    * Fixed incorrect price of configurable product on storefront
    * Fixed an issue where the Catalog page in Admin did not work in production mode
    * Fixed an issue where the Add to cart button did not redirect to the configuration page for configurable products
    * Fixed an issue where it was not possible to perform mass delete having used the Select All functionality to select the products
    * Fixed an issue where PayPal Express Checkout was always displayed as disabled in Internet Explorer
    * Fixed an issue where PayPal Express labels on checkout were small
    * Fixed an issue where the Configure Product modal window blended in with page content during order creation
    * Fixed an issue where hints behaved not user-friendly on the storefront
    * Fixed an issue with exception when creating account for guest customer after placing order within Express Checkout
    * Fixed orders grid mass actions
    * Fixed invoice grid search on Invoices tab of Order View page
    * Fixed an issue where gallery images were not loaded for Configurable Product
    * Fixed an issue where currency rates were displayed incorrectly
    * Fixed exception framework misuse in indexers
Tests:
    * Increased code coverage of the Theme module
    * Refactored some methods to decrease C.R.A.P index and implemented unit tests for CMS, Sitemap and Widget modules
    * Sample data installation covered with unit tests
    * Moved all test data for functional tests to repositories
    * Increased test coverage for setup fixtures which are used for performance tests
    * Updated some variations for checkout related automated tests
    * Increased SOAP connection timeout to prevent SOAP failures in API tests
* GitHub issues and requests:
    * [#1272](https://github.com/magento/magento2/issues/1272) -- In dashboard Last Orders items quantity showing wrong some times
    * [#1341](https://github.com/magento/magento2/issues/1341) -- [Question] How to specify attributes to load on ProductRepository::getList()
    * [#1370](https://github.com/magento/magento2/issues/1370) -- EAV Attribute Repository linking to catalog_eav_attribute
    * [#1382](https://github.com/magento/magento2/issues/1382) -- setup:install can now accept {{base_url}} as input to --base-url
    * [#1385](https://github.com/magento/magento2/pull/1385) -- Specify `Magento_Catalog` module on template for sorting
    * [#1411](https://github.com/magento/magento2/issues/1411) -- No error message shown when purchase qty mismatch the 'Qty Increments' setup
    * [#1420](https://github.com/magento/magento2/pull/1420) -- Make Api\SearchResults implement Api\SearchResultsInterface
    * [#1421](https://github.com/magento/magento2/pull/1421) -- Rename Api\SearchCriteriaBuilder::addFilter() to addFilters()
    * [#1427](https://github.com/magento/magento2/issues/1427) -- Fatal error: Call to a member function format() on null in magento2/lib/internal/Magento/Framework/Stdlib/DateTime/Timezone.php on line 260
    * [#1434](https://github.com/magento/magento2/issues/1434) -- Failed to add product variation

0.74.0-beta16
=============
* Framework improvements:
    * Improved declaration of JS widgets with mixins node
    * Optimized Magento\Framework\View\Element\Template for production mode
    * Added color picker JS library
* Various improvements:
    * Implemented enhanced checkout flow
    * Increased security of search filters
    * Added price variation caching based on WEEE
    * Updated adapter in payment gateway
    * Data version control was implemented on Customer and Quote modules to reduce number of unnecessary model saves
    * Optimized Magento\PageCache\Model\Observer\ProcessLayoutRenderElement::execute
    * Added event to make an ability to add elements on a main tab of attribute editing page
    * Email markup duplication is eliminated and email content management is simplified
    * Implemented and ability to update/replace and export advanced product prices
    * Added import history
    * Implemented and ability to export configurable products
* Payment improvements:
    * Moved PayPal solutions back to CE
    * PayPal Payments Standard is updated to PayPal Express Checkout API
    * Improved conflict resolution rules for PayPal configuration
* Tests:
    * Increased timeout for HTTP requests in api-functional test framework to avoid error "HTTP request failed"
    * Increased unit test coverage for Reports, CatalogRule, Config and CurrencySymbol modules
    * Functional tests maintenance for Tax, Sales, Reports modules
    * Created fixture generation scripts for functional tests
    * Fixed CheckoutWithGiftMessagesTest functional test
* Fixed bugs:
    * Fixed wrong redirect after adding to compare, wish list or cart from private content blocks
    * Fixed an issue where tax information was not saved in orders
    * Fixed an issue where total amount was not set in the correct place
    * Fixed an issue where product options details were missing while adding to wish list
    * Fixed an issue with empty customer first name and last name when new address is added on backend
    * Fixed an issue where it was not possible to create customer from backend if there was custom attribute
    * Fixed an issue where email could not be retrieved from Quote address after adding an address in backend order creation
    * Fixed XSS in order details
* GitHub issues and requests:
    * [#1389](https://github.com/magento/magento2/pull/1389) -- Replaced string check with simpler logic
    * [#1412](https://github.com/magento/magento2/pull/1412) -- Fix typo - change getChildhtml to getChildHtml
    * [#1415](https://github.com/magento/magento2/pull/1415) -- Add placeholder containers to invoice and shipment creation sections

0.74.0-beta15
=============
* Framework improvements:
    * Introduced Drag&Drop Columns functionality in scope of Enhanced Data Grids on CMS
    * Improved Column Actions functionality in scope of Enhanced Data Grids on CMS
    * Adapted Payment\Gateway framework to client requirements
    * Removed 'field_expr' option from filters
    * Added product details renderer list support on Catalog category page
    * Security: Clickjacking solution - introduced X-Frame-Options
    * Gift message was moved to shopping cart
    * Improved simple products export
    * Separated import of advanced prices
    * Changed 'updated_at' filter for products export
    * Added the link with sample product import file
    * Cleared and improved the  messages and names in different modules
    * Added mbstring extension as a requirement
* Tests:
    * Increased test coverage for the CatalogInventory, Email and Newsletter modules
    * Added wait to form element before filling a value in functional tests
    * Increased test coverage of Reports module
    * Functional tests were fixed and maintained
* Fixed bugs:
    * Fixed loading of images from database media storage with separate connection
    * Eliminated duplication of ComposerInformation class in Magento Framework and Setup Application
    * Fixed an error message format inconsistency in theme uninstall command
    * Fixed an issue where incorrect action name checks led to customer info leak
    * Fixed an issue where /magento_version exposed too detailed version information
    * Fixed an issue where generate data failed when table prefix exceeded length of 5
    * Fixed an issue where product options were displayed not styled on "Edit Product from Wishlist" Frontend page
    * Fixed an issue where payment information was displayed broken on "Order Management" Backend page forms
    * Fixed an issue where admin panel pop-ups contained lots of empty space
    * Fixed an issue where Customer account form was displayed broken
    * Fixed an issue where all text fields were invisible in Backend "Add New Customer" page
    * Fixed XSS issues in Magento - wishlist sending
    * Fixed an issue where it was unable to specify all values of "Multiple Select" Product Custom Option to purchase
    * Fixed an issue where setting a permission for 'ALL Groups' produced an error if other permissions existed
    * Fixed an issue where stock was not updated when Stock indexer was in Update on Schedule mode
    * Fixed an issue where it was not possible to update stock items of product using API service
    * Fixed an issue where Customer review changes in backend were not reflected in frontend until cache was cleared
    * Fixed an issue where cache was not updated when changing stock status using mass action
    * Fixed an issue where Stock Items API service to return low stock information did not return correct results
    * Fixed an issue where found records in global search in Backend could not be selected via keyboard
    * Fixed an issue where Category menu items went out of screen when page side was reached
    * Fixed an issue where subcategories in menu were shown instantly when user moved mouse quickly
    * Fixed an issue where popup header was our of window range while creating group product
    * Fixed an issue where region field was absent in customer address form on backend for "United Kingdom" country
    * Fixed an ability to edit the Order from Admin panel
    * Fixed an issue where email could not be retrieved from \Magento\Quote\Api\Data\AddressInterface after adding an address on OnePageCheckout
    * Fixed an issue where Products were not displayed correctly across all storeviews of the catalog
* GitHub issues:
    * [#1378](https://github.com/magento/magento2/issues/1319) -- jquery-cookie.js is not published by deploy tool in production mode
    * [#1314](https://github.com/magento/magento2/pull/1314)-- Fixed a bug where type attribute for tag button was missing
    * [#1354](https://github.com/magento/magento2/pull/1354) -- Add gitter.im badge to ReadMe.
    * [#1378](https://github.com/magento/magento2/pull/1378) -- Fix incorrect js filename

0.74.0-beta14
=============
* Framework improvements:
    * Introduced an ability to uninstall modules which were installed via composer (bin/magento module:uninstall 'moduleName')
    * Introduced an ability to uninstall themes (bin/magento theme:uninstall 'themeName')
    * Introduced an ability to backup and rollback DB and Media via CLI (bin/magento setup:backup, options are --code, --db or --media)
    * Introduced an ability to uninstall language packages (bin/magento i18n:uninstall 'languagePack')
    * Introduced API notation for the following modules: Backend, Backup, Cron, Log, PageCache
    * Added join processors to search services, joined config for services with extension attributes
    * Renamed hidden_tax to discount_tax_compensation
    * The customer address entity table was transformed from EAV into a flat model to minimize database operations
* Fixed bugs:
    * Fixed an issue where Setup Wizard failed on readiness check when Magento was deployed by composer create-project
    * Fixed the local file path disclosure when trying to browse image cache directory
    * Fixed an issue where development errors resulted in too many redirects
    * Fixed an integration test failure in Reports ViewedTest
    * Fixed an issue where it was impossible to save existent Grouped Product with no child items
    * Fixed an issue where message "We don't have as many "conf1" as you requested" appeared
    * Fixed an issue where second product from bundle product was ordered as separate item after checkout
    * Fixed an issue where configs for payments and shipping providers were not encrypted
    * Fixed an issue where Table Rates shipping method did not work
    * Fixed an issue where admin could not set locale properly on Account page
    * Fixed incomplete generated results of single tenant compiler
    * Fixed an issue with full page caching where one set of prices was cached for all customers
    * Fixed incorrect urls for private content
    * Fixed an issue where it was not possible to assign a product link to another product using API
    * Fixed an issue where zip code was not displayed as required field on Create New Order page
    * Fixed the Sample Data re-installation
    * Fixed random fails on inventory tab for test CreateSimpleProductEntityTest
* Tests:
    * Covered various modules with unit tests
    * Functional tests fixed and maintained
* GitHub issues:
    * [#1156](https://github.com/magento/magento2/pull/1156) -- Moves common code to all auto-generated Interceptor classes into a trait
    * [#1206](https://github.com/magento/magento2/pull/1206) -- Allow modules to live outside of app/code directory
    * [#1245](https://github.com/magento/magento2/pull/1245) -- Unable to save product per website wise
    * [#1347](https://github.com/magento/magento2/pull/1347) -- Fixed failing Install during integration tests (MAGETWO-38482)
    * [#1368](https://github.com/magento/magento2/pull/1368) -- Fix typo in getCurrentCategoryKey

0.74.0-beta13
=============
* Framework improvements:
    * Created Join Directive, Join Process for Tables, XML Config support to define a performance join for search services
    * Added support of field weighting for MySQL Search Engine
    * Modified indexer declaration to support field declaration
    * Model related methods and properties are removed from Magento Object
* Various improvements:
    * Added supporting of lost product types for Product Import/Export
    * Improved performance of Product Import/Export
    * Implemented Payment\Gateway infrastructure as a new design for payment methods
    * Fixed messages in Setup CLI
    * JS Smart fixed scroll
    * Improved sub-menu animation and sub-menu links mouse event effects
    * Automated UI Documentation build process with Grunt.js
    * Updated composer dependency to newer version
    * Implemented direct web link on Magento order transactions records
* Tests:
    * Reduced Travis CI integration test time
    * Increased test coverage for the Integration module
    * Re-structured unit tests for the updater app to follow the convention used by the rest of Magento code
* Fixed Bugs:
    * Fixed Help links in Install Wizard
    * Fixed an issue where composer install failed since ext-xsl was not available
    * Fixed web installer on HHVM
    * Fixed broken links to static assets when error occurs
    * Fixed failed integration tests on Travis CI builds
    * Fixed an issue where menu with one sub-menu item not being displayed
    * Fixed an issue where IPN messages did not show relevant info about transaction
    * Fixed an issue where Magento\Framework\Data\Form did not accept data-mage-init parameter
    * Fixed an issue where not all specified "Multiple Select" Bundle options were added to Shopping Cart
    * Fixed ConfigureProductInCustomerWishlistOnBackendTest functional test
    * Fixed an issue with all mandatory fields in the Sales data interfaces
    * Fixed an issue where billing and shipping sections did not contain address information on order print from Guest
    * Fixed an issue where orders placed in different store views had duplicated IDs
    * Fixed an issue where Shopping Cart Price Rules were not applying properly for Bundled products
    * Fixed an issue where column coupon_rule_name was not filled in the sales_order table when you create the order
    * Fixed an issue where customer registration or login on frontend created an empty cart
    * Fixed an issue where Product Model sometimes values change in getters methods
    * Fixed an issue where deleting option through API service for configurable product did not unlink variations
    * Fixed an issue where there was no ability to place order using multishipping if cart contained virtual product
    * Fixed an issue where "Terms and Conditions" was absent on order review step
    * Fixed an issue where grid actions for "Shopping Cart Items" grid was absent in Customer Account (Backend)
    * Fixed XSS vulnerability in Magento "Add to cart" link
    * Fixed UI issues on view order info frontend pages for guest customer
    * Fixed an issue where "Currency Rates" backend form was displayed broken
    * Fixed an issue where padding was missed for Custom Price Checkbox on "Create Order" Backend page
    * Fixed an issue where "Choose Variation" buttons lost alignment on "Create Configurable Product" Backend page
    * Fixed an issue where "Date & Time" Custom option was displayed broken on "Create Order" Backend page
    * Fixed an issue where colon was displayed before every Product Attribute label on Frontend
    * Fixed an issue where record from url_rewrite table was not removed when CMS page deleted
    * Fixed an issue where widget option "Number of Products to Display" did not work
    * Fixed validation message issues for CMS pages
    * Fixed an issue where "Click for Price" link was displayed in widgets for product with "Display Actual Price" != "On Gesture" MAP setting
    * Fixed an issue where Form_key cookie was not listed in privacy page
    * Fixed an issue where merchant wasn’t redirected to correspondent option when trying to enable Dashboard charts
    * Fixed an issue where wrong message was displayed after exceeding maximum failed login attempts
* GitHub issues:
    * [#1292](https://github.com/magento/magento2/pull/1292) Admin menu with 1 submenu item does not show the subitem
    * [#1133](https://github.com/magento/magento2/pull/1133) Getter methods shouldn't change values
    * [#1263](https://github.com/magento/magento2/issues/1263) "We don't have as many "product name" as you requested" not showing in mini cart
    * [#1284](https://github.com/magento/magento2/issues/1284) Order tracking link redirected to dashboard in admin

0.74.0-beta12
=============
* MTF Improvements:
    * Functional tests maintenance
* Framework improvements:
    * Customer entity table was transformed from EAV into a flat model to minimize DB operations
    * Improved admin authentication and removed bypass
    * Exposed CMS api's as web API
* Fixed bugs:
    * Fixed an issue where "Add Item To Return" button became disabled after required item fields were filled on Frontend
    * Fixed an issue with fatal error during place order with non default time zone
    * Fixed an issue where it was not possible to filter backups on name
    * Fixed an issue where routeIdType did not allow numbers
    * Fixed an issue with discounted prices for fixed bundle product
    * Fixed an issue with catalog prices not including custom option prices
    * Fixed an issue with tier prices being displayed 4 characters
    * Fixed an issue with extra FPT labels in mini shopping cart
    * Fixed an issue where it was not possible to place orders for products with FPT and catalog prices including tax
    * Fixed an issue with FPT attribute being required when creating product
    * Fixed an issue where final price was not recalculated after selecting product options
    * Fixed an issue where tax labels were not displayed for Bundle options on 'multi-select' and 'dropdown' controls
    * Fixed an issue where filters were not shown on product reviews report grid
    * Fixed an issue where second customer address was not deleted from customer account
    * Fixed an issue where custom options pop-up was still displayed after submit
    * Fixed an issue where Second Product was not added to Shopping Cart from Wishlist at first atempt
    * Fixed an issue where customer invalid email message was not displayed
    * Fixed an issue where All Access Tokens for Customer without Tokens could not be revoked
    * Fixed an issue where it was impossible to add Product to Shopping Cart from shared Wishlist
    * Magento_Sendfriend module should have upper case 'F'
    * Fixed set of issues with Ui module
    * Fixed JavaScript error on Invoice creation page
* Various improvements:
    * Hide payment credentials in debug log
    * Simplification of Payment Configuration
    * Introduced new Dialog widget
* Github issues:
    * [#1330](https://github.com/magento/magento2/pull/1330) -- Removing unused memory limit in htaccess
    * [#1307](https://github.com/magento/magento2/pull/1307) -- Corrected a sentence by removing a word

0.74.0-beta11
=============
* Framework improvements:
    * Improved component Bookmarks component in scope of Enhanced Data Grids on CMS
    * Improved component Advanced Filtering component in scope of Enhanced Data Grids on CMS
* Fixed bugs:
    * Fixed an issue where incorrect keys in REST request body allowed the request to go through successfully
    * Fixed an issue where interceptors were Generated with Invalid __wakeup()
    * Fixed an issue where redirect on the current page was not working in certain conditions
    * Fixed an issue where first store could not be selected on frontend
    * Fixed an issue with performance toolkit category creation
    * Fixed an issue when columns 'Interval', 'Price Rule' had incorrect values in Coupon Usage report
    * Fixed an issue where fatal error occurred on Abandoned Carts report grid
    * Fixed an issue where it was not possible to add product to shopping cart if Use Secure URLs in Frontend = Yes
    * Fixed an issue where email was not required during Guest Checkout
    * Fixed broken ability to skip reindex in `bin/magento setup:performance:generate-fixtures` command
    * Fixed an issue where `bin/magento indexer:reindex` command failed after `bin/magento setup:di:compile` was run
    * Fixed bug with broken JS i18n
    * Fixed an issue with wrong value at created_at updated_at fields after quote* save
    * Fixed an issue where customer could not be created in backend after adding Image type attribute
    * Fixed Sales InvoiceItem and Order data interfaces implementation
    * Fixed an issue with performance toolkit medium profile
    * Fixed an issue where Excel Formula Injection via CSV/XML export
    * Fixed an issue where it was not possible to open the Customers page in backend
    * Fixed an issue with internal server error after clicking Continue on Billing information
    * Fixed an issue where it was not possible to place order with Fedex shipping method
* Various changes:
    * Magento Centinel Removal
    * Removed ability to have multi-statement queries
* Test coverage:
    * Unit tests coverage
    * Covered php code by unit tests after new checkout implementation
* Github issues:
    * [#424](https://github.com/magento/magento2/issues/424) -- Combine tier pricing messages into block sentences
    * [#1300](https://github.com/magento/magento2/issues/1300), [#1311](https://github.com/magento/magento2/issues/1311), [#1313](https://github.com/magento/magento2/issues/1313) -- Creating product error with startdate

0.74.0-beta10
=============
* Framework improvements:
    * Created Admin Login support into the Upgrade Setup Tool
    * Added support for image types as custom attributes
    * Added @api annotations to all classes that are considered as stable public APIs; marked public data interfaces with '@api'
    * Defined Public API for modules: Catalog, CatalogRule, Msrp, UrlRewrite, CatalogUrlRewrite, CmsUrlRewrite, Sales, Quote, SalesRule, Captcha, Cms, Widget
    * Created documentation and code samples for Sales & Checkout Module Integration APIs
    * Increased code coverage of Integration module
    * Fixed performance issues in unit tests
    * Moved Sample Data install.php, dev/shell/cron.sh, performance-toolkit/generate.php, dependencies tools, and xmlUpdater to new bin/magento CLI
    * Separated Config File (config.php) into Environmental and App configs
    * Better validation of install and setup CLI commands
    * Changed option names to use dashes in order to conform to naming convention
* Code quality improvements:
    * Removed unused classes in Magento\Reports module
    * Overall unit test coverage was improved
    * Replaced all functional tests which were not end-to-end with injectable test
    * Replaced end-to-end test for automatic tax applying with injectable test
    * Functional tests maintained
    * Updated getElements method for Selenium Driver class in MTF
    * Implemented mechanism of cleaning up all data after scenario execution
    * Fixed integration testLayoutFilesTest
* Design improvements:
    * New look&feel for Collapsible Panels in backend
    * New look&feel for System Warnings Pop-Ups in backend
    * New look&feel for Grid Tables in backend
* Various improvements:
    * Implemented checkout UI rendering in browser
    * Added exact image sizes provision in templates
    * Added width and height attributes to Frontend logo
    * Integrated JIT(Just In Time) plugin loader for Grunt.js
    * Handling Invalid Less During PHP Compilation
    * Enhanced PageCache invalidation
    * Private data rendering in browser based on JSON data obtained from server side and kept in Local Storage instead of HTML obtained using AJAX PageCache action
    * Refactor blocks on most frequently used pages (Catalog, CMS) to use new private data rendering mechanism
    * Refactor blocks which can be added on any page (like widgets, banners) to use new private data rendering mechanism
    * Default PageCache entries TTL value increased from 2 minutes to 24 hours
    * Cache entries invalidation logging was introduced in order to simplify running of invalidation process
* Fixed bugs:
    * Fixed an issue where orders total report was not showing results grouped by day
    * Fixed an issue with non-displaying Abandoned Carts report grid
    * Fixed integration test failure in Reports GridTest
    * Fixed an issue where fixed bundle product could not be created
    * Fixed an issue where grouped product with quantity 0 was added to cart with quantity of 1
    * Fixed an issue where versions tab was absent after publish CMS page
    * Fixed an issue where shopping cart was empty after attempt to update it
    * Fixed an issue where there was no redirect to shopping cart after EDIT/UPDATE cart product if custom options
    * Fixed an issue where New Accounts report did not work
    * Fixed bug when Admin user wasn't locked after exceeding maximum login failures attempts
    * Fixed an issue where downloadable Product detailed info wasn't displayed in Cart & on Checkout
    * Fixed an issue with wrong copyright year for store front
    * Fixed: JSMinException when deploying static files in production mode with minification enabled
    * Fixed: Overlapped FPT Attribute in Mini Shopping Cart
    * Fixed: View of current item for Customer menu tabs
    * Fixed an issue where status label was not refreshed when disabling/enabling cache
    * Fixed an issue where Success Page of Web Setup did not show https
    * Fixed an issue with irrelevant/unused template
    * Fixed an issue where static content running setup:static-content:deploy with language code {a-Z}{a-Z}{a-Z} couldn't be generated
    * [Usability] Output from language, and timezone help commands is alphabetized
    * Fixed an issue with two identical IdentityInterface definitions in code base.
    * Fixed an issue where default value of timezone in installer was not correct
    * Fixed an issue where bank Transfer payment instructions were not displayed
    * Fixed an issue when New Accounts report did not work
    * Fixed an issue when New Accounts report showed invalid data
    * Fixed an issue with Exception in setup wizard after Magento is installed
    * Fixed visual misfits for Custom Options on "edit Bundle Product" Frontend page
    * Fixed broken layout for configurable variations when changing from pricing measure from $ to %
    * Fixed widget UI issues on Frontend
    * Fixed an issue with Float button bar on Backend
    * Fixed UI issue shown in ACL Resource Tree
    * Fixed an issue where it was not possible to place order on frontend using secure urls
    * Fixed an issue with wrong behaviour for save new shipping address during checkout
    * Fixed an issue with customers information leak via Checkout
    * Fixed an issue where users could not login to Magento admin & front on HipHop Virtual Machine
    * Fixed an issue where product Options details were not shown in Mini Cart
    * Fixed an issue where Create Permanent Redirect check-box was inactive on Edit Category page
    * Fixed an issue where it was impossible to perform product mass update
    * Fixed an issue where category redirect was absent after update category url
* Github issues:
    * [#566](https://github.com/magento/magento2/issues/566) -- Fulltext search index: slow query in resetSearchResults()
    * [#1269](https://github.com/magento/magento2/issues/1269) -- Magento dashboard revenue ,shipping ,qty,tax all are 0.
    * [#1200](https://github.com/magento/magento2/issues/1200) -- I'm not getting default values (name & email) in contact form when i logged in
    * [#1087](https://github.com/magento/magento2/pull/1087) -- Check for select and multiselect to ignore corrupted attributes
    * [#1268](https://github.com/magento/magento2/issues/1268) -- Dist front: search is broken
    * [#1195](https://github.com/magento/magento2/pull/1195) -- Use table alias for qty field
    * [#1274](https://github.com/magento/magento2/pull/1274) -- CatalogImportExport validation
    * [#1233](https://github.com/magento/magento2/issues/1233) -- Wrong message when moving a category
    * [#1040](https://github.com/magento/magento2/issues/1040) -- Required date validation on Magento2 Backend
    * [#1246](https://github.com/magento/magento2/issues/1246) -- How to load product store wise?
    * [#1222](https://github.com/magento/magento2/issues/1222) -- Sku attribute save error
    * [#1237](https://github.com/magento/magento2/issues/1237) -- Category admin reset button does nothing
    * [#1046](https://github.com/magento/magento2/issues/1046) -- Two equal files
    * [#1282](https://github.com/magento/magento2/pull/1282), [#1285](https://github.com/magento/magento2/pull/1285) -- Fix broken link in CHANGELOG.md
    * [#1223](https://github.com/magento/magento2/issues/1223) -- Store config re-encrypt encrypted values on save
    * [#1242](https://github.com/magento/magento2/issues/1242) -- Eclipse pdt validator error

0.74.0-beta9
=============
* Framework improvements
    * Magento became compatible with MySQL Cluster
    * Zend Framework 2 is upgraded up to version 2.4.0
* Various
    * Updated payments infrastructure so it can use transparent redirects
    * Defined public API for Tax/Pricing components
    * Refactored controller actions in the Product area
    * Moved commands cache.php, indexer.php, log.php, test.php, compiler.php, singletenant\_compiler.php, generator.php, pack.php, deploy.php and file\_assembler.php to the new bin/magento CLI framework
* Data Migration Tool
    * The Data Migraiton Tool is published in the separate [repository](https://github.com/magento/data-migration-tool-ce "Data Migration Tool repository")
* Fixed bugs
    * Fixed an issue where error appeared during placing order with virtual product
    * Fixed an issue where billing and shipping sections didn't contain address information on order print
    * Fixed an issue where fatal error appeared on Catalog page on backend for user with custom role scope
    * Fixed an issue where product could not be found in search results when the website was assigned after product creation
    * Fixed an issue where shopping cart was empty after attempt to update it
    * Fixed an issue where there was no redirect to shopping cart after edit/updating cart product with custom options
    * Fixed an issue where environment variables were messed up for different entry points
    * Fixed an issue where tax class name was corrupted if containing '<' char
    * Fixed an issue where there was no ability to place an order with custom option "file"
    * Fixed an issue where sensitive cookies were persistent
    * Fixed possible XSS in payment methods
    * Fixed an issue with integration test failure when run in default mode
    * Fixed an issue with integration tests failure when xdebug is enabled
    * Fixed an issue where there was impossible to delete any entity which calls confirmation alert
* GitHub issues and pull requests
    * [#904](https://github.com/magento/magento2/issues/904) -- Installation Incomplete with XDebug enabled
    * [#1083](https://github.com/magento/magento2/pull/1083) -- Move Topmenu CategoryData creation to a public method to enable plugin
    * [#1125](https://github.com/magento/magento2/pull/1125) -- Saving category reset its changes in category tree
    * [#1144](https://github.com/magento/magento2/pull/1144) -- Refactor bindRemoveButtons for improved performance
    * [#1214](https://github.com/magento/magento2/pull/1214) -- Avoid following error
    * [#1216](https://github.com/magento/magento2/issues/1216) -- Can't install sample data

0.74.0-beta8
=============
* Performance Toolkit improvements
    * Added order generator
    * Added indexer mode switcher via profile config
* UI Improvements
    * Added hide/show columns for CMS pages/blocks grid on backend
    * Updated the multi-select functionality & UI for CMS pages/blocks grid on backend
    * Added the new look & feel for Edit Order Page (view/edit order)
* Framework Improvements
    * Updated API framework to support different integration object ACLs
    * Updated unit and integration tests config files to include tests from the Updater Application
    * Exceptions caught and logged before reaching Phrase::__toString() method
* MTF Improvements
    * Replaced end-to-end One-page Checkout test with online shipment methods with scenario test
    * Replaced end-to-end Layered Navigation test with injectable test
    * Replaced end-to-end Shopping Cart price rule test with injectable test
    * Replaced end-to-end Switch Currency test with injectable test
    * Fixed the filling condition element
    * Updated a set of functional tests
* Various
    * Eliminated functional logic in constructors
    * Updated public API definitions
    * Added information for Downloadable Products to Catalog Product Data Object
    * Added information for Catalog Inventory data to Catalog Product Data Object
    * Added information for Grouped Products to Catalog Product Data Object
    * Added information for Configurable Products to Catalog Product Data Object
    * Cleaned Tax API data interfaces
    * Removed OptionTypesListInterface and type field in OptionInterface
* Fixed bugs
    * Fixed an issue with focus state appearing on click event in Admin Menu logo.
    * Fixed an issue where order was placed via Payflow link without providing credit card data
    * Fixed an issue where titles were displayed for backend navigation menu group when it only contained a single section
    * Fixed an issue where REST URL paths were not case-sensitive
    * Implement transparent redirect API
    * Fixed an issue in cron.php with checking for functions which are disabled in php.ini
    * Front-end development workflow settings scope changed to Global
    * Fixed an issue with widget title escape
    * Fixed the filename filtering
    * Fixed an issue with universal fatal error in the profiler option #2
    * Fixed an issue when shipping address in backend could not be changed when creating order
    * Fixed the performance issue with tax rules creation
    * The extended attributes became optional
    * Fixed an issue where final price did not recalculate when option was selected
    * Fixed an issue with price currency symbols
    * Fixed an issue when low_stock_date showed incorrect data
    * Fixed an issue with random integration test failure
* GitHub issues
    * [#526] (https://github.com/magento/magento2/issues/526) -- Area Sessions: Magento 2 Should not Allow "area-less" Sessions During an Area Aware Request
    * [#1212] (https://github.com/magento/magento2/issues/1212) -- Magento 2 0.74.0-beta5 unable to open home page after successful installation
    * [#1213] (https://github.com/magento/magento2/issues/1213) -- Magento 2 0.74.0-beta6 unable to open home page right after successful installation
    * [#1157] (https://github.com/magento/magento2/issues/1157) -- Something went wrong with the subscription
    * [#1228] (https://github.com/magento/magento2/issues/1228) -- PDOException during attempt to export products: Unknown column 'entity_value.entity_type_id' in 'on clause’

0.74.0-beta7
=============
* Framework improvements
    * Exceptions are caught and logged before reaching the Phrase::__toString() method
    * Refactored controller actions in the Checkout area
    * Refactored controller actions in the Tax area
    * Implemented new look & feel for the Edit Order page (View/Edit Order)
    * Replaced the end-to-end test for Onepage Checkout with online shipment methods with the scenario test
* Fixed bugs
    * Fixed an issue where a success message was absent when adding a product with options from Wishlist to Shopping Cart
    * Fixed an issue where an exception was thrown when trying to sort Customer Groups by Tax Class
    * Fixed an issue where the background color changed to the “on focus” state when clicking  the Admin Menu logo
    * Fixed an issue with Mini Shopping Cart containing extra empty space
* GitHub issues
    * [#1173] (https://github.com/magento/magento2/pull/1173) -- Change to HttpClient4 from Java client; fix regex issues
    * [#1185] (https://github.com/magento/magento2/pull/1185) -- Error message for duplicated phrases not allowed in Generator.php
    * [#1199] (https://github.com/magento/magento2/pull/1199) -- Add Event for sales_order_state_change_before during Order->saveState()
    * [#1201] (https://github.com/magento/magento2/pull/1101) -- Add customer_validate event
    * [#1202] (https://github.com/magento/magento2/pull/1102) -- Email sending events

0.74.0-beta6
=============
* Framework improvements
    * Implemented a default exception handler for blocks
    * Updated the root composer.json file
    * Updated the setup tool to support different editions
    * Added an ability to operate with Sales & Checkout APIs as guests and registered users
    * Implemented the additional Sales & Checkout APIs for registered customers and guests
    * Added unit tests to cover Sales & Checkout services code
* Various
    * Standardized the hierarchy of exceptions
    * Added bundle product API integration to Catalog
* Fixed bugs
    * Fixed an issue where it was impossible to place an order using multiple address checkout
    * Fixed an issue where DB timestamp columns with current_timestamp on update were not handled correctly
    * Fixed an issue with FPT in partial invoices
    * Fixed a performance issue in benchmark test
    * Fixed the incorrect Exception class in the Magento_CurrencySymbol module
    * Fixed an issue by letting MySQL determine a database table type instead of MyISAM
    * Fixed an issue where test failures occurred when the database and the application were in different time zones
    * Fixed an issue where \Magento\Framework\Phrase omitted placeholder values if no renderer was set

0.74.0-beta5
=============
* Various
    * Added the new methods/fields in the Catalog Product Data Object
    * Improved the Nginx configuration sample file for better web-server responsiveness and security
    * Implemented the new look & feel for Create New Order page
    * Removed the redundant DB constraints for cascade operations related to order management
    * Implemented the mechanism of asynchronous email notifications after creation of Orders, Invoices, Shipments and Credit Memos
    * Moved the join logic on application level in order to make DB separation possible in Reports component
    * Implemented the TTL and event approaches of cache invalidation, introduced the full and the partial Varnish Cache flush
    * Moved all Setup commands to Magento CLI
    * Exposed CMS API as Web API
* Fixed bugs:
    * Unexpected response for API "/V1/customers/password" service
    * Can’t include a third-party link to frontend section via layout
    * Specified details for Grouped product are lost after adding to wishlist
    * Impossible to configure products in customer wishlist in Admin Panel
    * Adding the product from wishlist to cart if more than one store view exists
    * Specified product field custom options is not displayed in wishlist in Admin Panel
    * Checkout doesn't work with JS bundling enabled in production mode
    * Issue with price excluding tax when selecting downloadable links
    * Undefined index warning in case the frontend cache information is missing in configuration file
    * "New Order" email is not sent to customer after placing order via API service
    * 503 error when placing order with multiple shipping addresses if mail transport doesn't exist
    * Broken words for fields with long labels all over the Admin Panel
    * Issue with saving 'is_virtual' flag in quote
    * "Void" button available after "Deny Payment" operation
    * Uninstall logic did not clean cache properly
    * Obsolete code tests did not cover Tests folders
    * Random fail of Magento\Log\Test\Unit\Model\VisitorTest
* GitHub issues:
   * [#1149] (https://github.com/magento/magento2/issues/1149) -- Checkout Grand Total amount miscalculation
   * [#1165] (https://github.com/magento/magento2/pull/1165) -- Fix typos
   * [#1182] (https://github.com/magento/magento2/pull/1182) -- Update system.xml for 'fix' sortOrder in adminhtml
   * [#1186] (https://github.com/magento/magento2/pull/1186) -- SalesSequence: Fixed composer installer dependency

0.74.0-beta4
=============
* Various
    * Implemented the getDefaultResult method, to be able to catch exceptions in FrontController and redirect user to the correct page
    * The getDefaultResult method is invoked to return default result of action execution within controllers. It can be used to generate the ‘execute’ method result in action controllers
    * Eliminated the unused exceptions. Exceptions that weren't linked to any logic were also eliminated and replaced with LocalizedException or its child classes
    * Refactored all controllers where possible: the default exception handling logic moved to FrontController. Controllers that cannot be refactored do not conflict with the new logic
* Framework:
    * Created Magento Console to perform CLI actions
    * Introduced a new SalesSequence module that is responsible for documents numeration management across the Order Management System
    * Implemented the mechanism of asynchronous indexing of sales entities grids
* Setup
    * Added the ConfigOption and ConfigOptionsList classes to be used by modules to manage deployment configuration
    * Moved all existing segments logic to new classes
    * Added the config:set command, which enables deployment configuration management
    * Removed the old 'install-configuration' tool
* Functional tests:
    * Fixed functional test for order placement from backend
    * Replaced the end-to-end test for a product with MAP with an injectable test
* Design
    * Updated the Blank and Luma themes to enable theme (not only library) variables overriding in the _theme.less file of any inherited theme. Included LESS code standards to the UI Library documentation
* Fixed bugs:
    * Fixed an issue where composite products could not be added to the order from the Recently Viewed Products section
    * Fixed an issue where not all .js files were added to a bundle
    * Fixed an issue where it was possible to save an incorrect IP value in the Developer Client Restriction field
    * Fixed an issue where a raw DB error was thrown when trying to enter a custom variable with duplicated variable code

0.74.0-beta3
=============
* API
    * The orders were extended with the gift messages
    * The page and block data and repository interfaces
    * Updated the public API list
* Framework improvements
    * Improved the profile generator
    * Introduced the new environment for Jasmine tests
* Design
    * Inverted the new admin area styles scope, clean up the old styles
    * New Side Panels on Admin Area
* Various
    * Asynchronous indexing for sales grids
    * Advanced Mini Cart
    * The HTML minification management on Admin Area
    * Minor UI improvements
    * The GitHub contribution process was updated in the README.md file
* Fixed bugs
    * Fixed the assets deployment tool with the minification
    * Fixed the JMeter scenario for the performance toolkit
    * Fixed the static files caching on Varnish
    * Fixed Admin user creation with the duplicated email or name (incorrect URL)
    * Fixed the link on Reset password email for secure URL case
    * Fixed the configured product adding from the wish-list to shopping cart
    * Fixed the long labels display on Admin Area
    * Fixed the Navigation Menu items on Admin Area
    * Various unit and integration tests bugs
* GitHub issues and requests
    * [#675] (https://github.com/magento/magento2/issues/675) -- Fix for Textarea element cols and rows #675

0.74.0-beta2
=============
* Fixed bugs
    * Wrong capitalization of the label names (the sentence-style capitalization instead of the headline style)
    * Inconsistency in the labels in the Admin panel
    * Customer menu tabs aren't displayed as selected for the child pages
    * An issue with the Active item in the navigation menu in the Blank and Luma themes
    * Incorrect price alignment during checkout in the Blank and Luma themes
    * Broken field "URL" in the Downloadable product in the Admin panel
* GitHub issues and requests:
    * [#1096] (https://github.com/magento/magento2/issues/1096) -- Customer model - getPrimaryAddresses without primary billing address
    * [#1114] (https://github.com/magento/magento2/issues/1114) -- GA bug
    * [#1116] (https://github.com/magento/magento2/issues/1116) -- Incorrect use of implode()
    * [#1126] (https://github.com/magento/magento2/pull/1126) -- Fixed occurrences of implode with wrong argument order
    * [#1128] (https://github.com/magento/magento2/pull/1128) -- Change wording for long operation warning

0.74.0-beta1
=============
* Various
    * Inline JS code was eliminated
    * Fixed XSS vulnerability issues
    * The "Last login time" functionality was moved from the `Magento_Log` module to the `Magento_Customer` module
    * Implemented two-strategies JavaScript translation
    * Improved backend menu keyboard accessibility
    * Accessibility improvements: WAI-ARIA in a product item on a category page and related products
    * Checkout flow code can work with a separate DB storage
    * <a href="http://devdocs.magento.com/guides/v1.0/release-notes/changes.html#change-devrc-unit">Unit tests moved to module directories</a>
    * Addressed naming inconsistencies in REST routes
    * Added Advanced Developer workflow for frontend developers
* Setup
    * Utilized Magento error handler in the Setup application to convert errors and warnings to exceptions
    * Fixed an issue when private content handling did not work with enabled HTML profiler and developer mode
    * Fixed an issue where Magento Composer Installer failed to uninstall last package
    * Fixed an issue where a fatal error was thrown in the Setup application after running composer install with the `--no-dev` option
    * Fixed a JavaScript issue with expanding the list of modules on the  Customize Your Store step in the Setup Wizard
    * Fixed a JavaScript issue with returning from the Create Admin Account step to the Customize Your Store step in the Setup Wizard
* Framework
    * Added a new `Magento_MediaStorage` module to store components of the `Magento_Core` module
    * Implemented JavaScript resources bundling (server side pre-processing)
    * Replaced `Zend_Locale` with native PHP implementation
    * Replaced `Zend_Date` with native PHP `DateTime` object/functions
    * Refactored Magento\Framework\Exception\LocalizedException
    * Renamed Magento\Framework\Validator\ValidatorException
    * Renamed Magento\Framework\Controller\Result\JSON to meet PSR standard
    * Updated the oyejorge/less.php library to the latest version
    * Refactored WebApi framework to support particular types for custom attributes
    * Version used in SOAP declarations is now taken from routes declared in webapi.xml
    * Added ability to extend API data interfaces using extension attributes
    * Removed the `Magento_Core` module
* Web API Framework
    * Factories are used instead of builders
    * Removed auto-generation of builders
    * Made `interfaceName` a required parameter in `Magento\Framework\Api\DataObjectHelper::populateWithArray` method
* Performance
    * Increased caching coverage of Magento storefront pages: Cart, Register, Login, My Account
    * Finished work around <a href="http://hhvm.com/" target="_blank">HHVM compatibility</a>
    * Fixed EAV caching on storefront
    * Optimized dependency injection compilation for interception
* Design
    * New design for the Magento Admin
    * New message design in Setup Wizard
    * New design for Minimum Advertised Price (MAP) on storefront catalog pages
* Fixed bugs
    * Catch syntax error in `module.xml` files
    * Profiling of cache operations was permanently disabled
    * Session was not cleared when layout is cached
    * Page cache was invalidated by cron jobs after reindexing, even when nothing was changed
    * Typo in method name in `Adminhtml/Index/Grid.php`
    * Missing validation of table prefix in Step 2: Add a Database in the Setup wizard
    * User hint of password strength validator in Web Setup wizard now consistent with the algorithm used
    * New Logger did not format exception and debug info correctly
    * Wrong styles structure
    * Customer is redirected to shopping cart by clicking on mini-shopping cart after adding product
    * Gift Message information for Order level is not presented on storefront or Admin orders
    * Wrong `customer_id` value for GiftMessages created using API service
    * No ability to place order for guest customer using API service
    * Shopping Cart displayed partly broken if it contained a Product with an image as a custom option
    * Impossible to add product to the shopping cart with Custom option of `type="file"`
    * Adding to cart dialog widget with MSRP price on product page was broken
    * Copy and paste detector is run against test files that are blacklisted
    * Displayed the wrong price on product page when selecting an option for configurable product
    * Tax amount (tax on full shipping) is refunded when partial shipping amount is refunded
    * Price (including tax) is shown on product page when configuration is set to display excluding tax
    * Fixed Product Tax (FPT) is not applied in shopping cart and orders for registered users
    * FPT not applied for registered users when FPC is disabled
    * "All categoryName" menu link is absent, subcategories are shown on hover of parent category
    * Horizontal scrolling displays when browser width is resized to mobile size
    * Broken design for "select store" element in CMS grid filter
    * Attribute value uniqueness isn't checked for custom product template
    * Category tree is not displayed in conditions for Catalog Price Rules
    * Remove hard-coded IDs from catalog API code
    * Bottom margin for "Wishlist Search" widget
    * Custom option image with limits view for storefront
    * Category page displayed outdated prices after catalog price rule was deleted
    * Cart quantity is more than in-stock amount
    * Page layout configuration cannot be extended or overridden by the theme
    * Page layout with custom set of containers causing fatal error
    * Reset password e-mails requested from second store view has link and name of the first main store
    * Cannot place order for virtual product with customer address attribute from Admin
    * Specified details for bundle product are lost after adding to wishlist
    * Customer address is set to non-default after changing account information
    * Unable to save newsletter subscription information of customer in Admin
    * Guest can't add product to wishlist while registering
    * Cron job for Shipping
    * Solution for issue with attributes with list of countries
    * Unable to generate variations while creating a configurable product
    * Variations are created with out of stock status if configurable product has been switched from simple product
    * Impossible to search Downloadable product using file title
    * Change order of loading integration tests (load config annotations before fixtures)
    * Impossible to upload files in configuration
    * Price displaying on product page for bundle product
    * Display bug for tier prices
    * Required marker is displayed on wrong line in Admin
    * Categories' titles in storefront navigation Menu overlap "expand" button on mobile
    * Admin Login form alignment issues with IE9
    * Display check boxes on Update Attributes page using a mass action
    * Removed Test\Unit from cached dependency injection configuration for performance reasons
    * Impossible to place order with DHL EU shipping method
    * Updates while tables recreation in setup process
    * Pagination issues in the Downloadable Products tab page in a customer account
    * Adding existing attribute on New Product page
    * "Manage Stock" is not saving for bundle product
    * Filter did not work for Order Total report
    * Error on reports for Order Totals if grouped by Year
    * Customer can't find order on storefront
    * Postal code is still mandatory for non-US addresses that don't use it
    * Price of simple product isn't recalculated after selecting options on product page
    * Don't load bundle quantity from options on bundle page
    * Impossible to remove added row from "Minimum Qty Allowed in Shopping Cart"
    * Impossible to add to the cart a product with required Custom Options of "Field" and/or "Area" type
    * Syntax error in New Shipment email template
    * Removed `adminhtml`-only web service route for using customer user password reset tokens and setting new passwords
    * Removed the relevant URL Rewrites configuration after removing a category
    * Static obsolete code test did not recognize partial namespaces
    * Magento breaks when set specific locale
    * Impossible to update Gift Message from Admin
    * Impossible to create configurable product
    * Impossible to create new attribute using the Product Creation page
    * Product Template page did not work in IE9 and FF
    * Product image could added only after double-click in IE9
    * Inconsistent timestamp return for Admin timezone
    * 404 page is displayed on any action with order that it viewed under guest
    * "500 Internal Server Error" in case of excess "Maximum Qty Allowed in Shopping Cart" value
    * MAP link is displayed for a product on category page after delete Catalog Price Rule
    * Deploy script modifies LESS files with `@urls-resolved: true`
    * Zip code field is missing in customer addresses in the Admin
    * Impossible to add bundle product with required option to shopping cart without selecting all available options
    * Empty email is sent when a registered user changes password in the storefront
    * Tabs widget does not initialize sometimes on Product Creation page
    * Fatal error when trying to send notify customer by email about shipment
* Tests
    * Fixed an issue with `WebDriverException` for iframes in functional tests
    * Added functional test for Admin menu navigation
    * Replaced end-to-end test for online one-page checkout with injectable test
    * Replaced end-to-end test for administrator user with injectable test
    * Replaced end-to-end test for catalog price rule with injectable test
    * Replaced end-to-end test for store view with injectable test
    * Increased integration tests coverage for `Magento_Indexer` module
    * Increased unit test coverage for `Magento_Cms`, `Magento_Email`, and `Magento_Sales` modules
* GitHub issues and requests:
    * [#533] (https://github.com/magento/magento2/issues/533) -- Remove Allow all access in .htaccess
    * [#850] (https://github.com/magento/magento2/issues/850) -- HTML Profiler and pub/static Resources
    * [#919] (https://github.com/magento/magento2/issues/919) -- System information error when error is fixed but page wasn't refreshed
    * [#987] (https://github.com/magento/magento2/pull/987) -- Fix mod_expires for dynamic content
    * [#1004] (https://github.com/magento/magento2/issues/1004) -- Problem with template luma
    * [#1014] (https://github.com/magento/magento2/issues/1014) -- php index.php update - Class Magento\Store\Model\StoreManagerInterface does not exist
    * [#1015] (https://github.com/magento/magento2/issues/1015) -- After success setup/index.php update - "Missing required argument $engines of Magento\Framework\View\TemplateEngineFactory"
    * [#1016] (https://github.com/magento/magento2/issues/1016) -- Backend Javascript Errors (new installation)
    * [#1020] (https://github.com/magento/magento2/issues/1020) -- Bug generating Sitemap Cron expression
    * [#1029] (https://github.com/magento/magento2/issues/1029) -- Admin dashboard Most Viewed Products Tab issue (without product list)
    * [#1035] (https://github.com/magento/magento2/issues/1035) -- Bug in Magento\Framework\Simplexml\Element::appendChild
    * [#1042] (https://github.com/magento/magento2/issues/1042) -- Lost catalog rewrite url after page/list-mode/limit changed
    * [#1045] (https://github.com/magento/magento2/issues/1045) -- Bad rendering frontend category menu
    * [#1048] (https://github.com/magento/magento2/pull/1048) -- Make possible to upload SVG logo by admin
    * [#1052] (https://github.com/magento/magento2/pull/1052) -- Fix history cleanup for missed cron jobs
    * [#1062] (https://github.com/magento/magento2/pull/1062) -- Add check to see if PHP > 5.6 and always_populate_raw_post_data = -1
    * [#1082] (https://github.com/magento/magento2/pull/1082) -- Fix incorrect variable name ($schema -> $scheme)
    * [#1086] (https://github.com/magento/magento2/issues/1086) -- Email message containing non English character is displayed incorrectly on the receiver
    * [#1088] (https://github.com/magento/magento2/pull/1088) -- Add developer mode example to .htaccess
    * [#1107] (https://github.com/magento/magento2/issues/1107) -- Serious security issue in Customer Address edit section

0.42.0-beta11
=============
* Various improvements:
    * Added LICENSE to Developer module
    * Refactored Catalog and related module to use mutable data object interface
    * Refactored Sales and related modules to use mutable data interfaces
* Setup:
    * Added styles for new module enabling / disabling section in Installation wizard
    * Modules Install and upgrade capabilities are refactored to implement interfaces used by Setup application
* Framework:
    * Moved/refactored Magento\IO\Sftp adapter into Filesystem library
    * Removed Magento IO library
    * Implemented Dynamic Types Binding in SOAP
    * Implemented Extensible Attributes generation
    * Improved Web API Related Code Quality
    * Moved Specific Helper Components From the Magento/Core Module to Magento/Framework
* Performance:
    * Inline JS code is eliminated
    * Created fixture for changing Magento config update via Performance Toolkit Generator
* Fixed bugs:
    * Issue with  multiple addresses checkout
    * Issue with catalog page update after a product status changes
    * Issue with distance between "Log in" & "or" & "Register" in Frontend header
    * Issue with tax details and amounts in frontend and backend order
    * JavaScript error when clicking on toggle arrow to show FPT in shopping cart
    * PHP Warning when trying to checkout with Multiple Addresses on review order page
* Functional tests:
    * Refactored end-to-end test for order placement from backend and for OnePageCheckout
* GitHub requests:
    * [#1035] (https://github.com/magento/magento2/issues/1035) -- Bug in Magento\Framework\Simplexml\Element::appendChild
    * [#1053] (https://github.com/magento/magento2/issues/1053) -- #865: add getParams/setParam to RequestInterface
    * [#1066] (https://github.com/magento/magento2/issues/1066) -- PHP 5.5.16-1+deb.sury.org~precise+1

0.42.0-beta10
=============
* Framework
    * Replaced \Magento\Framework\Model\Exception with LocalizedException
    * Replaced obsolete exceptions
    * Config components are moved to new Magento_Config module
    * Variables components are moved to new Magento_Variable module
    * Preferences, shared instance creation and compiled factory accelerated by 3%
    * Fixed "HEADERS ALREADY SENT" error when controller action outputs directly
* Setup
    * Added ability to install and upgrade components in Setup Tool (CLI)
    * Added ability to manage list of modules in Setup Wizard
    * Fixed error after "Start Readiness Check" button press in Setup Wizard
    * Fixed error with Base URL change after "Previous" button press on Step 4 in Setup Wizard
    * Fixed error with HTTPS options related to auto-filled Base URL on Step 3 of Setup Wizard
    * Fixed error in "app\design\frontend\Magento\luma\composer.json"
* LESS Compiler
    * Added automatic update in "pub/static" of changed "css/js/images" after materialization
    * Variable names are renamed to meet Magento Code Standards
    * Added client-side LESS files compilation to reduce page load time in developer mode
* Widgets
    * Fixed fatal error on page which contain a widget with a condition based on "Category"
* GitHub requests
    * [#899](https://github.com/magento/magento2/issues/899) -- When accessing any category - error report generated
    * [#986](https://github.com/magento/magento2/pull/986) -- Make it possible to exclude classes (directories) for compilation
    * [#1054](https://github.com/magento/magento2/pull/1054) -- Fix typo in MAGE_MODE

0.42.0-beta9
=============
* Framework Improvements:
    * Layout Models are moved from Core module to appropriate modules
    * View components are moved from Core to Theme module
    * Rest of theme related configuration files are refactored
    * StoreManagerInterface is moved from Framework to App folder
    * ZF1 controller libraries are updated
    * Class definitions in multi-tenant mode are removed
    * DI configuration became more optimal: OM cached configuration uses the general pattern for all argument types in application
    * Varnish 4 configuration is updated
    * Layout Processing became more fast
    * HTML response minified
    * App Components and Specific Helper Components are moved from the Magento_Core Module
* UI improvements:
    * Add to cart operation became asynchronous and doesn`t reload page (AJAX call)
* Fixed Defects:
    * When Inline Translation is enabled, JQuery buttons for translate were broken
    * Base URL has invalid place inside Magento Admin Address on "Web Configuration" step of installation wizard
    * Inability of submit Product from keyboard while Product Creation
    * Sold products aren't displayed in Bestsellers
    * Compiled definitions can cause unexpected errors compared to runtime definitions
* Accessibility improvements:
    * WAI-ARIA attributes are added to Frontend Layered Navigation and Customer Dropdown, Frontend Product Page Tabs, Frontend Cart Summary collapsible panels, Frontend forms and notifications, Frontend Checkout pages
* Tests improvements:
    * Added mechanism of replacing 3-rd party credentials in functional tests
    * Update of end-to-end tests for create product, update product, promoted product, out of stock product, create product with new category, unassign products on category, create backend customer with injectable test
* Various improvements:
    * JS template engine became unified on Backend and Frontend
    * Increased unit test coverage for Magento/Indexer module
    * Version number info became accessible at a public URL
* GitHub requests:
    * [#1027](https://github.com/magento/magento2/issues/1027) -- Can't add new subcategory
    * [#921](https://github.com/magento/magento2/issues/921) -- Change resource ids from Magento_Adminhtml::* to Magento_Backend

0.42.0-beta8
=============
* Various improvements:
    * Existing Builders were replaced with DataFactories in Customer and Tax modules
    * Refactored controller actions in the Checkout and CMS modules
    * Increased coverage with static tests for `.phtml` files
    * Moved Cookie related functionality from `Theme` and `Core` modules into a new `Cookie` module
    * Moved minfication configuration settings to the `View` library level
* UI improvements:
    * Restyled installation wizard
    * Prepared styles for Dashboard in the Backend area
* Framework improvements:
    * Added `setCustomAttribute` and `setCustomAttributes` methods to `ExtensibleDataInterface`
    * Added setter methods to data object interfaces
    * Replaced `Builders` with `Factories`
    * Added `DataObjectHelper.php` which contains the common set of methods of all builders
    * Refactored `__()` to return `Phrase` object
    * Allowed usage of `LocalizedException` on the framework's library level
    * Added expiration/lifetime management of frontend resources
    * Unified MTF configurations format for Framework, TestCase variations and TestCase scenario configurations
* Fixed bugs:
    * Fixed an issue with product reviews list paging
    * Fixed an issue where sold products were not displayed in Bestsellers
    * Fixed an issue with image rendering on the CMS page on Frontend when `webserver rewrites = no`
* GitHub requests:
    * [#790](https://github.com/magento/magento2/issues/790) -- Magento API fails in a CGI env (zf1 issue)
    * [#909](https://github.com/magento/magento2/issues/909) -- Manage Titles in popup window front-end issue
    * [#996](https://github.com/magento/magento2/issues/996) -- Pager block should support url "fragment".
    * [#985](https://github.com/magento/magento2/pull/985) -- Allow camelcase in vendorname for menus
    * [#1025](https://github.com/magento/magento2/pull/1025) -- Wrong parameter for getting base url for 'media' path in "Image" form element.

0.42.0-beta7
=============
* Various improvements:
    * Added Varnish 4 support
    * Added CSS minification
    * Improved the performance toolkit
* Fixed bugs:
    * Fixed an issue where the compiler for the single tenant mode did not resolve Repositories
    * Fixed an issue where the "Select all" mass action on the Customers page did not select all customers
    * Fixed an issue where values for a customer  attribute of multiple-select type were not saved
    * Fixed an issue where the parental wakeup() method was not called in interceptors
    * Fixed an issue where bundle products with the same configurations added from different pages were displayed in the wishlist as separate items
    * Fixed an issue where the number of items added to the wishlist was not displayed on certain pages
    * Fixed an issue where logging was broken
    * Fixed an issue where it was impossible to use \Magento\Customer\Model\Resource\AddressRepository::getList with predefined direction(sortOrder)
    * Fixed an issue where editing a product from wishlist led caused a fatal error
    * Fixed an issue where the redirect link to continue shopping was absent in the success message after adding product to a wishlist
    * Fixed an issue where HTML tags where displayed in product prices on the Customer's Wishlist page in Admin
    * Fixed an issue where the Name and Email fields were not automatically when creating an email using the Email to Friend functionality
    * Fixed an issue with the redirect after searching product in a customer wishlist in Admin
    * Fixed an issue where a configurable product did not go out of stock when last subitem of some option was sold
    * Fixed an issue with varnish config generation for multiple IPs in access list field
    * Fixed the wrong di.xml in the Magento_Developer module
    * Fixed an issue where changes were not saved when default billing/shipping address was not selected in customer addresses
    * Fixed the issue where the Update Qty button looked disabled during a partial invoice creation
    * Fixed an issue where the creation date was not displayed in invoices and credit memo grids
    * Fixed an issue where it was impossible to install Magento_Quote on PHP 5.6
    * Fixed an issue that changes are not saved when default billing/shipping address is unchecked in customer addresses
    * Fixed an issue where "Update Qty" button looks disabled while creating partial invoice
    * Fixed an issue where date created column is not populated in invoices and credit memo grid
    * Fixed an issue with installation of Magento_Quote module on PHP 5.6
    * Fixed an issue with wrong link "File Permission Help"
    * Fixed an issue where dev/tools are broken when DI compiler is used due to skipped by the compiler dev/tools/Magento folder
* Framework improvements:
    * JavaScript testsuites divided into frontend, backend and lib suites
    * Implemented image compression on server side upload
    * Implemented frontend page resources sorting
    * Removed the Magic __call method usage in templates
    * Introduced Jasmine + PhantomJS JavaScript testing infrastructure
    * Removed support of PHP 5.4
* Setup Tool improvements:
    * Added tools for enabling/disabling modules: "module-enable --modules=Module_One,Module_Two, module-disable --modules=Module_One,Module_Two"
    * Added help option for displaying list of available modules: "help module-list"
* GitHub requests :
    * [#593](https://github.com/magento/magento2/issues/593) -- Allow to use "0" as customer group
    * [#804](https://github.com/magento/magento2/issues/804) -- Comment about VAT number displayed under different field in Customer Configuration

0.42.0-beta6
=============
* Various improvements:
    * Implemented caching for WebAPI configuration
    * Improved tests coverage of the CurrencySymbol module
    * Table catalogsearch_fulltext is setting up with ENGINE=InnoDB
    * Improved unit test coverage of the Catalog related functionality
    * Optimized JS dependencies
    * Refactored controller actions in the Sales module
    * Refactored controller actions in the Customer module
    * Removed the assertion for the exact number of attributes in API-functional tests for customer metadata.
    * Refactored API code for the CheckoutAgreements module
    * Refactored API code for the GiftMessage module
    * Refactored API for the Checkout module
* Fixed bugs:
    * Fixed an where issue were WebAPI generated the wrong WSDL
    * Fixed an issue where Catalog, Checkout, Customer API ACLs did not support AJAX use case(s)
    * Fixed an issue where SOAP tests failed after upgrading to ZF 1.12.9
    * Fixed an issue where the 'There is no data for export' message was displayed permanently after invalid search
    * Fixed an issue where there was no ability to set category position during creation it
    * Fixed a CSS issue where certain images were absent on banners ()
    * Fixed an issue where the 'Date Of Birth' value was i reset to current date on the customer form)
    * Fixed an issue where the behavior of the "Terms and Conditions" validation on multiple address checkout was different from the one for the onepage checkout
    * Fixed an issue where it was impossible to checkout with multiple addresses
    * Fixed an issue where the 'This is a required field ' message was not displayed for "Terms and Conditions" if the latter  was not selected
* GitHub Requests:
    * [#963](https://github.com/magento/magento2/pull/963) -- Default Accept header
    * [#995](https://github.com/magento/magento2/pull/995) -- Prevent a warning in activated developer mode when 'plugins' is no array
    * [#866](https://github.com/magento/magento2/issues/866) -- Configurable product attribute scope
    * [#965](https://github.com/magento/magento2/pull/965) -- extra tests for current interception behavior
* Service Contracts:
    * The Downloadable module basic implementation
* Framework improvements:
    * Refactored and covered with tests the classes with high CRAP value (>50)
    * Moved Theme Management changes, Design changes, Design\Backend modules, and Observer components from the Core module to the Theme module
    * Moved Debug Hints models from the Core module to the newly added Developer module
    * Moved URL components, Factory, and EntityFactory from the Core module to the Magento Framework
* UI improvements:
    * Compressed and resized images
    * Added new base styles for the Admin re-design
    * Added the WAI-ARIA attributes are to the Search Autocomplete on the storefront
    * Added visual style for the 'skip to content' attribute on the storefront
    * Fixed the style of persistent login messages on the storefront for all themes
    * Fixed the style of scrolling for Categories with long names in the Admin
    * Fixed the "css/print.css" file path on the storefront pages for all themes
* Tests improvements:
    * Converted all fixtures/repositories for functional tests to .xml files
    * Improved interaction between webdriver and the new Magento JS forms
    * Increased unit and integration tests coverage

0.42.0-beta5
=============
* UI improvements:
    * Updated the design of Enable Cookies CMS page
    * Implemented UI improvements for Widgets
    * Fixed the "Help Us to Keep Magento Healthy Report All Bugs (ver. #)" link Magento Admin
    * Various UI improvements
* Various improvements:
    * Implemented Sales Quote as a standalone Magento module
    * Performed custom EAV entities code and DB tables cleanup
    * Eliminating remnants of the Core module:
        * Moved Application Emulation from the Magento_Core module to the Magento_Store module
        * Moved Validator Factory from the Magento_Core module to the Magento Framework
    * Added static integrity test for composer.json files
    * Added PHPMD and PHPCS annotations to the codebase
* Tests improvements:
    * Added MVP tag to the functional tests
    * Created acceptance functional test suite
    * Replaced end-to-end tests for url rewrite creation, CMS page creation, category creation, review creation, customer frontend creation, and tax rule creation with injectable tests
    * Automated test cases for downloadable products with taxes
* Fixed bugs:
    * Fixed an issue where the Discounts and Coupons RSS Feed had incorrect title
    * Fixed an issue where a wrong special price expiration date was displayed in RSS
    * Fixed an issue in the Import functionality where imported files disappeared after the Check Data operation
    * Fixed an issue where the Unsubscribe link in the Newsletter was broken
    * Fixed an issue where stock status changed incorrectly after import
    * Fixed an issue where selected filters and exclude did not work during Export
    * Fixed an issue where tax details order was different on order/invoice/refund create and view pages (
    * Fixed a typo in the getCalculationAlgorithm public function
    * Fixed an issue where the incorrect value of Subtotal Including Tax was displayed in invoices
    * Fixed an issue where tax details were not displayed on a new order
    * Improved pricing performance using caching
    * Fixed an issue where CsvImportHandler tests still referring to links from Tax module instead of TaxImportExport module
    * Fixed an issue where an exception was thrown instead of 404 if altering the url for a product with required configuration on the storefront
    * Fixed an issue where the title of successfully placed order page (was empty
    * Fixed an issue where certain fields were not disabled by default on the website scope in System configuration as expected
    * Fixed an issue where third party interfaces were not supported by single-tenant compiler
    * Eliminated the 'protocol' parameter from the ReadInterface and WriteInterface
* GitHub requests:
    * [#979](https://github.com/magento/magento2/pull/979) -- Adding OSL license file name
    * [#978](https://github.com/magento/magento2/pull/978) -- Added ignore rule for media assets in wysiwyg directory
    * [#877](https://github.com/magento/magento2/pull/877) -- Made Topmenu HTML Editable
    * [#906](https://github.com/magento/magento2/pull/906) -- Add tests for View\Layout\Reader\Block and slight refactoring
    * [#682](https://github.com/magento/magento2/issues/682) -- \Magento\Framework\Pricing\PriceCurrencyInterface depends on Magento application code
    * [#581](https://github.com/magento/magento2/issues/581) -- About ByPercent.php under different currencies
    * [#964](https://github.com/magento/magento2/pull/964) -- Improving documentation for jMeter performance tests
    * [#871](https://github.com/magento/magento2/issues/871) -- Replace Symfony2/Yaml in composer
    * [#990](https://github.com/magento/magento2/pull/990) -- add @see annotation before class to make it recognizable by IDE
    * [#988](https://github.com/magento/magento2/pull/988) -- Prevent Varnish from creating cache variations of static files
* Framework improvements:
    * Improved unit and integration tests coverage

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
    * [#648](https://github.com/magento/magento2/issues/648) -- An equal (=) sign in the hash of the product page to break the tabs functionality
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
  * Fixed an issue with no results for the Product Bestsellers report
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
  * Fixed issues with regards to discount tax compensation
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
  * Fixed an issue where a fatal error occurred when opening a fixed bundle product with custom options page on the frontend
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
