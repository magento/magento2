<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\CatalogRule\Api\Data\RuleInterface;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\CatalogRule\Model\ResourceModel\Rule\Product\Price;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var CollectionFactory $catalogRuleCollectionFactory */
$catalogRuleCollectionFactory = $objectManager->get(CollectionFactory::class);
/** @var CatalogRuleRepositoryInterface $catalogRuleRepository */
$catalogRuleRepository = $objectManager->get(CatalogRuleRepositoryInterface::class);
/** @var Price $catalogRuleProductPriceResource */
$catalogRuleProductPriceResource = $objectManager->get(Price::class);
$catalogRuleCollection = $catalogRuleCollectionFactory->create();
/** @var RuleInterface $catalogRule */
foreach ($catalogRuleCollection->getItems() as $catalogRule) {
    $catalogRuleRepository->delete($catalogRule);
}
$catalogRuleProductPriceResource->getConnection()->delete($catalogRuleProductPriceResource->getMainTable());
