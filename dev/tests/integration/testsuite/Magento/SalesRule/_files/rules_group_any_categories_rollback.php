<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
declare(strict_types=1);
=======
>>>>>>> upstream/2.2-develop

use Magento\SalesRule\Model\Rule;

$collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get(\Magento\SalesRule\Model\ResourceModel\Rule\Collection::class);

/** @var Rule $rule */
foreach ($collection as $rule) {
    $rule->delete();
}
