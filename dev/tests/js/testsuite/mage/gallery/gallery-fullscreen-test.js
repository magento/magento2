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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
GalleryFullscreenTest = TestCase('GalleryFullscreenTest');
GalleryFullscreenTest.prototype.setUp = function() {
    /*:DOC +=
     <div data-role="media-gallery-test"></div>
     */
    this.galleryFullscreenElement = jQuery('[data-role=media-gallery-test]');
};
GalleryFullscreenTest.prototype.widgetName = 'galleryFullScreen';
GalleryFullscreenTest.prototype.tearDown = function() {
    this.galleryFullscreenDestroy();
};
GalleryFullscreenTest.prototype.galleryFullscreenDestroy = function() {
    var instance = this.galleryFullscreenElement.data(this.widgetName) ||
            this.galleryFullscreenElement.data(this.widgetName.toLowerCase());
    if(instance) {
        this.galleryFullscreenElement[this.widgetName]('destroy');
    }
};
GalleryFullscreenTest.prototype.galleryFullscreenCreate = function(options, element) {
    (element || this.galleryFullscreenElement)[this.widgetName](options || {} );
    return this.galleryFullscreenElement.data(this.widgetName) ||
        this.galleryFullscreenElement.data(this.widgetName.toLowerCase());
};

GalleryFullscreenTest.prototype.testInit = function() {
    this.galleryFullscreenElement[this.widgetName]();
    assertTrue(this.galleryFullscreenElement.is(':mage-galleryfullscreen'));
};

GalleryFullscreenTest.prototype.testCreate = function() {
    var galleryFullscreenInstance = this.galleryFullscreenCreate(),
        _bind = jsunit.stub(galleryFullscreenInstance, '_bind');
    galleryFullscreenInstance._create();

    assertTrue(_bind.callCount === 1);
};

GalleryFullscreenTest.prototype.testBind = function() {
    var galleryFullscreenInstance = this.galleryFullscreenCreate(),
        _fullScreen = jsunit.stub(galleryFullscreenInstance, '_fullScreen'),
        zoomImage = jQuery('<p data-role="zoom-image" />'),
        zoomTrack = jQuery('<p data-role="zoom-track" />');

    galleryFullscreenInstance.element.append(zoomImage).append(zoomTrack);
    zoomImage.trigger('click');
    assertTrue(_fullScreen.callCount === 1);
    zoomTrack.trigger('click');
    assertTrue(_fullScreen.callCount === 2);
};
