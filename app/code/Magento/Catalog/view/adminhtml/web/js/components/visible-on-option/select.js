/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
define([
    'Magento_Ui/js/form/element/select',
    'Magento_Catalog/js/components/visible-on-option/strategy'
], function (Element, strategy) {
    'use strict';

    return Element.extend(strategy);
});
