/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
GalleryTest = TestCase('GalleryTest');
GalleryTest.prototype.setUp = function() {
    /*:DOC +=
     <script data-template="gallery-wrapper" type="text/x-jQuery-tmpl">
        <div data-role="gallery-base-image-container"></div>
        <div data-role="gallery-notice-container"></div>
        <div data-role="gallery-thumbs-container"><div>
        <div data-role="gallery-buttons-container"></div>
     </script>
     <script data-template="gallery-buttons" type="text/x-jQuery-tmpl">
        <a data-role="gallery-prev" href="#"></a>
        <a data-role="gallery-next" href="#"></a>
     </script>
     <script data-template="gallery-base-image" type="text/x-jQuery-tmpl">
        <img data-role="zoom-image" {{if !fullSizeMode}}data-large="${large}"
            src="${medium}"{{else}}src="${large}"{{/if}} alt="${title}"/>
     </script>
     <script data-template="gallery-thumbs" type="text/x-jQuery-tmpl">
         <div>
         {{each(index, img) images}}
            <a title="${img.title}" data-index="${index}" data-role="gallery-thumb" href="#">
                <img alt="${img.title}" src="${img.small}" itemprop="image">
            </a>
         {{/each}}
         <div>
     </script>
     <script data-template="notice" type="text/x-jQuery-tmpl">
        <p class="notice" data-role="notice">${text}</p>
     </script>
     <div data-role="media-gallery-test"></div>
    */
    this.galleryElement = jQuery('[data-role=media-gallery-test]');
};
GalleryTest.prototype.tearDown = function() {
    this.galleryDestroy();
};
GalleryTest.prototype.galleryDestroy = function() {
    if(this.galleryElement.data('gallery')) {
        this.galleryElement.gallery('destroy');
    }
};
GalleryTest.prototype.galleryCreate = function(options, element) {
    return (element || this.galleryElement).gallery(options || {} ).data('gallery');
};

GalleryTest.prototype.galleryHTML =
    '<div data-role="media-gallery">' +
        '<div class="product photo main">' +
            '<div data-role="gallery-base-image-container">' +
                '<a href="#" data-role="zoom-image" data-large="image1.large.jpg">' +
                    '<img data-role="zoom-image" src="image1.medium.js" />' +
                '</a>' +
            '</div>' +
            '<div data-role="gallery-notice-container">' +
                '<p data-role="notice"></p>' +
            '</div>' +
        '</div>' +
        '<div class="product photo thumbs">' +
            '<ul>' +
                '<li>' +
                    '<a href="#" data-role="gallery-thumb"' +
                    ' data-image-small="image1.small.jpg"' +
                    ' data-image-medium="image1.medium.jpg"' +
                    ' data-image-large="image1.large.jpg"' +
                    ' data-image-selected="true">' +
                        '<img src="image1.small.jpg" alt=""/>' +
                    '</a>' +
                '</li>' +
                '<li>' +
                    '<a href="#" data-role="gallery-thumb"' +
                    ' data-image-small="image2.small.jpg"' +
                    ' data-image-medium="image2.medium.jpg"' +
                    ' data-image-large="image2.large.jpg">' +
                        '<img src="image2.small.jpg" alt=""/>' +
                    '</a>' +
                '</li>' +
            '</ul>' +
        '</div>' +
    '</div>';

GalleryTest.prototype.galleryOptions = {
    controls: {
        notice: {
            text: 'Test Notice'
        }
    }
};

GalleryTest.prototype.galleryImages = [
    {
        small: 'image1.small.jpg',
        medium: 'image1.medium.jpg',
        large: 'image1.large.jpg',
        selected: true
    },
    {
        small: 'image2.small.jpg',
        medium: 'image2.medium.jpg',
        large: 'image2.large.jpg'
    }
];

GalleryTest.prototype.resetImageSelection = function(images) {
    return jQuery.grep(images, function(image) {
        if (image.selected) {
            delete image.selected;
        }
        return true;
    });
};

GalleryTest.prototype.testInit = function() {
    this.galleryElement.gallery();
    assertTrue(this.galleryElement.is(':mage-gallery'));
};

GalleryTest.prototype.testSerializeImages = function() {
    this.galleryElement.replaceWith(this.galleryHTML);
    var galleryInstance = this.galleryCreate(),
        images = galleryInstance._serializeImages();

    assertEquals(images[0], this.galleryImages[0]);
    assertEquals(images[1], this.galleryImages[1]);

    jQuery('[data-role=gallery-thumb]').data('image-small', null);
    images = galleryInstance._serializeImages();
    assertTrue(images.length === 0);

    this.galleryDestroy();
    this.galleryElement.replaceWith(this.galleryHTML);
    galleryInstance = this.galleryCreate();
    jQuery('[data-role=gallery-thumb]').data('image-medium', null);
    images = galleryInstance._serializeImages();
    assertTrue(images.length === 0);

    this.galleryDestroy();
    this.galleryElement.replaceWith(this.galleryHTML);
    galleryInstance = this.galleryCreate();
    jQuery('[data-role=gallery-thumb]').data('image-large', null);
    images = galleryInstance._serializeImages();
    assertTrue(images.length === 0);
};

GalleryTest.prototype.testBind = function() {
    var galleryInstance = this.galleryCreate(),
        options = galleryInstance.options,
        _on = jsunit.stub(galleryInstance, '_on');

    galleryInstance._bind();
    assertTrue(_on.callCount > 0);
    assertTrue(_on.callArgsStack[0][0]['click ' + options.selectors.thumb] === 'select');
    assertTrue(_on.callArgsStack[0][0]['click ' + options.selectors.prev] === 'prev');
    assertTrue(_on.callArgsStack[0][0]['click ' + options.selectors.next] === 'next');
};

GalleryTest.prototype.testToggleControl = function() {
    var galleryInstance = this.galleryCreate(),
        controlName = 'testControl',
        _initControl = jsunit.stub(galleryInstance, '_initControl');

    galleryInstance[controlName] = null;
    galleryInstance._toggleControl(controlName, true);
    assertTrue(_initControl.callCount === 1);

    // Append to body needed for that the method ".is(':visible')" worked correctly
    galleryInstance[controlName] = jQuery('<p />').hide().appendTo('body');
    assertFalse(galleryInstance[controlName].is(':visible'));
    galleryInstance._toggleControl(controlName, true);
    assertTrue(galleryInstance[controlName].is(':visible'));

    galleryInstance[controlName] = jQuery('<p />').show().appendTo('body');
    assertTrue(galleryInstance[controlName].is(':visible'));
    galleryInstance._toggleControl(controlName, false);
    assertFalse(galleryInstance[controlName].is(':visible'));
};

GalleryTest.prototype.testSetOption = function() {
    var galleryInstance = this.galleryCreate(),
        _toggleControl = jsunit.stub(galleryInstance, '_toggleControl'),
        _initControl = jsunit.stub(galleryInstance, '_initControl'),
        _render = jsunit.stub(galleryInstance, '_render');

    galleryInstance.options.showThumbs = false;
    galleryInstance._setOption('showThumbs', true);
    assertEquals(_toggleControl.lastCallArgs[0], 'thumbs');
    assertTrue(_toggleControl.lastCallArgs[1]);

    galleryInstance.options.showButtons = false;
    galleryInstance._setOption('showButtons', true);
    assertEquals(_toggleControl.lastCallArgs[0], 'slideButtons');
    assertTrue(_toggleControl.lastCallArgs[1]);

    galleryInstance.options.showNotice = false;
    galleryInstance._setOption('showNotice', true);
    assertEquals(_toggleControl.lastCallArgs[0], 'notice');
    assertTrue(_toggleControl.lastCallArgs[1]);

    galleryInstance.options.fullSizeMode = false;
    galleryInstance._setOption('fullSizeMode', true);
    assertEquals(_initControl.lastCallArgs[0], 'baseImage');

    galleryInstance._setOption('images', []);
    assertTrue(_render.callCount === 1);
};

GalleryTest.prototype.testSelect = function() {
    var galleryInstance = this.galleryCreate(),
        e = {currentTarget: jQuery('<p />').data('index', 1)},
        updateTriggered = false,
        _select = jsunit.stub(galleryInstance, '_select');

    galleryInstance.baseImage = jQuery('<p />');
    galleryInstance._on(galleryInstance.baseImage, {
        imageupdated: function() {
            updateTriggered = true;
        }
    });

    galleryInstance.selected = 0;
    galleryInstance.select(e);
    assertTrue(updateTriggered);
    assertTrue(_select.callCount === 1);
    assertEquals(_select.lastCallArgs[0], e.currentTarget.data('index'));

    updateTriggered = false;
    galleryInstance.selected = e.currentTarget.data('index');
    galleryInstance.select(e);
    assertFalse(updateTriggered);
};

GalleryTest.prototype.test_select = function() {
    var galleryInstance = this.galleryCreate({images: this.galleryImages}),
        options = galleryInstance.options,
        selectIndex = 1,
        _setSelected = jsunit.stub(galleryInstance, '_setSelected'),
        _initControl = jsunit.stub(galleryInstance, '_initControl');

    var thumb = galleryInstance.thumbs
        .find(options.selectors.thumb)
        .eq(selectIndex);
    assertFalse(thumb.hasClass(options.activeClass));
    galleryInstance._select(selectIndex);
    assertTrue(_setSelected.callCount === 1);
    assertTrue(_initControl.callCount === 1);
    assertEquals(_setSelected.lastCallArgs[0], selectIndex);
    assertEquals(_initControl.lastCallArgs[0], 'baseImage');
    assertTrue(thumb.hasClass(options.activeClass));
};

GalleryTest.prototype.testResolveIndex = function() {
    var galleryInstance = this.galleryCreate({images: this.galleryImages}),
        imagesLength = this.galleryImages.length,
        resolvedIndex;

    resolvedIndex = galleryInstance._resolveIndex(imagesLength);
    assertTrue(resolvedIndex === 0);

    resolvedIndex = galleryInstance._resolveIndex(imagesLength + 1);
    assertTrue(resolvedIndex === 0);

    resolvedIndex = galleryInstance._resolveIndex(-1);
    assertTrue(resolvedIndex === imagesLength - 1);

    resolvedIndex = galleryInstance._resolveIndex(0);
    assertTrue(resolvedIndex === 0);
};

GalleryTest.prototype.testPrev = function() {
    var galleryInstance = this.galleryCreate({images: this.galleryImages}),
        _select = jsunit.stub(galleryInstance, '_select'),
        _resolveIndex = jsunit.stub(galleryInstance, '_resolveIndex'),
        _getSelected = jsunit.stub(galleryInstance, '_getSelected');
    _getSelected.returnValue = 2;
    _resolveIndex.returnValue = 1;
    galleryInstance.selected = 1;

    galleryInstance.prev();
    assertTrue(_getSelected.callCount === 1);
    assertTrue(_resolveIndex.callCount === 1);
    assertTrue(_resolveIndex.lastCallArgs[0] === _getSelected.returnValue - galleryInstance.selected);
    assertTrue(_select.callCount === 1);
    assertTrue(_select.lastCallArgs[0] === _resolveIndex.returnValue);
};

GalleryTest.prototype.testNext = function() {
    var galleryInstance = this.galleryCreate({images: this.galleryImages}),
        _select = jsunit.stub(galleryInstance, '_select'),
        _resolveIndex = jsunit.stub(galleryInstance, '_resolveIndex'),
        _getSelected = jsunit.stub(galleryInstance, '_getSelected');
    _getSelected.returnValue = 1;
    _resolveIndex.returnValue = 2;
    galleryInstance.selected = 1;

    galleryInstance.next();
    assertTrue(_getSelected.callCount === 1);
    assertTrue(_resolveIndex.callCount === 1);
    assertTrue(_resolveIndex.lastCallArgs[0] === _getSelected.returnValue + galleryInstance.selected);
    assertTrue(_select.callCount === 1);
    assertTrue(_select.lastCallArgs[0] === _resolveIndex.returnValue);
};

GalleryTest.prototype.testRender = function() {
    var galleryInstance = this.galleryCreate({showNotice: false, showThumbs: false, showButtons: false}),
        _initControl = jsunit.stub(galleryInstance, '_initControl'),
        _renderWrapper = jsunit.stub(galleryInstance, '_renderWrapper');

    galleryInstance._render();
    assertTrue(_initControl.callCount === 1);
    assertTrue(_initControl.lastCallArgs.length > 0);
    assertTrue(_initControl.lastCallArgs[0] === 'baseImage');

    _initControl.reset();
    galleryInstance.options.showNotice = true;
    galleryInstance._render();
    assertTrue(_initControl.callCount === 2);
    assertTrue(_initControl.callArgsStack.length === 2);
    assertTrue(_initControl.callArgsStack[0][0] === 'notice');
    assertTrue(_initControl.callArgsStack[1][0] === 'baseImage');

    _initControl.reset();
    galleryInstance.options.showThumbs = true;
    galleryInstance._render();
    assertTrue(_initControl.callCount === 3);
    assertTrue(_initControl.callArgsStack.length === 3);
    assertTrue(_initControl.callArgsStack[0][0] === 'notice');
    assertTrue(_initControl.callArgsStack[1][0] === 'baseImage');
    assertTrue(_initControl.callArgsStack[2][0] === 'thumbs');

    _initControl.reset();
    galleryInstance.options.showButtons = true;
    galleryInstance._render();
    assertTrue(_initControl.callCount === 4);
    assertTrue(_initControl.callArgsStack.length === 4);
    assertTrue(_initControl.callArgsStack[0][0] === 'notice');
    assertTrue(_initControl.callArgsStack[1][0] === 'baseImage');
    assertTrue(_initControl.callArgsStack[2][0] === 'thumbs');
    assertTrue(_initControl.callArgsStack[3][0] === 'slideButtons');
};
GalleryTest.prototype.testSetSelected = function() {
    var galleryInstance = this.galleryCreate(),
        selected = 1;

    galleryInstance._setSelected(selected);
    assertTrue(galleryInstance.selected === selected);
};
GalleryTest.prototype.testGetSelected = function() {
    var galleryInstance = this.galleryCreate(),
        _findSelected = jsunit.stub(galleryInstance, '_findSelected');

    galleryInstance.selected = null;
    galleryInstance._getSelected();
    assertTrue(_findSelected.callCount === 1);

    _findSelected.reset();
    galleryInstance.selected = 1;
    var selected = galleryInstance._getSelected();
    assertNull(_findSelected.callCount);
    assertEquals(selected, galleryInstance.selected);
};
GalleryTest.prototype.testFindSelected = function() {
    var galleryInstance = this.galleryCreate({images: this.galleryImages});

    galleryInstance.options.images = this.resetImageSelection(galleryInstance.options.images);
    galleryInstance.options.images[0].selected = true;
    var selected = galleryInstance._findSelected();
    assertTrue(selected === 0);

    galleryInstance.options.images = this.resetImageSelection(galleryInstance.options.images);
    galleryInstance.options.images[1].selected = true;
    selected = galleryInstance._findSelected();
    assertTrue(selected === 1);

    galleryInstance.options.images = this.resetImageSelection(galleryInstance.options.images);
    galleryInstance.options.images[0].selected = true;
    galleryInstance.options.images[1].selected = true;
    selected = galleryInstance._findSelected();
    assertTrue(selected === 0);
};
GalleryTest.prototype.testInitControl = function() {
    var galleryInstance = this.galleryCreate(),
        _renderControl = jsunit.stub(galleryInstance, '_renderControl'),
        controlContainer = jQuery('<p data-role="test-control-container" />'),
        controlName = 'test',
        renderedControl = jQuery('<p data-role="test-control" />');

    galleryInstance.element.append(controlContainer);
    galleryInstance.options.controls[controlName] = {container: '[data-role=test-control-container]'};
    _renderControl.returnValue = renderedControl;
    galleryInstance._initControl('test');
    assertTrue(_renderControl.callCount === 1);
    assertTrue(_renderControl.lastCallArgs[0] === 'test');
    assertTrue(galleryInstance.element.find('[data-role=test-control]').length > 0);
    assertTrue(galleryInstance.element.find('[data-role=test-control]').is(renderedControl));
};
