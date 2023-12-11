/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'uiComponent',
    'mage/translate',
    'text!Magento_MediaGalleryUi/template/grid/massactions/massactionButtons.html'
], function ($, Component, $t, massactionButtons) {
    'use strict';

    return Component.extend({
        defaults: {
            gridSelector: '[data-id="media-gallery-masonry-grid"]',
            standAloneTitle: 'Manage Gallery',
            slidePanelTitle: 'Media Gallery',
            defaultTitle: null,
            are: null,
            standAloneArea: 'standalone',
            slidepanelArea: 'slidepanel',
            massactionButtonsSelector: '.massaction-buttons',
            buttonsSelectorStandalone: '.page-actions-buttons',
            buttonsSelectorSlidePanel: '.page-actions.floating-header',
            buttons: '.page-main-actions :button',
            massactionModeTitle: $t('Select Images to Delete')
        },

        /**
         * Initializes media gallery massaction component.
         *
         * @returns {Sticky} Chainable.
         */
        initialize: function () {
            this._super().observe([
                'massActionMode'
            ]);

            return this;
        },

        /**
         * Switch massaction view state per active mode.
         */
        switchView: function () {
            this.changePageTitle();
            this.switchButtons();
        },

        /**
         * Hide or show buttons per active mode.
         */
        switchButtons: function () {
            if (this.massActionMode()) {
                this.activateMassactionButtonView();
            } else {
                this.revertButtonsToDefaultView();
            }
        },

        /**
         * Sets buttons to default regular -mode view.
         */
        revertButtonsToDefaultView: function () {
            $(this.buttons).removeClass('no-display');
            $(this.massactionButtonsSelector).remove();
        },

        /**
          * Activate mass action buttons view
          */
        activateMassactionButtonView: function () {
            var buttonsContainer;

            $(this.buttons).addClass('no-display');

            buttonsContainer =  this.area === this.standAloneArea ?
                this.buttonsSelectorStandalone :
                this.buttonsSelectorSlidePanel;

            $(buttonsContainer).append(massactionButtons);
            $(this.massactionButtonsSelector).applyBindings();
        },

        /**
         * Change page title per active mode.
         */
        changePageTitle: function () {
            var title = $('h1:contains(' + this.standAloneTitle + ')'),
                titleSelector;

            if (title.length === 1)  {
                titleSelector = title;
                this.area = this.standAloneArea;
            } else {
                titleSelector = $('h1:contains(' + this.slidePanelTitle + ')');
                this.area = this.slidepanelArea;
            }

            if (this.massActionMode()) {
                this.defaultTitle = titleSelector.text();
                titleSelector.text(this.massactionModeTitle);
            } else {
                titleSelector = $('h1:contains(' + this.massactionModeTitle + ')');
                titleSelector.text(this.defaultTitle);
            }
        }
    });
});
