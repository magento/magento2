<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

use Magento\SalesRule\Model\Rule;

$collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get(\Magento\SalesRule\Model\ResourceModel\Rule\Collection::class);

/** @var Rule $rule */
foreach ($collection as $rule) {
    $rule->delete();
}
