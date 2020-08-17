<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\SalesRule\Model\Rule;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Registry;

/** @var Registry $registry */
$registry = Bootstrap::getObjectManager()->get(Registry::class);
$rule = Bootstrap::getObjectManager()->get(Rule::class);

/** @var Rule $rule */
$ruleId = $registry->registry('50% Off on Configurable parent category');
$rule->load($ruleId);
if ($rule->getId()) {
    $rule->delete();
}
