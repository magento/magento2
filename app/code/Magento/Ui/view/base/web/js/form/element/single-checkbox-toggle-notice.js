/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/single-checkbox'
], function (SingleCheckbox) {
    'use strict';

    return SingleCheckbox.extend({
        defaults: {
            notices: [],
            tracks: {
                notice: true
            }
        },

        /**
         * Choose notice on initialization
         *
         * @returns {*|void|Element}
         */
        initialize: function () {
            this._super()
                .chooseNotice();

            return this;
        },

        /**
         * Choose notice function
         *
         * @returns void
         */
        chooseNotice: function () {
            var checkedNoticeNumber = Number(this.checked());

            this.notice = this.notices[checkedNoticeNumber];
        },

        /**
         * Choose notice on update
         *
         * @returns void
         */
        onUpdate: function () {
            this._super();
            this.chooseNotice();
        }
    });
});
