<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

use Magento\SalesRule\Model\Rule;

$collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get(\Magento\SalesRule\Model\ResourceModel\Rule\Collection::class);

/** @var Rule $rule */
foreach ($collection as $rule) {
    $rule->delete();
}
