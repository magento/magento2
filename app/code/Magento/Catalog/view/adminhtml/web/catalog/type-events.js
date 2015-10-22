/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Catalog/js/product/weight-handler',
    'Magento_ConfigurableProduct/js/configurable-type-handler',
    'Magento_Downloadable/downloadable-type-handler',
    'Magento_Bundle/js/bundle-type-handler'
], function($, weight, configurable, downloadable, bundle) {

    return {
        $type: $('#product_type_id'),
        controllers: {
            weight: weight,
            configurable: configurable,
            downloadable: downloadable,
            bundle: bundle
        },
        init: function () {
            //todo: need refactoring
            if (weight.productHasWeight()) {
                this.baseType = {
                    virtual: 'virtual',
                    real: this.$type.val() //simple, configurable
                };
            } else {
                this.baseType = {
                    virtual: this.$type.val(), //downloadable, virtual, grouped, bundle
                    real: 'simple'
                };
            }
            this.baseType.current = this.$type.val();
            this.controllers.type = this.baseType;

            this.bindAll();

            setTimeout(this._notifyType.bind(this), 0);
        },
        bindAll: function () {
            $(document).on('setTypeProduct', function (event, type) {
                this.setType(type);
            }.bind(this));

            //direct change type input
            this.$type.on('change', function() {
                this.baseType.current = this.$type.val();
                this._notifyType();
            }.bind(this));
        },
        getType: function () {
            return {
                base: this.baseType.real,
                virtual: this.baseType.virtual,
                current: this.$type.val()
            };
        },
        setType: function (type) {
            return this.$type.val(type || this.baseType.real).trigger('change');
        },
        _notifyType: function () {
            $(document).trigger('changeTypeProduct', this.controllers);
        }
    };
});
