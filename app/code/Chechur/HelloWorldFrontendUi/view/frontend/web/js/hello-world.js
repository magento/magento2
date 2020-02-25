/**
 * @api
 */
define([
    'uiElement',
    'mage/storage'
], function(Component, storage) {
    'use strict';

    return Component.extend({
        defaults: {
            helloMessage: '',
            template: 'Chechur_HelloWorldFrontendUi/hello_world',
        },

        /**
         * @returns {Object}
         */
        initObservable: function () {
            this._super().observe([
                'helloMessage'
            ]);
            this.updateMessage();

            return this;
        },

        /**
         * Update hello message by rest API request.
         */
        updateMessage: function () {
            let $this = this;

            return storage.get(
                'rest/V1/hello',
                false
            ).done(function (response) {
                $this.helloMessage(response);
            }).error(function (response) {
                $this.helloMessage('');
            });
        }
    });
});
