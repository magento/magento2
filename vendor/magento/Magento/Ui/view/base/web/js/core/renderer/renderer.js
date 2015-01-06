/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
define([
    './components/types',
    './components/layout',
    'Magento_Ui/js/lib/class'
], function(Types, Layout, Class){
    'use strict';

    return Class.extend({
        initialize: function(data){
            this.types = new Types(data.types);
            this.layout = new Layout(data.layout, this.types);

            return this;
        },

        render: function(data){
            this.layout.run(data.layout);
            this.types.set(data.types);
        }
    });
});