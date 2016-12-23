/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global varienAccordion, varienLoader, Cookie */
define([
    'prototype'
], function () {
    'use strict';

    window.varienAccordion = new Class.create(); //eslint-disable-line
    varienAccordion.prototype = {
        /**
         * @param {*} containerId
         * @param {*} activeOnlyOne
         */
        initialize: function (containerId, activeOnlyOne) {
            var links, i;

            this.containerId = containerId;
            this.activeOnlyOne = activeOnlyOne || false;
            this.container   = $(this.containerId);
            this.items       = $$('#' + this.containerId + ' dt');
            this.loader      = new varienLoader(true); //jscs:ignore requireCapitalizedConstructors

            links = $$('#' + this.containerId + ' dt a');

            for (i in links) {
                if (links[i].href) {
                    Event.observe(links[i], 'click', this.clickItem.bind(this));
                    this.items[i].dd = this.items[i].next('dd');
                    this.items[i].link = links[i];
                }
            }

            this.initFromCookie();
        },

        /**
         * Init from cookie.
         */
        initFromCookie: function () {
            var activeItemId, visibility;

            if (this.activeOnlyOne &&
                (activeItemId = Cookie.read(this.cookiePrefix() + 'active-item')) !== null) {
                this.hideAllItems();
                this.showItem(this.getItemById(activeItemId));
            } else if (!this.activeOnlyOne) {
                this.items.each(function (item) {
                    if ((visibility = Cookie.read(this.cookiePrefix() + item.id)) !== null) {
                        if (visibility == 0) { //eslint-disable-line eqeqeq
                            this.hideItem(item);
                        } else {
                            this.showItem(item);
                        }
                    }
                }.bind(this));
            }
        },

        /**
         * @return {String}
         */
        cookiePrefix: function () {
            return 'accordion-' + this.containerId + '-';
        },

        /**
         * @param {*} itemId
         * @return {*}
         */
        getItemById: function (itemId) {
            var result = null;

            this.items.each(function (item) {
                if (item.id == itemId) { //eslint-disable-line
                    result = item;
                    throw $break;
                }
            });

            return result;
        },

        /**
         * @param {*} event
         */
        clickItem: function (event) {
            var item = Event.findElement(event, 'dt');

            if (this.activeOnlyOne) {
                this.hideAllItems();
                this.showItem(item);
                Cookie.write(this.cookiePrefix() + 'active-item', item.id, 30 * 24 * 60 * 60);
            } else {
                if (this.isItemVisible(item)) { //eslint-disable-line no-lonely-if
                    this.hideItem(item);
                    Cookie.write(this.cookiePrefix() + item.id, 0, 30 * 24 * 60 * 60);
                } else {
                    this.showItem(item);
                    Cookie.write(this.cookiePrefix() + item.id, 1, 30 * 24 * 60 * 60);
                }
            }
            Event.stop(event);
        },

        /**
         * @param {Object} item
         */
        showItem: function (item) {
            if (item && item.link) {
                if (item.link.href) {
                    this.loadContent(item);
                }

                Element.addClassName(item, 'open');
                Element.addClassName(item.dd, 'open');
            }
        },

        /**
         * @param {Object} item
         */
        hideItem: function (item) {
            Element.removeClassName(item, 'open');
            Element.removeClassName(item.dd, 'open');
        },

        /**
         * @param {*} item
         * @return {*}
         */
        isItemVisible: function (item) {
            return Element.hasClassName(item, 'open');
        },

        /**
         * @param {*} item
         */
        loadContent: function (item) {
            if (item.link.href.indexOf('#') == item.link.href.length - 1) { //eslint-disable-line eqeqeq
                return;
            }

            if (Element.hasClassName(item.link, 'ajax')) {
                this.loadingItem = item;
                this.loader.load(item.link.href, {
                    updaterId: this.loadingItem.dd.id
                }, this.setItemContent.bind(this));

                return;
            }
            location.href = item.link.href;
        },

        /**
         * @param {Object} content
         */
        setItemContent: function (content) {
            if (content.isJSON) {
                return;
            }
            this.loadingItem.dd.innerHTML = content;
        },

        /**
         * Hide all items
         */
        hideAllItems: function () {
            var i;

            for (i in this.items) {
                if (this.items[i].id) {
                    Element.removeClassName(this.items[i], 'open');
                    Element.removeClassName(this.items[i].dd, 'open');
                }
            }
        }
    };
});
