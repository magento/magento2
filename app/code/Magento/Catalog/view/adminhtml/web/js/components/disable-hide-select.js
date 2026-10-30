/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
define([
    'Magento_Ui/js/form/element/select',
    'Magento_Catalog/js/components/visible-on-option/strategy',
    'Magento_Catalog/js/components/disable-on-option/strategy'
], function (Element, visibleStrategy, disableStrategy) {
    'use strict';

    return Element.extend(visibleStrategy).extend(disableStrategy);
});
