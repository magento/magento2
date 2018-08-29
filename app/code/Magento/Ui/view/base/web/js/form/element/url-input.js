/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'underscore',
    'uiLayout',
    'mage/translate',
    'Magento_Ui/js/form/element/abstract'
], function (_, layout, $t, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            linkedElement: {},
            settingTemplate: 'ui/form/element/urlInput/setting',
            typeSelectorTemplate: 'ui/form/element/urlInput/typeSelector',
            options: [],
            linkedElementInstances: {},
            //checkbox
            isDisplayAdditionalSettings: true,
            settingValue: false,
            settingLabel: $t('Open in new tab'),
            tracks: {
                linkedElement: true
            },
            baseLinkSetting: {
                namePrefix: '${$.name}.',
                dataScopePrefix: '${$.dataScope}.',
                provider: '${$.provider}'
            },
            urlTypes: {},
            listens: {
                settingValue: 'checked',
                disabled: 'hideLinkedElement',
                linkType: 'createChildUrlInputComponent'
            },
            links: {
                linkType: '${$.provider}:${$.dataScope}.type',
                settingValue: '${$.provider}:${$.dataScope}.setting'
            }
        },

        /** @inheritdoc */
        initConfig: function (config) {
            var processedLinkTypes = {},
                baseLinkType = this.constructor.defaults.baseLinkSetting;

            _.each(config.urlTypes, function (linkSettingsArray, linkName) {
                //add link name by link type
                linkSettingsArray.name = baseLinkType.namePrefix + linkName;
                linkSettingsArray.dataScope = baseLinkType.dataScopePrefix + linkName;
                linkSettingsArray.type = linkName;
                linkSettingsArray.disabled = config.disabled;
                linkSettingsArray.visible = config.visible;
                processedLinkTypes[linkName] = {};
                _.extend(processedLinkTypes[linkName], baseLinkType, linkSettingsArray);
            });
            _.extend(this.constructor.defaults.urlTypes, processedLinkTypes);

            this._super();
        },

        /**
         * Initializes observable properties of instance
         *
         * @returns {Abstract} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe('componentTemplate options value linkType settingValue checked isDisplayAdditionalSettings')
                .setOptions();

            return this;
        },

        /**
         * Set options to select based on link types configuration
         *
         * @return {Object}
         */
        setOptions: function () {
            var result = [];

            _.each(this.urlTypes, function (option, key) {
                result.push({
                    value: key,
                    label: option.label,
                    sortOrder: option.sortOrder || 0
                });
            });

            //sort options by sortOrder
            result.sort(function (a, b) {
                return a.sortOrder > b.sortOrder ? 1 : -1;
            });

            this.options(result);

            return this;
        },

        /** @inheritdoc */
        setPreview: function (visible) {
            this.linkedElement().visible(visible);
        },

        /**
         * Initializes observable properties of instance
         *
         * @param {Boolean} disabled
         */
        hideLinkedElement: function (disabled) {
            this.linkedElement().disabled(disabled);
        },

        /** @inheritdoc */
        destroy: function () {
            _.each(this.linkedElementInstances, function (value) {
                value().destroy();
            });
            this._super();
        },

        /**
         * Create child component by value
         *
         * @param {String} value
         * @return void
         */
        createChildUrlInputComponent: function (value) {
            var elementConfig;

            if (!_.isEmpty(value) && _.isUndefined(this.linkedElementInstances[value])) {
                elementConfig = this.urlTypes[value];
                layout([elementConfig]);
                this.linkedElementInstances[value] = this.requestModule(elementConfig.name);
            }
            this.linkedElement = this.linkedElementInstances[value];

        },

        /**
         * Returns linked element to display related field in template
         * @return String
         */
        getLinkedElementName: function () {
            return this.linkedElement;
        },

        /**
         * Add ability to choose check box by clicking on label
         */
        checkboxClick: function () {
            if (!this.disabled()) {
                this.settingValue(!this.settingValue());
            }
        }
    });
});
