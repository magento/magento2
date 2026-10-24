<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var RuleRepositoryInterface $ruleRepository */
$ruleRepository = $objectManager->get(RuleRepositoryInterface::class);
/** @var RuleCollectionFactory  $ruleCollectionFactory */
$ruleCollectionFactory = $objectManager->get(RuleCollectionFactory::class);
$ruleCollection = $ruleCollectionFactory->create();

foreach ($ruleCollection->getItems() as $rule) {
    $ruleRepository->deleteById($rule->getRuleId());
}
