/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    mage.js
 * @package     test
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
LoaderTest = TestCase('LoaderTest');
LoaderTest.prototype.testInit = function() {
    /*:DOC += <div id="loader"></div> */
    var loader = jQuery('#loader').loader();
    assertEquals(true, loader.is(':mage-loader'));
    loader.loader('destroy');
};
LoaderTest.prototype.testCreateOnBeforeSend = function() {
    /*:DOC += <div id="loader"></div> */
    var loader = jQuery('#loader').trigger('ajaxSend');
    assertEquals(true, loader.is(':mage-loader'));
    loader.loader('destroy');
};
LoaderTest.prototype.testLoaderOnBody = function() {
    jQuery('body').loader();
    assertEquals(true, jQuery('body div:first').is('.loading-mask'));
    jQuery('body').loader('destroy');
};
LoaderTest.prototype.testLoaderOnDOMElement = function() {
    /*:DOC += <div id="loader"></div> */
    var loader = jQuery('#loader').loader();
    assertEquals(true, loader.prev().is('.loading-mask'));
    loader.loader('destroy');
};
LoaderTest.prototype.testLoaderOptions = function() {
    /*:DOC += <div id="loader"></div> */
    var loader = jQuery('#loader').loader({
        icon: 'icon.gif',
        texts: {
            loaderText: 'Loader Text',
            imgAlt: 'Image Alt Text'
        }
    });
    assertEquals('icon.gif', loader.prev().find('img').attr('src'));
    assertEquals('Image Alt Text', loader.prev().find('img').attr('alt'));
    assertEquals('Loader Text', loader.prev().find('span').text());
    loader.loader('destroy');
    loader.loader({
        template:'<div id="test-template"></div>'
    });
    assertEquals(true, loader.prev().is('#test-template'));
};
LoaderTest.prototype.testHideOnComplete = function() {
    /*:DOC += <div id="loader"></div> */
    var loader = jQuery('#loader').loader(),
        loaderIsVisible = jQuery('.loading-mask').is(':visible');
    loader.trigger('ajaxComplete');
    assertEquals(false, jQuery('.loading-mask').is(':visible') === loaderIsVisible);
    loader.loader('destroy');
};
LoaderTest.prototype.testRender = function() {
    /*:DOC += <div id="loader" style="widht:200px; height:200px;"></div> */
    var loader = jQuery('#loader').loader();
    assertEquals(true, $('.loading-mask').is(':visible'));
    loader.loader('destroy');
};
LoaderTest.prototype.testShowHide = function() {
    /*:DOC += <div id="loader" style="widht:200px; height:200px;"></div> */
    var loader = jQuery('#loader').loader();
    loader.loader('show');
    assertEquals(true, $('.loading-mask').is(':visible'));
    loader.loader('hide');
    assertEquals(false, $('.loading-mask').is(':visible'));
    loader.loader('destroy');
};
LoaderTest.prototype.testDestroy = function() {
    /*:DOC += <div id="loader"></div> */
    var loader = jQuery('#loader').loader(),
        loaderExist = loader.is(':mage-loader');
    loader.loader('destroy');
    assertEquals(false, loader.is(':mage-loader') === loaderExist);
    loader.loader('destroy');
};
