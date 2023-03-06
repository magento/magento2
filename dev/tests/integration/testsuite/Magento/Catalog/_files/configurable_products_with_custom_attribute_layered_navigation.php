<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CacheCleaner;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/ConfigurableProduct/_files/configurable_products.php');

$objectManager = Bootstrap::getObjectManager();

/** @var ProductAttributeRepositoryInterface $attributeRepository */
$attributeRepository = $objectManager->get(ProductAttributeRepositoryInterface::class);
/** @var $attribute Attribute */
$attribute = $attributeRepository->get('test_configurable');

$attribute->setIsSearchable(1)
    ->setIsVisibleInAdvancedSearch(1)
    ->setIsFilterable(true)
    ->setIsFilterableInSearch(true)
    ->setIsVisibleOnFront(1);

$attributeRepository->save($attribute);
CacheCleaner::cleanAll();
