<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\SalesRule\Model\Rule;

$collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get(\Magento\SalesRule\Model\ResourceModel\Rule\Collection::class);

/** @var Rule $rule */
foreach ($collection as $rule) {
    $rule->delete();
}
