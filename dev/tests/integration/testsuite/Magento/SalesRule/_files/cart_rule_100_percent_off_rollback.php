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

$objectManager = Bootstrap::getObjectManager();
/** @var Magento\Framework\Registry $registry */
$registry = Bootstrap::getObjectManager()->get(Registry::class);
/** @var RuleRepositoryInterface $ruleRepository */
$ruleRepository = $objectManager->get(RuleRepositoryInterface::class);

/** @var Magento\SalesRule\Model\Rule $rule */
$ruleId = $registry->registry('Magento/SalesRule/_files/cart_rule_100_percent_off');

if ($ruleId) {
    try {
        $ruleRepository->deleteById($ruleId);
        // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
    } catch (NoSuchEntityException $e) {
        /**
         * Tests which are wrapped with MySQL transaction clear all data by transaction rollback.
         */
    }
}
