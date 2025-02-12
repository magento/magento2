/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'wysiwygAdapter',
    'underscore',
    'tinymce'
], function (wysiwygAdapter, _, tinyMCE) {
    'use strict';

    var obj;

    beforeEach(function () {

        /**
         * Dummy constructor to use for instantiation
         * @constructor
         */
        var Constr = function () {};

        Constr.prototype = wysiwygAdapter;

        obj = new Constr();
        obj.eventBus = new window.varienEvents();
        obj.initialize('id', {
            'store_id': 0,
            'tinymce': {
                'content_css': ''
            },
            'files_browser_window_url': 'url'
        });
        obj.setup();
    });

    describe('"openFileBrowser" method', function () {
        it('Opens file browser to given instance', function () {
            expect(_.size(obj.eventBus.arrEvents['open_browser_callback'])).toBe(1);
        });
    });

    describe('"triggerSave" method', function () {
        it('Check method call.', function () {
            spyOn(tinyMCE, 'triggerSave');
            obj.triggerSave();
            expect(tinyMCE.triggerSave).toHaveBeenCalled();
        });
    });
});
