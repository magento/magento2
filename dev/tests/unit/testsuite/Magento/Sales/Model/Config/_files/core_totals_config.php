<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

return array(
    // Totals declared in Magento_Sales
    'nominal' => array('before' => array('subtotal'), 'after' => array()),
    'subtotal' => array('after' => array('nominal'), 'before' => array('grand_total')),
    'shipping' => array(
        'after' => array('subtotal', 'freeshipping', 'tax_subtotal'),
        'before' => array('grand_total')
    ),
    'grand_total' => array('after' => array('subtotal'), 'before' => array()),
    'msrp' => array('after' => array(), 'before' => array()),
    // Totals declared in Magento_SalesRule
    'freeshipping' => array('after' => array('subtotal'), 'before' => array('tax_subtotal', 'shipping')),
    'discount' => array('after' => array('subtotal', 'shipping'), 'before' => array('grand_total')),
    // Totals declared in Magento_Tax
    'tax_subtotal' => array('after' => array('freeshipping'), 'before' => array('tax', 'discount')),
    'tax_shipping' => array('after' => array('shipping'), 'before' => array('tax', 'discount')),
    'tax' => array('after' => array('subtotal', 'shipping'), 'before' => array('grand_total')),
    // Totals declared in Magento_Weee
    'weee' => array('after' => array('subtotal', 'tax', 'discount', 'grand_total', 'shipping'), 'before' => array())
);
