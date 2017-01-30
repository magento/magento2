/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    './column',
    'jquery',
    'mage/template',
    'text!Magento_Ui/templates/grid/cells/thumbnail/preview.html',
    'Magento_Ui/js/modal/modal'
], function (Column, $, mageTemplate, thumbnailPreviewTemplate) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/thumbnail',
            fieldClass: {
                'data-grid-thumbnail-cell': true
            }
        },
        getSrc: function (row) {
            return row[this.index + '_src']
        },
        getOrigSrc: function (row) {
            return row[this.index + '_orig_src'];
        },
        getLink: function (row) {
            return row[this.index + '_link'];
        },
        getAlt: function (row) {
            return row[this.index + '_alt']
        },
        isPreviewAvailable: function() {
            return this.has_preview || false;
        },
        preview: function (row) {
            var modalHtml = mageTemplate(
                thumbnailPreviewTemplate,
                {
                    src: this.getOrigSrc(row), alt: this.getAlt(row), link: this.getLink(row),
                    linkText: $.mage.__('Go to Details Page')
                }
            );
            var previewPopup = $('<div/>').html(modalHtml);
            previewPopup.modal({
                title: this.getAlt(row),
                innerScroll: true,
                modalClass: '_image-box',
                buttons: []}).trigger('openModal');
        },
        getFieldHandler: function (row) {
            if (this.isPreviewAvailable()) {
                return this.preview.bind(this, row);
            }
        }
    });
});
