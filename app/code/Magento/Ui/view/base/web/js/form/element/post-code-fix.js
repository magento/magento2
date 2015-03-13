/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/lib/registry/registry',
    './abstract'
], function (registry, Abstract) {
    'use strict';

    return Abstract.extend({
        /**
         * Converts the result of parent 'getInitialValue' call to boolean
         *
         * @return {Boolean}
         */
        initListeners: function(){
            this._super();
            this.provider.data.on('update:'+this.parentScope+'.country_id', this.update.bind(this));
            return this;
        },

        update: function(value){
            var parentScope  = this.getPart(this.getPart(this.name, -2), -2),
                component = registry.get(parentScope + '.country_id.0'),
                element;

            component
                .options()
                .some(function (el, idx) {
                    element = el;
                    return el.value === value;
                });

            if(!element.is_region_required) {
                this.error(false);
            }
            this.required(!!element.is_region_required);
        }
    });
});