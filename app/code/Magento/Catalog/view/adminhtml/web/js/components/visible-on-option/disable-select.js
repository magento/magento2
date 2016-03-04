/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/form/element/select',
    'Magento_Catalog/js/components/visible-on-option/strategy',
    'Magento_Catalog/js/components/visible-on-option/disable-strategy'
], function (Element, strategy, disableStrategy) {
    'use strict';

    return Element.extend(strategy).extend(disableStrategy);
});
