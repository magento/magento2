<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture(
    'Magento/CatalogRuleConfigurable/_files/configurable_product_with_percent_rule_rollback.php'
);
Resolver::getInstance()->requireDataFixture(
    'Magento/Customer/_files/customer_group_rollback.php'
);

$objectManager = Bootstrap::getObjectManager();
/** @var CatalogRuleRepositoryInterface $ruleRepository */
$ruleRepository = $objectManager->create(CatalogRuleRepositoryInterface::class);
/** @var IndexBuilder $indexBuilder */
$indexBuilder = $objectManager->get(IndexBuilder::class);
/** @var CollectionFactory $ruleCollectionFactory */
$ruleCollectionFactory = $objectManager->get(CollectionFactory::class);
$ruleCollection = $ruleCollectionFactory->create()
    ->addFieldToFilter(
        'name',
        [
            'in' => [
                'Same priority rule for NOT LOGGED IN customer group',
                'Same priority rule for custom customer group',
            ]
        ]
    );
foreach ($ruleCollection as $rule) {
    $ruleRepository->delete($rule);
}
$indexBuilder->reindexFull();
