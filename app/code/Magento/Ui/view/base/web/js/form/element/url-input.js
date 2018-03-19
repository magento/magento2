/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'underscore',
    'mageUtils',
    'uiLayout',
    'mage/translate',
    'Magento_Ui/js/lib/validation/validator',
    'Magento_Ui/js/form/element/abstract'
], function (_, utils, layout, $t, validator, Abstract) {
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
            //observable object(without functional call)
            tracks: {
                linkedElement: true
            },
            urlTypes: {
                base: {
                    namePrefix: '${$.name}.',
                    dataScopePrefix: '${$.dataScope}.',
                    provider: '${$.provider}'
                }
            },
            listens: {
                value: 'renderComponent',
                checked: 'updateSettingValue',
                disabled: 'hideLinkedElement'
            },
            links: {
                linkType: '${$.provider}:${$.dataScope}.type',
                settingValue: '${$.provider}:${$.dataScope}.setting',
                value: false
            }
        },

        /**
         * Initializes observable properties of instance
         *
         * @returns {Abstract} Chainable.
         */
        initObservable: function () {

            this._super();

            this.observe('componentTemplate options value linkType settingValue checked isDisplayAdditionalSettings')
                .processLinkTypes()
                .setOptions();

            return this;
        },

        /**
         * Adds link types array with default settings
         */
        processLinkTypes: function () {
            var processedLinkTypes = {},
                baseLinkType = this.urlTypes.base;

            delete this.urlTypes.base;
            _.each(this.urlTypes, function (linkSettingsArray, linkName) {
                //add link name by link type
                linkSettingsArray.name = baseLinkType.namePrefix + linkName;
                linkSettingsArray.dataScope = baseLinkType.dataScopePrefix + linkName;
                linkSettingsArray.type = linkName;
                processedLinkTypes[linkName] = {};
                _.extend(processedLinkTypes[linkName], baseLinkType, linkSettingsArray);
            });
            _.extend(this.urlTypes, processedLinkTypes);

            return this;
        },

        /**
         * Set options to select based on link types configuration
         *
         * @return {exports}
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

        /**
         * @inheritdoc
         */
        setPreview: function (visible) {
            this.linkedElement().visible(visible);
        },

        /**
         * Initializes observable properties of instance
         *
         * @param {Boolean} disabled
         * @returns void.
         */
        hideLinkedElement: function (disabled) {
            this.linkedElement().disabled(disabled);
        },

        /**
         * @{inheritDoc}
         */
        destroy: function () {
            _.each(this.linkedElementInstances, function (value) {
                value().destroy();
            });
            this._super();
        },

        /**
         * Set url setting value to datasource
         *
         * @param {Boolean} checked
         *
         * @return void
         */
        updateSettingValue: function (checked) {
            if (checked) {
                this.source.set(this.dataScope + '.setting', checked);
            }
        },

        /**
         * Initialize linked input field based on linked type
         *
         * @param {String} value
         *
         * @return void
         */
        renderComponent: function (value) {

            if (!_.isUndefined(value) && value) {
                this.getChildUrlInputComponent(value);
                //to store current element
                this.linkedElement = this.linkedElementInstances[value];
                this.linkType(value);
            }
        },

        /**
         * Returns child component by value
         *
         * @param {String} value
         * @return void
         */
        getChildUrlInputComponent: function (value) {
            var elementConfig;

            if (_.isUndefined(this.linkedElementInstances[value])) {
                elementConfig = this.urlTypes[value];
                layout([elementConfig]);
                this.linkedElementInstances[value] = this.requestModule(elementConfig.name);
            }
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
            this.checked(!this.checked());
        }
    });
});
