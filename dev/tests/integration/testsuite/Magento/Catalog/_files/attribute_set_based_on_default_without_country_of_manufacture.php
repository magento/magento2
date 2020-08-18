<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Group;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\Entity\Type;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var ProductAttributeRepositoryInterface $attributeRepository */
$attributeRepository = $objectManager->get(ProductAttributeRepositoryInterface::class);
/** @var ProductAttributeInterface $attributeCountryOfManufacture */
$attributeCountryOfManufacture = $attributeRepository->get('country_of_manufacture');

/** @var Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
$attributeSet = $objectManager->create(Set::class);
/** @var Type $entityType */
$entityType = $objectManager->create(Type::class)
    ->loadByCode(Magento\Catalog\Model\Product::ENTITY);
$data = [
    'attribute_set_name' => 'custom_attribute_set_wout_com',
    'entity_type_id' => $entityType->getId(),
    'sort_order' => 300,
];

$attributeSet->setData($data);
$attributeSet->validate();
$attributeSet->save();
$attributeSet->initFromSkeleton($entityType->getDefaultAttributeSetId());
/** @var Group $group */
foreach ($attributeSet->getGroups() as $group) {
    $groupAttributes = $group->getAttributes();
    $newAttributes = array_filter(
        $groupAttributes,
        function ($attribute) use ($attributeCountryOfManufacture) {
            /** @var ProductAttributeInterface $attribute */
            return (int)$attribute->getAttributeId() !== (int)$attributeCountryOfManufacture->getAttributeId();
        }
    );
    if (count($newAttributes) < count($groupAttributes)) {
        $group->setAttributes($newAttributes);
        break;
    }
}
$attributeSet->save();
