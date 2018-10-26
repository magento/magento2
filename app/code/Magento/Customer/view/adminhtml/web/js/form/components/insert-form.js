/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/insert-form'
], function (Insert) {
    'use strict';

    return Insert.extend({
        // Should be refactored after form save.!!!!!
        // defaults: {
        //     updateModalProvider: '${ $.parentName }',
        //     subTitlePrefix: $t('Belongs to '),
        //     switcherSelector: '.store-switcher',
        //     toRemove: [],
        //     // imports: {
        //     //     removeResponseData: '${ $.removeResponseProvider }',
        //     //     modalTitle: '${ $.modalTitleProvider }',
        //     //     modalSubTitle: '${ $.modalSubTitleProvider }',
        //     //     destroyClosedModalContents: '${ $.updateModalProvider }:state'
        //     // },
        //     // listens: {
        //     //     responseData: 'afterUpdate',
        //     //     removeResponseData: 'afterRemove',
        //     //     modalTitle: 'changeModalTitle',
        //     //     modalSubTitle: 'changeModalSubTitle'
        //     // },
        //     modules: {
        //         updateModal: '${ $.updateModalProvider }',
        //         removeModal: '${ $.removeModalProvider }',
        //         upcomingListing: 'index = ${ $.upcomingListingProvider }'
        //     }
        // },
        //
        // /** @inheritdoc **/
        // initialize: function () {
        //     _.bindAll(this, 'onSwitcherSelect');
        //     this._super();
        //     this.updateModal(this.initSwitcherHandler.bind(this));
        //
        //     return this;
        // },
        //
        // initConfig: function (options) {
        //   debugger;
        //     return this._super();
        // },
        //
        // /** @inheritdoc */
        // destroyInserted: function () {
        //     if (this.isRendered) {
        //         _.each(this.toRemove, function (componentName) {
        //             registry.get(componentName, function (component) {
        //                 if (component.hasOwnProperty('delegate')) {
        //                     component.delegate('destroy');
        //                 } else {
        //                     component.destroy();
        //                 }
        //             });
        //         });
        //     }
        //
        //     this._super();
        // },
        //
        // // /**
        // //  * Form save callback.
        // //  *
        // //  * @param {Object} data
        // //  */
        // // afterUpdate: function (data) {
        // //     if (!data.error) {
        // //         this.updateModal('closeModal');
        // //         this.upcomingListing('reload');
        // //     }
        // // },
        //
        // // /**
        // //  * Form remove callback.
        // //  *
        // //  * @param {Object} data
        // //  */
        // // afterRemove:  function (data) {
        // //     if (!data.error) {
        // //         this.removeModal('closeModal');
        // //         this.afterUpdate(data);
        // //     }
        // // },
        //
        // // /**
        // //  * Change modal title.
        // //  *
        // //  * @param {String} title
        // //  */
        // // changeModalTitle: function (title) {
        // //     this.updateModal('setTitle', title);
        // // },
        // //
        // // /**
        // //  * Change modal sub title.
        // //  *
        // //  * @param {String} subTitle
        // //  */
        // // changeModalSubTitle: function (subTitle) {
        // //     subTitle = subTitle ?
        // //     this.subTitlePrefix + this.modalTitle + ' ' + subTitle :
        // //         '';
        // //
        // //     this.updateModal('setSubTitle', subTitle);
        // // },
        //
        // /**
        //  * Destroy contents of modal when it is closed
        //  *
        //  * @param {Boolean} state
        //  */
        // destroyClosedModalContents: function (state) {
        //     if (state === false) {
        //         this.destroyInserted();
        //     }
        // },
        //
        // /**
        //  * Switcher initialization.
        //  */
        // initSwitcherHandler: function () {
        //     var switcherSelector = this.updateModal().rootSelector + ' ' + this.switcherSelector,
        //         self = this;
        //
        //     $.async(switcherSelector, function (switcher) {
        //         $(switcher).on('click', 'li a', self.onSwitcherSelect);
        //     });
        // },
        //
        // /**
        //  * Store switcher selection handler.
        //  * @param {Object} e - event object.
        //  */
        // onSwitcherSelect: function (e) {
        //     var self = this,
        //         param = $(e.currentTarget).data('param'),
        //         params = {
        //             store: 0
        //         };
        //
        //     params[param] = $(e.currentTarget).data('value');
        //
        //     uiConfirm({
        //         content:  $t('Please confirm scope switching. All data that hasn\'t been saved will be lost.'),
        //         actions: {
        //
        //             /** Confirm callback. */
        //             confirm: function () {
        //                 self.destroyInserted();
        //                 params = _.extend(self.previousParams, params);
        //                 self.render(params);
        //             }
        //         }
        //     });
        // }
    });
});
