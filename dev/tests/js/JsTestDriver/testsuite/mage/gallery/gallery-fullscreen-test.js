/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
