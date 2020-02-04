/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'wysiwygAdapter',
    'underscore'
], function (wysiwygAdapter, _) {
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
        obj.initialize(1, {
            'store_id': 0,
            'tinymce4': {
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
});
