<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Catalog\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\AttributeGroupRepositoryInterface;
use Magento\Eav\Api\Data\AttributeGroupInterface;
use Magento\Eav\Api\Data\AttributeGroupInterfaceFactory;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Eav\Api\Data\AttributeSetInterfaceFactory;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\Api\DataObjectHelper;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$attributeSetFactory = $objectManager->get(AttributeSetInterfaceFactory::class);
$attributeGroupFactory = $objectManager->get(AttributeGroupInterfaceFactory::class);
/** @var DataObjectHelper $dataObjectHelper */
$dataObjectHelper = $objectManager->get(DataObjectHelper::class);
/** @var AttributeGroupRepositoryInterface $attributeGroupRepository */
$attributeGroupRepository = $objectManager->get(AttributeGroupRepositoryInterface::class);
/** @var AttributeSetRepositoryInterface $attributeSetRepository */
$attributeSetRepository = $objectManager->get(AttributeSetRepositoryInterface::class);

/** @var AttributeSetInterface $attributeSet */
$attributeSet = $attributeSetFactory->create();
$entityTypeId = $objectManager->create(Type::class)->loadByCode('catalog_product')->getId();
$dataObjectHelper->populateWithArray(
    $attributeSet,
    [
        'attribute_set_name' => 'attribute_set_test',
        'entity_type_id' => $entityTypeId,
    ],
    AttributeSetInterface::class
);
$attributeSetRepository->save($attributeSet);

/** @var AttributeGroupInterface $attributeGroup */
$attributeGroup = $attributeGroupFactory->create();
$dataObjectHelper->populateWithArray(
    $attributeGroup,
    [
        'attribute_set_id' => $attributeSet->getAttributeSetId(),
        'attribute_group_name' => 'attribute-group-name',
        'default_id' => 1,
    ],
    AttributeGroupInterface::class
);
$attributeGroupRepository->save($attributeGroup);

// during renaming group code is not changed
$attributeGroup->setAttributeGroupName('attribute-group-renamed');
$attributeGroupRepository->save($attributeGroup);
