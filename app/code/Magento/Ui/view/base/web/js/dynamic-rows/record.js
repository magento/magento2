/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'uiRegistry',
    'uiCollection'
], function (_, registry, uiCollection) {
    'use strict';

    return uiCollection.extend({
        defaults: {
            visible: true,
            disabled: true,
            headerLabel: '',
            label: '',
            positionProvider: 'position',
            imports: {
                data: '${ $.provider }:${ $.dataScope }'
            },
            listens: {
                position: 'initPosition'
            },
            links: {
                position: '${ $.name }.${ $.positionProvider }:value'
            },
            exports: {
                index: '${ $.provider }:${ $.dataScope }.record_id'
            },
            modules: {
                parentComponent: '${ $.parentName }'
            }
        },

        /**
         * Calls 'initObservable' of parent
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super()
                .track('position')
                .observe([
                    'visible',
                    'disabled',
                    'data',
                    'label'
                ]);

            return this;
        },

        /**
         * Init element position
         *
         * @param {Number} position - element position
         */
        initPosition: function (position) {
            this.parentComponent().setMaxPosition(position, this);

            if (!position) {
                this.position = this.parentComponent().maxPosition;
            }
        },

        /**
         * Get label for collapsible header
         *
         * @param {String} label
         *
         * @returns {String}
         */
        getLabel: function (label) {
            if (_.isString(label)) {
                this.label(label);
            } else if (label && this.label()) {
                return this.label();
            } else {
                this.label(this.headerLabel);
            }

            return this.label();
        },

        /**
         * Set visibility to record child
         *
         * @param {Boolean} state
         */
        setVisible: function (state) {
            this.elems.each(function (cell) {
                cell.visible(state);
            });
        },

        /**
         * Set visibility to child by index
         *
         * @param {Number} index
         * @param {Boolean} state
         */
        setVisibilityColumn: function (index, state) {
            index = parseInt(index, 10);
            this.elems()[index].visible(state);
        },

        /**
         * Set disabled to child
         *
         * @param {Boolean} state
         */
        setDisabled: function (state) {
            this.elems.each(function (cell) {
                cell.disabled(state);
            });
        },

        /**
         * Set disabled to child by index
         *
         * @param {Number} index
         * @param {Boolean} state
         */
        setDisabledColumn: function (index, state) {
            index = parseInt(index, 10);
            this.elems()[index].disabled(state);
        }
    });
});
