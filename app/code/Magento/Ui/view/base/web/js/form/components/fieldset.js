/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Ui/js/lib/collapsible',
    'underscore'
], function (Collapsible, _) {
    'use strict';

    return Collapsible.extend({
        defaults: {
            template: 'ui/form/fieldset',
            collapsible: false,
            changed: false,
            loading: false,
            error: false,
            opened: false,
            level: 0,
            visible: true,
            initializeFieldsetDataByDefault: false,    /* Data in some fieldsets should be initialized before open */
            disabled: false,
            listens: {
                'opened': 'onVisibilityChange'
            },
            additionalClasses: {}
        },

        /**
         * Extends instance with defaults. Invokes parent initialize method.
         * Calls initListeners and pushParams methods.
         */
        initialize: function () {
            _.bindAll(this, 'onChildrenUpdate', 'onChildrenError', 'onContentLoading');

            return this._super()
                ._setClasses();
        },

        /**
         * Initializes components' configuration.
         *
         * @returns {Fieldset} Chainable.
         */
        initConfig: function () {
            this._super();
            this._wasOpened = this.opened || !this.collapsible;

            return this;
        },

        /**
         * Calls initObservable of parent class.
         * Defines observable properties of instance.
         *
         * @returns {Object} Reference to instance
         */
        initObservable: function () {
            this._super()
                .observe('changed loading error visible');

            return this;
        },

        /**
         * Calls parent's initElement method.
         * Assignes callbacks on various events of incoming element.
         *
         * @param  {Object} elem
         * @return {Object} - reference to instance
         */
        initElement: function (elem) {
            elem.initContainer(this);

            elem.on({
                'update':   this.onChildrenUpdate,
                'loading':  this.onContentLoading,
                'error':  this.onChildrenError
            });

            if (this.disabled) {
                try {
                    elem.disabled(true);
                }
                catch (e) {

                }
            }

            return this;
        },

        /**
         * Is being invoked on children update.
         * Sets changed property to one incoming.
         *
         * @param  {Boolean} hasChanged
         */
        onChildrenUpdate: function (hasChanged) {
            if (!hasChanged) {
                hasChanged = _.some(this.delegate('hasChanged'));
            }

            this.bubble('update', hasChanged);
            this.changed(hasChanged);
        },

        /**
         * Extends 'additionalClasses' object.
         *
         * @returns {Group} Chainable.
         */
        _setClasses: function () {
            var additional = this.additionalClasses,
                classes;

            if (_.isString(additional)) {
                additional = this.additionalClasses.split(' ');
                classes = this.additionalClasses = {};

                additional.forEach(function (name) {
                    classes[name] = true;
                }, this);
            }

            _.extend(this.additionalClasses, {
                'admin__collapsible-block-wrapper': this.collapsible,
                _show: this.opened,
                _hide: !this.opened,
                _disabled: this.disabled
            });

            return this;
        },

        /**
         * Handler of the "opened" property changes.
         *
         * @param {Boolean} isOpened
         */
        onVisibilityChange: function (isOpened) {
            if (!this._wasOpened) {
                this._wasOpened = isOpened;
            }
        },

        /**
         * Is being invoked on children validation error.
         * Sets error property to one incoming.
         *
         * @param {String} message - error message.
         */
        onChildrenError: function (message) {
            var hasErrors = this.elems.some('error');

            this.error(hasErrors || message);
        },

        /**
         * Callback that sets loading property to true.
         */
        onContentLoading: function (isLoading) {
            this.loading(isLoading);
        }
    });
});
