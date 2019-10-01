<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * List of templates with data-mage-init attribute where JS component is not correctly called.
 *
 * JS component is initialized in php here. These templates cannot be refactored easily.
 */
return [
    ['Magento_Braintree', 'view/frontend/templates/paypal/button_shopping_cart.phtml']
];
