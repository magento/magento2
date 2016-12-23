/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global formId */
/* eslint-disable max-depth, no-lonely-if, strict */
var Captcha = Class.create();

Captcha.prototype = {
    /**
     * @param {*} url
     * @param {*} formId
     */
    initialize: function (url, formId) {
        this.url = url;
        this.formId = formId;
    },

    /**
     * @param {*} elem
     */
    refresh: function (elem) {
        window.formId = this.formId;

        if (elem) {
            Element.addClassName(elem, 'refreshing');
        }
        new Ajax.Request(this.url, {
            /**
             * @param {Object} response
             */
            onSuccess: function (response) {
                var json;

                if (response.responseText.isJSON()) {
                    json = response.responseText.evalJSON();

                    if (!json.error && json.imgSrc) {
                        $(formId).writeAttribute('src', json.imgSrc);

                        if (elem) {
                            Element.removeClassName(elem, 'refreshing');
                        }
                    } else {
                        if (elem) {
                            Element.removeClassName(elem, 'refreshing');
                        }
                    }
                }
            },
            method: 'post',
            parameters: {
                'formId': this.formId
            }
        });
    }
};
