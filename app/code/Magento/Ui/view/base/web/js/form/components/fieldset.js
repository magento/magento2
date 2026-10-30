/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
            initializeFieldsetDataByDefault: false, /* Data in some fieldsets should be initialized before open */
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
            this._childrenErrorsCount = 0;

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
         * Assigns callbacks on various events of incoming element.
         *
         * @param  {Object} elem
         * @return {Object} - reference to instance
         */
        initElement: function (elem) {
            elem.initContainer(this);

            elem.on({
                'update': this.onChildrenUpdate,
                'loading': this.onContentLoading,
                'error': this.onChildrenError
            });

            if (this.disabled) {
                try {
                    elem.disabled(true);
                } catch (e) {// eslint-disable-line no-unused-vars
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
        onChildrenError: function (message, child) {
            var hasErrors = false;

            if (child && typeof child === 'object') {
                hasErrors = this._updateChildrenErrors(message, child);
            } else if (!message) {
                hasErrors = this._isChildrenHasErrors(hasErrors, this);
            }

            if (!message && this._childrenErrorsCount > 0 && hasErrors === false) {
                hasErrors = this._resyncChildrenErrors(this);
            }

            this.error(hasErrors || message);

            if (hasErrors || message) {
                this.open();
            }
        },

        /**
         * Update cached errors count for child element.
         *
         * @param {String} message
         * @param {Object} child
         * @return {Boolean}
         * @private
         */
        _updateChildrenErrors: function (message, child) {
            var hasError = !!message,
                prevHasError;

            prevHasError = !!child.__uiHasError;

            if (hasError !== prevHasError) {
                this._childrenErrorsCount += hasError ? 1 : -1;
                this._childrenErrorsCount = Math.max(0, this._childrenErrorsCount);
                child.__uiHasError = hasError;
            }

            return this._childrenErrorsCount > 0;
        },

        /**
         * Returns errors of children if exist
         *
         * @param {Boolean} hasErrors
         * @param {*} container
         * @return {Boolean}
         * @private
         */
        _isChildrenHasErrors: function (hasErrors, container) {
            var self = this;

            if (hasErrors === false && container.hasOwnProperty('elems')) {
                hasErrors = container.elems.some('error');

                if (hasErrors === false && container.hasOwnProperty('_elems')) {
                    container._elems.forEach(function (child) {

                        if (hasErrors === false) {
                            hasErrors = self._isChildrenHasErrors(hasErrors, child);
                        }
                    });
                }
            }

            return hasErrors;
        },

        /**
         * Re-sync cached error count with recursive check.
         *
         * @param {*} container
         * @return {Boolean}
         * @private
         */
        _resyncChildrenErrors: function (container) {
            var hasErrors = this._isChildrenHasErrors(false, container);

            if (!hasErrors) {
                this._childrenErrorsCount = 0;
            }

            return hasErrors;
        },

        /**
         * Callback that sets loading property to true.
         */
        onContentLoading: function (isLoading) {
            this.loading(isLoading);
        }
    });
});
