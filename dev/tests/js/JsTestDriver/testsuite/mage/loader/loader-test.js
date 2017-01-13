/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
LoaderTest = TestCase('LoaderTest');
LoaderTest.prototype.setUp = function() {
    /*:DOC += <div id="loader"></div> */
};
LoaderTest.prototype.tearDown = function() {
    var loaderInstance = jQuery('#loader').data('loader');
    if(loaderInstance && loaderInstance.destroy) {
        loaderInstance.destroy();
    }
};
LoaderTest.prototype.getInstance = function() {
    return jQuery('#loader').data('loader');
};
LoaderTest.prototype.testInit = function() {
    var div = jQuery('#loader').loader();
    div.loader('show');
    assertEquals(true, div.is(':mage-loader'));
};
// @TODO Need to be fixed to avoid errors on the bamboo server in context of MAGETWO-5085 ticket
/*LoaderTest.prototype._testCreateOnBeforeSend = function() {
    /*:DOC += <div id="loader"></div> */
/*  var loader = jQuery('#loader').trigger('ajaxSend');
    assertEquals(true, loader.is(':mage-loader'));
    loader.loader('destroy');
};*/
LoaderTest.prototype.testLoaderOnBody = function() {
    var body = jQuery('body').loader();
    body.loader('show');
    assertEquals(true, jQuery('body div:first').is('.loading-mask'));
    body.loader('destroy');
};
LoaderTest.prototype.testLoaderOnDOMElement = function() {
    var div = jQuery('#loader').loader(),
        loaderInstance = this.getInstance();
    div.loader('show');
    assertEquals(true, div.find(':first-child').is(loaderInstance.spinner));
};
LoaderTest.prototype.testLoaderOptions = function() {
    /*:DOC += <div id="loader"></div> */
    var div = jQuery('#loader').loader({
            icon: 'icon.gif',
            texts: {
                loaderText: 'Loader Text',
                imgAlt: 'Image Alt Text'
            }
        }),
        loaderInstance = this.getInstance();
    div.loader('show');
    assertEquals('icon.gif', loaderInstance.spinner.find('img').attr('src'));
    assertEquals('Image Alt Text', loaderInstance.spinner.find('img').attr('alt'));
    assertEquals('Loader Text', loaderInstance.spinner.find('div.popup-inner').text());
    div.loader('destroy');
    div.loader({
        template:'<div id="test-template"></div>'
    });
    div.loader('show');
    loaderInstance = this.getInstance();
    assertEquals(true, loaderInstance.spinner.is('#test-template'));
    div.loader('destroy');
};
LoaderTest.prototype.testHideOnComplete = function() {
    /*:DOC += <div id="loader"></div> */
    var div = jQuery('#loader').loader();
    div.loader('show');
    loaderIsVisible = jQuery('.loading-mask').is(':visible');
    div.trigger('processStop');
    assertEquals(false, jQuery('.loading-mask').is(':visible') === loaderIsVisible);
};
LoaderTest.prototype.testRender = function() {
    /*:DOC += <div id="loader" style="widht:200px; height:200px;"></div> */
    var div = jQuery('#loader').loader();
    div.loader('show');
    assertEquals(true, $('.loading-mask').is(':visible'));
};
LoaderTest.prototype.testShowHide = function() {
    /*:DOC += <div id="loader" style="widht:200px; height:200px;"></div> */
    var div = jQuery('#loader').loader();
    div.loader('show');
    assertEquals(true, $('.loading-mask').is(':visible'));
    div.loader('hide');
    assertEquals(false, $('.loading-mask').is(':visible'));
};
LoaderTest.prototype.testDestroy = function() {
    /*:DOC += <div id="loader"></div> */
    var div = jQuery('#loader').loader(),
        loaderExist = div.is(':mage-loader');
    div.loader('destroy');
    assertEquals(false, div.is(':mage-loader') === loaderExist);
};
