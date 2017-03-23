<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
