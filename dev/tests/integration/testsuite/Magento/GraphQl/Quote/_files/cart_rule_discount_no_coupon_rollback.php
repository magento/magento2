<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\ResourceModel\Rule as RuleResource;
use Magento\SalesRule\Model\RuleFactory as RuleFactory;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var RuleRepositoryInterface $ruleRepository */
$ruleRepository = $objectManager->get(RuleRepositoryInterface::class);
/** @var RuleResource $ruleResource */
$ruleResource = $objectManager->get(RuleResource::class);
/** @var RuleFactory $ruleFactory */
$ruleFactory = $objectManager->get(RuleFactory::class);
$salesRule = $ruleFactory->create();

$ruleResource->load($salesRule, '50% Off for all orders', 'name');
// FIXME: the rule cannot be found for some reason
if ($salesRule->getRuleId()) {
    $ruleRepository->deleteById($salesRule->getRuleId());
}
