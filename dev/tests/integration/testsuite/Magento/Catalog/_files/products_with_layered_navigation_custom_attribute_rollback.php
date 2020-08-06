<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\TestFramework\Helper\CacheCleaner;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Eav/_files/empty_attribute_set_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/categories_rollback.php');

$eavConfig = Bootstrap::getObjectManager()->get(\Magento\Eav\Model\Config::class);
$attributesToDelete = ['test_configurable', 'second_test_configurable'];
/** @var AttributeRepositoryInterface $attributeRepository */
$attributeRepository = Bootstrap::getObjectManager()->get(AttributeRepositoryInterface::class);

foreach ($attributesToDelete as $attributeCode) {
    /** @var \Magento\Eav\Api\Data\AttributeInterface $attribute */
    $attribute = $attributeRepository->get('catalog_product', $attributeCode);
    $attributeRepository->delete($attribute);
}
/** @var $product \Magento\Catalog\Model\Product */
$objectManager = Bootstrap::getObjectManager();

$entityType = $objectManager->create(\Magento\Eav\Model\Entity\Type::class)->loadByCode('catalog_product');

// remove attribute set

/** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attributeSetCollection */
$attributeSetCollection = $objectManager->create(
    \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection::class
);
$attributeSetCollection->addFilter('attribute_set_name', 'second_attribute_set');
$attributeSetCollection->addFilter('entity_type_id', $entityType->getId());
$attributeSetCollection->setOrder('attribute_set_id'); // descending is default value
$attributeSetCollection->setPageSize(1);
$attributeSetCollection->load();

/** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
$attributeSet = $attributeSetCollection->fetchItem();
$attributeSet->delete();

CacheCleaner::cleanAll();
