/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
ZoomTest = TestCase('ZoomTest');
ZoomTest.prototype.setUp = function() {
    /*:DOC +=
     <script data-template="zoom-display" type="text/x-magento-template">
        <div data-role="zoom-container">
            <div data-role="zoom-inner"></div>
        </div>
     </script>
     <script data-template="zoom-enlarged-image" type="text/x-magento-template">
        <img data-role="enlarged-image" src="<%= data.img %>" />
     </script>
     <script data-template="zoom-track" type="text/x-magento-template">
        <div data-role="zoom-track"></div>
     </script>
     <script data-template="zoom-lens" type="text/x-magento-template">
        <div data-role="zoom-lens"></div>
     </script>
     <script data-template="notice" type="text/x-magento-template">
        <p class="notice" data-role="notice"><%= data.text %></p>
     </script>
     <div data-role="zoom-test">
        <img />
     </div>
     */
    this.zoomElement = jQuery('[data-role=zoom-test]');
};
ZoomTest.prototype.tearDown = function() {
    this.zoomDestroy();
};
ZoomTest.prototype.zoomDestroy = function() {
    if(this.zoomElement.data('zoom')) {
        this.zoomElement.zoom('destroy');
    }
};
ZoomTest.prototype.zoomCreate = function(options, element) {
    return (element || this.zoomElement).zoom(options || {} ).data('zoom');
};
ZoomTest.prototype.testInit = function() {
    this.zoomElement.zoom();
    assertTrue(this.zoomElement.is(':mage-zoom'));
};
ZoomTest.prototype.testCreate = function() {
    var zoomInstance = this.zoomCreate(),
        _setZoomData = jsunit.stub(zoomInstance, '_setZoomData'),
        _render = jsunit.stub(zoomInstance, '_render'),
        _bind = jsunit.stub(zoomInstance, '_bind'),
        _hide = jsunit.stub(zoomInstance, '_hide'),
        _largeImageLoaded = jsunit.stub(zoomInstance, '_largeImageLoaded');

    zoomInstance.largeImage = [{
        complete: false
    }];

    zoomInstance._create();
    assertTrue(_setZoomData.callCount === 1);
    assertTrue(_render.callCount === 1);
    assertTrue(_bind.callCount === 1);
    assertNull(_largeImageLoaded.callCount);
    assertTrue(_hide.callCount === 2);
    _setZoomData.reset();
    _render.reset();
    _bind.reset();
    _hide.reset();

    zoomInstance.largeImage[0].complete = true;
    zoomInstance._create();
    assertTrue(_setZoomData.callCount === 1);
    assertTrue(_render.callCount === 1);
    assertTrue(_bind.callCount === 1);
    assertTrue(_largeImageLoaded.callCount === 1);
    assertTrue(_hide.callCount === 2);
};
ZoomTest.prototype.testRender = function() {
    var zoomInstance = this.zoomCreate(),
        _renderControl = jsunit.stub(zoomInstance, '_renderControl'),
        _renderLargeImage = jsunit.stub(zoomInstance, '_renderLargeImage');
    _renderControl.returnCallback = function(control) {
        return jQuery('<p />', {'data-control': control});
    };

    zoomInstance._render();
    assertTrue(_renderControl.callCount === 4);
    assertTrue(zoomInstance.element.find('[data-control=track]').length > 0);
    assertTrue(zoomInstance.element.find('[data-control=lens]').length > 0);
    assertTrue(zoomInstance.element.find('[data-control=display]').length > 0);
    assertTrue(zoomInstance.element.find('[data-control=notice]').length > 0);
    assertTrue(_renderLargeImage.callCount === 1);
};
ZoomTest.prototype.testToggleNotice = function() {
    var zoomInstance = this.zoomCreate(),
        getZoomRatio = jsunit.stub(zoomInstance, 'getZoomRatio');

    zoomInstance.noticeOriginal = 'notice original';
    zoomInstance.options.controls.notice = {
        text: 'test text'
    };

    zoomInstance.notice.text('');
    zoomInstance.largeImageSrc = 'image.large.jpg';
    zoomInstance.activated = false;
    getZoomRatio.returnValue = 2;
    zoomInstance._toggleNotice();
    assertEquals(zoomInstance.notice.text(), zoomInstance.options.controls.notice.text);
    assertTrue(getZoomRatio.callCount === 1);

    zoomInstance.notice.text('');
    zoomInstance.largeImageSrc = null;
    zoomInstance.activated = false;
    getZoomRatio.returnValue = 2;
    zoomInstance._toggleNotice();
    assertEquals(zoomInstance.notice.text(), zoomInstance.noticeOriginal);

    zoomInstance.notice.text('');
    zoomInstance.largeImageSrc = 'image.large.jpg';
    zoomInstance.activated = true;
    getZoomRatio.returnValue = 2;
    zoomInstance._toggleNotice();
    assertEquals(zoomInstance.notice.text(), zoomInstance.noticeOriginal);

    zoomInstance.notice.text('');
    zoomInstance.largeImageSrc = 'image.large.jpg';
    zoomInstance.activated = false;
    getZoomRatio.returnValue = 0;
    zoomInstance._toggleNotice();
    assertEquals(zoomInstance.notice.text(), zoomInstance.noticeOriginal);
};

ZoomTest.prototype.testRefresh = function() {
    var zoomInstance = this.zoomCreate(),
        _refreshControl = jsunit.stub(zoomInstance, '_refreshControl');

    zoomInstance._refresh();
    assertTrue(_refreshControl.callCount === 3);
    assertTrue(_refreshControl.callArgsStack[0][0] === 'display');
    assertTrue(_refreshControl.callArgsStack[1][0] === 'track');
    assertTrue(_refreshControl.callArgsStack[2][0] === 'lens');
};

ZoomTest.prototype.testBind = function() {
    var zoomInstance = this.zoomCreate(),
        _on = jsunit.stub(zoomInstance, '_on'),
        events = {};

    zoomInstance.largeImage = jQuery('<p />');
    zoomInstance._bind();
    assertTrue(_on.callCount > 0);
    assertTrue(
        _on.callArgsStack[0][0][
            zoomInstance.options.startZoomEvent +
            ' ' +
            zoomInstance.options.selectors.image
        ] === 'show'
    );
    assertTrue(
        jQuery.type(_on.callArgsStack[0][0][
            zoomInstance.options.stopZoomEvent +
            ' ' +
            zoomInstance.options.selectors.track
        ]) === 'function'
    );
    assertTrue(_on.callArgsStack[0][0]['mousemove ' + zoomInstance.options.selectors.track] === '_move');
    assertTrue(_on.callArgsStack[0][0].imageupdated === '_onImageUpdated');
    assertTrue(_on.callArgsStack[1][0].is(zoomInstance.largeImage));
    assertTrue(_on.callArgsStack[1][1].load === '_largeImageLoaded');
};
ZoomTest.prototype.testEnable = function() {
    var zoomInstance = this.zoomCreate(),
        _onImageUpdated = jsunit.stub(zoomInstance, '_onImageUpdated');

    zoomInstance.enable();
    assertTrue(_onImageUpdated.callCount === 1);
};
ZoomTest.prototype.testDisable = function() {
    var zoomInstance = this.zoomCreate();

    zoomInstance.noticeOriginal = 'original notice';
    zoomInstance.notice.text('');
    zoomInstance.disable();
    assertEquals(zoomInstance.noticeOriginal, zoomInstance.notice.text());
};
ZoomTest.prototype.testShow = function() {
    var zoomInstance = this.zoomCreate(),
        e = {
            preventDefault: jsunit.stub(),
            stopImmediatePropagation: jsunit.stub()
        },
        getZoomRatio = jsunit.stub(zoomInstance, 'getZoomRatio'),
        _show = jsunit.stub(zoomInstance, '_show'),
        _refresh = jsunit.stub(zoomInstance, '_refresh'),
        _toggleNotice = jsunit.stub(zoomInstance, '_toggleNotice'),
        _trigger = jsunit.stub(zoomInstance, '_trigger');

    getZoomRatio.returnValue = 0;
    zoomInstance.show(e);
    assertTrue(e.preventDefault.callCount === 1);

    e.preventDefault.reset();
    getZoomRatio.reset();
    getZoomRatio.returnValue = 2;
    zoomInstance.largeImageSrc = 'image.large.jpg';
    zoomInstance.show(e);
    assertTrue(e.preventDefault.callCount === 1);
    assertTrue(e.stopImmediatePropagation.callCount === 1);
    assertTrue(zoomInstance.activated);
    assertTrue(_show.callCount > 0);
    assertTrue(_refresh.callCount === 1);
    assertTrue(_toggleNotice.callCount === 1);
    assertTrue(_trigger.callCount === 1);
    assertTrue(_trigger.lastCallArgs[0] === 'show');
};
ZoomTest.prototype.testHide = function() {
    var zoomInstance = this.zoomCreate(),
        _hide = jsunit.stub(zoomInstance, '_hide'),
        _toggleNotice = jsunit.stub(zoomInstance, '_toggleNotice'),
        _trigger = jsunit.stub(zoomInstance, '_trigger');

    zoomInstance.hide();
    assertTrue(_hide.callCount > 0);
    assertTrue(_toggleNotice.callCount === 1);
    assertTrue(_trigger.callCount === 1);
    assertTrue(_trigger.lastCallArgs[0] === 'hide');
};
ZoomTest.prototype.testOnImageUpdated = function() {
    var zoomInstance = this.zoomCreate(),
        _setZoomData = jsunit.stub(zoomInstance, '_setZoomData'),
        _refreshLargeImage = jsunit.stub(zoomInstance, '_refreshLargeImage'),
        _refresh = jsunit.stub(zoomInstance, '_refresh'),
        hide = jsunit.stub(zoomInstance, 'hide'),
        testImage = jQuery('<p data-role="test-image" />');

    zoomInstance.options.selectors.image = "[data-role=test-image]";
    zoomInstance.element.append(testImage);
    zoomInstance.image = testImage;
    zoomInstance._onImageUpdated();
    assertNull(_setZoomData.callCount);
    assertNull(_refreshLargeImage.callCount);
    assertNull(_refresh.callCount);
    assertNull(hide.callCount);

    zoomInstance.image = jQuery('<p />');
    zoomInstance.largeImageSrc = null;
    zoomInstance._onImageUpdated();
    assertTrue(_setZoomData.callCount === 1);
    assertNull(_refreshLargeImage.callCount);
    assertNull(_refresh.callCount);
    assertTrue(hide.callCount === 1);

    _setZoomData.reset();
    hide.reset();
    zoomInstance.largeImageSrc = 'image.large.jpg';
    zoomInstance._onImageUpdated();
    assertTrue(_setZoomData.callCount === 1);
    assertTrue(_refreshLargeImage.callCount === 1);
    assertTrue(_refresh.callCount === 1);
    assertNull(hide.callCount);
};
ZoomTest.prototype.testLargeImageLoaded = function() {
    var zoomInstance = this.zoomCreate(),
        _toggleNotice = jsunit.stub(zoomInstance, '_toggleNotice'),
        _getAspectRatio = jsunit.stub(zoomInstance, '_getAspectRatio'),
        _getWhiteBordersOffset = jsunit.stub(zoomInstance, '_getWhiteBordersOffset'),
        processStopTriggered = false,
        image = jQuery('<p data-role="test-image" />');

    _getWhiteBordersOffset.returnValue = 1;
    zoomInstance.element.append(image);
    zoomInstance.options.selectors.image = '[data-role=test-image]';
    zoomInstance.image = image;
    _getAspectRatio.returnCallback = function(image) {
        if (image.is(zoomInstance.image)) {
            return 0;
        } else {
            return 1;
        }
    };

    jQuery(zoomInstance.options.selectors.image).on('processStop', function() {
        processStopTriggered = true;
    });
    zoomInstance.ratio = 1;

    zoomInstance._largeImageLoaded();
    assertNull(zoomInstance.ratio);
    assertTrue(_toggleNotice.callCount === 1);
    assertTrue(processStopTriggered);
    assertTrue(_getAspectRatio.callCount > 0);
    assertTrue(_getWhiteBordersOffset.callCount === 1);
    assertEquals(zoomInstance.whiteBordersOffset, _getWhiteBordersOffset.returnValue);
};
ZoomTest.prototype.testRefreshLargeImage = function() {
    var zoomInstance = this.zoomCreate(),
        css = {top: 0, left: 0};
    zoomInstance.largeImage = jQuery('<img />');
    zoomInstance.largeImageSrc = 'large.image.jpg';

    zoomInstance._refreshLargeImage();
    assertNotUndefined(zoomInstance.largeImage.prop('src'));
    assertEquals(zoomInstance.largeImage.css('top'), css.top + 'px');
    assertEquals(zoomInstance.largeImage.css('left'), css.left + 'px');
};
ZoomTest.prototype.testRenderLargeImage = function() {
    var zoomInstance = this.zoomCreate();

    zoomInstance.element.append(jQuery('<p data-role="test-image" />'));
    zoomInstance.options.selectors.image = '[data-role=test-image]';

    var image = zoomInstance._renderLargeImage();
    assertTrue(image.is('img'));
    assertTrue(image.is(zoomInstance.largeImage));
};
ZoomTest.prototype.testGetZoomRatio = function() {
    var zoomInstance = this.zoomCreate(),
        imageSize = {width: 100, height: 100},
        largeImageSize = {width: 200, height: 200};

    zoomInstance.ratio = null;
    zoomInstance.image = jQuery('<img />', imageSize);
    zoomInstance.largeImageSize = largeImageSize;
    var zoomRatio = zoomInstance.getZoomRatio();

    assertEquals(zoomRatio, (largeImageSize.width / imageSize.width));
    zoomInstance.ratio = 100;
    zoomRatio = zoomInstance.getZoomRatio();
    assertEquals(zoomRatio, zoomInstance.ratio);
};
ZoomTest.prototype.testGetAspectRatio = function() {
    var zoomInstance = this.zoomCreate(),
        aspectRatio = zoomInstance._getAspectRatio(),
        size = {width: 200, height: 100};
    assertNull(aspectRatio);
    aspectRatio = zoomInstance._getAspectRatio(jQuery('<div />', size));
    assertEquals((Math.round((size.width / size.height) * 100) / 100), aspectRatio);
};
