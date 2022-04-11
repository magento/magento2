<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

$bootstrap = Bootstrap::getObjectManager();

/** @var Registry $registry */
$registry = $bootstrap->get(Registry::class);

/** @var RuleRepositoryInterface $ruleRepository */
$ruleRepository = $bootstrap->get(RuleRepositoryInterface::class);

$ruleId = $registry->registry('Magento/SalesRule/_files/cart_rule_free_shipping_subtotal_equals_more_than_30_rollback');
if ($ruleId) {
    try {
        $ruleRepository->deleteById($ruleId);
        $registry->unregister('Magento/SalesRule/_files/cart_rule_free_shipping_subtotal_equals_more_than_30_rollback');
    } catch (NoSuchEntityException $e) {
    }
}
