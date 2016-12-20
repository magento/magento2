/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * Simple Navigation with replacing old handlers.
 *
 * @param {String} id - id of ul element with navigation lists
 * @param {Object} settings - object with settings
 */
/* global mainNav */
define([
    'prototype'
], function () {
    'use strict';

    /**
     * Main nav.
     */
    window.mainNav = function () {
        var main = {
            'obj_nav':   $(arguments[0]) || $('nav'),
            settings:  {
                'show_delay': 0,
                'hide_delay': 0,
                _ie6: /MSIE 6.+Win/.test(navigator.userAgent),
                _ie7: /MSIE 7.+Win/.test(navigator.userAgent)
            },

            /**
             * @param {Object} obj
             * @param {*} level
             */
            init: function (obj, level) {
                obj.lists = obj.childElements();
                obj.lists.each(function (el, ind) {
                    main.handlNavElement(el);

                    if ((main.settings._ie6 || main.settings._ie7) && level) {
                        main.ieFixZIndex(el, ind, obj.lists.size());
                    }
                });

                if (main.settings._ie6 && !level) {
                    document.execCommand('BackgroundImageCache', false, true);
                }
            },

            /**
             * @param {Object} list
             */
            handlNavElement: function (list) {
                if (list !== undefined) {

                    /**
                     * On mouse over.
                     */
                    list.onmouseover = function () {
                        main.fireNavEvent(this, true);
                    };

                    /**
                     * On mouse out.
                     */
                    list.onmouseout = function () {
                        main.fireNavEvent(this, false);
                    };

                    if (list.down('ul')) {
                        main.init(list.down('ul'), true);
                    }
                }
            },

            /**
             * @param {HTMLElement} el
             * @param {*} i
             * @param {*} l
             */
            ieFixZIndex: function (el, i, l) {
                if (el.tagName.toString().toLowerCase().indexOf('iframe') == -1) { //eslint-disable-line eqeqeq
                    el.style.zIndex = l - i;
                } else {
                    el.onmouseover = 'null';
                    el.onmouseout = 'null';
                }
            },

            /**
             * @param {Onject} elm
             * @param {*} ev
             */
            fireNavEvent: function (elm, ev) {
                if (ev) {
                    elm.addClassName('over');
                    elm.down('a').addClassName('over');

                    if (elm.childElements()[1]) {
                        main.show(elm.childElements()[1]);
                    }
                } else {
                    elm.removeClassName('over');
                    elm.down('a').removeClassName('over');

                    if (elm.childElements()[1]) {
                        main.hide(elm.childElements()[1]);
                    }
                }
            },

            /**
             * @param {Object} subElm
             */
            show: function (subElm) {
                if (subElm['hide_time_id']) {
                    clearTimeout(subElm['hide_time_id']);
                }
                subElm['show_time_id'] = setTimeout(function () {
                    if (!subElm.hasClassName('shown-sub')) {
                        subElm.addClassName('shown-sub');
                    }
                }, main.settings['show_delay']);
            },

            /**
             * @param {Object} subElm
             */
            hide: function (subElm) {
                if (subElm['show_time_id']) {
                    clearTimeout(subElm['show_time_id']);
                }
                subElm['hide_time_id'] = setTimeout(function () {
                    if (subElm.hasClassName('shown-sub')) {
                        subElm.removeClassName('shown-sub');
                    }
                }, main.settings['hide_delay']);
            }

        };

        if (arguments[1]) {
            main.settings = Object.extend(main.settings, arguments[1]);
        }

        if (main['obj_nav']) {
            main.init(main['obj_nav'], false);
        }
    };

    document.observe('dom:loaded', function () {
        //run navigation without delays and with default id="#nav"
        //mainNav();

        //run navigation with delays
        mainNav('nav', {
            'show_delay': '100',
            'hide_delay': '100'
        });
    });
});
