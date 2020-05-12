<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

require __DIR__ . '/../../Eav/_files/empty_attribute_set_rollback.php';
require __DIR__ . '/../../Catalog/_files/categories_rollback.php';

use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Type;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection as AttributeSetCollection;
use Magento\Framework\App\ObjectManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Eav\Api\AttributeRepositoryInterface;

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();

$eavConfig = $objectManager->get(Config::class);
$attributesToDelete = ['test_configurable', 'second_test_configurable'];
/** @var AttributeRepositoryInterface $attributeRepository */
$attributeRepository = $objectManager->get(AttributeRepositoryInterface::class);

foreach ($attributesToDelete as $attributeCode) {
    /** @var AttributeInterface $attribute */
    $attribute = $attributeRepository->get('catalog_product', $attributeCode);
    $attributeRepository->delete($attribute);
}

// remove attribute set
$entityType = $objectManager->create(Type::class)->loadByCode('catalog_product');
/** @var AttributeSetCollection $attributeSetCollection */
$attributeSetCollection = $objectManager->create(
    AttributeSetCollection::class
);
$attributeSetCollection->addFilter('attribute_set_name', 'second_attribute_set');
$attributeSetCollection->addFilter('entity_type_id', $entityType->getId());
$attributeSetCollection->setOrder('attribute_set_id');
$attributeSetCollection->setPageSize(1);
$attributeSetCollection->load();

/** @var AttributeSet $attributeSet */
$attributeSet = $attributeSetCollection->fetchItem();
$attributeSet->delete();
