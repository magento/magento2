/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'Magento_Ui/js/lib/registry/registry',
    './abstract'
], function (_, registry, Abstract) {
    'use strict';

    return Abstract.extend({
        /**
         * @return {this}
         */
        initListeners: function(){
            this._super()
                .update()
                .provider.data.on('update:'+this.parentScope+'.country_id', this.update.bind(this));

            return this;
        },

        update: function(){
            var parentScope  = this.getPart(this.getPart(this.name, -2), -2),
                countryComponent = registry.get(parentScope + '.country_id.0'),
                value = countryComponent.value(),
                element;

            countryComponent
                .options()
                .some(function (el) {
                    element = el;
                    return el.value === value;
                });

            if(!element.is_region_required) {
                this.error(false);
                this.validation = _.omit(this.validation, 'required-entry');
            } else {
                this.validation['required-entry'] = true;
            }
            this.required(!!element.is_region_required);

            return this;
        }
    });
});