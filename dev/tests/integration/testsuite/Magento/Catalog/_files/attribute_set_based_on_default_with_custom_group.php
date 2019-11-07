<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeGroupInterface;
use Magento\Eav\Api\Data\AttributeSetInterfaceFactory;
use Magento\Eav\Model\Entity\Attribute\GroupFactory;
use Magento\Eav\Model\Entity\Type;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var AttributeSetRepositoryInterface $attributeSetRepository */
$attributeSetRepository = $objectManager->get(AttributeSetRepositoryInterface::class);
/** @var AttributeSetInterfaceFactory $attributeSetFactory */
$attributeSetFactory = $objectManager->get(AttributeSetInterfaceFactory::class);
/** @var Type $entityType */
$entityType = $objectManager->create(Type::class)->loadByCode(ProductAttributeInterface::ENTITY_TYPE_CODE);
$attributeSet = $attributeSetFactory->create(
    [
        'data' => [
            'id' => null,
            'attribute_set_name' => 'new_attribute_set',
            'entity_type_id' => $entityType->getId(),
            'sort_order' => 300,
        ],
    ]
);
$attributeSet->isObjectNew(true);
$attributeSet->setHasDataChanges(true);
$attributeSet->validate();
$attributeSetRepository->save($attributeSet);
$attributeSet->initFromSkeleton($entityType->getDefaultAttributeSetId());
/** @var AttributeGroupInterface $newGroup */
$newGroup = $objectManager->get(GroupFactory::class)->create();
$newGroup->setId(null)
    ->setAttributeGroupName('Test attribute group name')
    ->setAttributeSetId($attributeSet->getAttributeSetId())
    ->setSortOrder(11)
    ->setAttributes([]);
/** @var AttributeGroupInterface[] $groups */
$groups = $attributeSet->getGroups();
array_push($groups, $newGroup);
$attributeSet->setGroups($groups);
$attributeSetRepository->save($attributeSet);
