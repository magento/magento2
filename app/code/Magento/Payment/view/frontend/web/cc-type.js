/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";

    $.widget('mage.creditCardType', {
        options: {
            typeCodes: ['SS', 'SM', 'SO'] // Type codes for Switch/Maestro/Solo credit cards.
        },

        /**
         * Bind change handler to select element and trigger the event to show/hide
         * the Switch/Maestro or Solo credit card type container for those credit card types.
         * @private
         */
        _create: function() {
            this.element.on('change', $.proxy(this._toggleCardType, this)).trigger('change');
        },

        /**
         * Toggle the Switch/Maestro and Solo credit card type container depending on which
         * credit card type is selected.
         * @private
         */
        _toggleCardType: function() {
            $(this.options.creditCardTypeContainer)
                .toggle($.inArray(this.element.val(), this.options.typeCodes) !== -1);
        }
    });

    return $.mage.creditCardType;
});