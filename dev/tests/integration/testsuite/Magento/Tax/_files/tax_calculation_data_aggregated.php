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

/**
 * Global array that holds test scenarios data
 *
 * @var array
 */
$taxCalculationData = [];

require_once __DIR__ . '/scenarios/excluding_tax_apply_tax_after_discount.php';
require_once __DIR__ . '/scenarios/excluding_tax_apply_tax_after_discount_discount_tax.php';
require_once __DIR__ . '/scenarios/excluding_tax_apply_tax_before_discount.php';
require_once __DIR__ . '/scenarios/excluding_tax_unit.php';
require_once __DIR__ . '/scenarios/excluding_tax_row.php';
require_once __DIR__ . '/scenarios/excluding_tax_total.php';
require_once __DIR__ . '/scenarios/including_tax_unit.php';
require_once __DIR__ . '/scenarios/including_tax_row.php';
require_once __DIR__ . '/scenarios/including_tax_total.php';
require_once __DIR__ . '/scenarios/excluding_tax_multi_item_unit.php';
require_once __DIR__ . '/scenarios/excluding_tax_multi_item_row.php';
require_once __DIR__ . '/scenarios/excluding_tax_multi_item_total.php';
require_once __DIR__ . '/scenarios/including_tax_cross_border_trade_disabled.php';
require_once __DIR__ . '/scenarios/including_tax_cross_border_trade_enabled.php';
require_once __DIR__ . '/scenarios/multi_tax_rule_total_calculate_subtotal_no.php';
require_once __DIR__ . '/scenarios/multi_tax_rule_unit_calculate_subtotal_no.php';
require_once __DIR__ . '/scenarios/multi_tax_rule_total_calculate_subtotal_yes.php';
require_once __DIR__ . '/scenarios/multi_tax_rule_two_row_calculate_subtotal_yes_row.php';
require_once __DIR__ . '/scenarios/multi_tax_rule_two_row_calculate_subtotal_yes_total.php';
