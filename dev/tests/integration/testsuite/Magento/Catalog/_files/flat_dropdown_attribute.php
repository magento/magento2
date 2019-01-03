<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Eav\Model\Entity;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Api\Data\AttributeOptionInterfaceFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;

/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var AttributeOptionInterfaceFactory $attributeOptionFactory */
$attributeOptionFactory = $objectManager->get(AttributeOptionInterfaceFactory::class);
/** @var ProductAttributeRepositoryInterface $attributeRepository */
$attributeRepository = $objectManager->get(ProductAttributeRepositoryInterface::class);
/** @var $installer CategorySetup */
$installer = $objectManager->create(CategorySetup::class);
$entityModel = $objectManager->create(Entity::class);
$attributeSetId = $installer->getAttributeSetId(Product::ENTITY, 'Default');
$entityTypeId = $entityModel->setType(Product::ENTITY)
    ->getTypeId();
$groupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);
/** @var ProductAttributeInterface $attribute */
$attribute = $objectManager->create(ProductAttributeInterface::class);

$attribute->setAttributeCode('flat_attribute')
    ->setEntityTypeId($entityTypeId)
    ->setIsVisible(true)
    ->setFrontendInput('select')
    ->setIsFilterable(1)
    ->setIsUserDefined(1)
    ->setUsedInProductListing(1)
    ->setBackendType('int')
    ->setIsUsedInGrid(1)
    ->setIsVisibleInGrid(1)
    ->setIsFilterable(0)
    ->setIsHtmlAllowedOnFront(1)
    ->setIsFilterableInGrid(1)
    ->setAttributeGroupId($groupId)
    ->setIsGlobal(0)
    ->setIsUsedInProductListing(1)
    ->setFrontendLabel('nobody cares')
    ->setAttributeGroupId($groupId)
    ->setAttributeSetId(4);

$optionsArray = [
    [
        'label' => 'Option 1',
        'value' => 'option_1'
    ],
    [
        'label' => 'Option 2',
        'value' => 'option_2'
    ],
    [
        'label' => 'Option 3',
        'value' => 'option_3'
    ],
];

$options = [];
foreach ($optionsArray as $option) {
    $options[] = $attributeOptionFactory->create(['data' => $option]);
}
$attribute->setOptions($options);
$attributeRepository->save($attribute);
