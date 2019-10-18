<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Eav\Model\Entity\Type;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var AttributeSetRepositoryInterface $attributeSetRepository */
$attributeSetRepository = $objectManager->get(AttributeSetRepositoryInterface::class);
/** @var Type $entityType */
$entityType = $objectManager->create(Type::class)->loadByCode(ProductAttributeInterface::ENTITY_TYPE_CODE);
/** @var Collection $attributeSetCollection */
$attributeSetCollection = $objectManager->create(CollectionFactory::class)->create();
$attributeSetCollection->addFilter('attribute_set_name', 'new_attribute_set');
$attributeSetCollection->addFilter('entity_type_id', $entityType->getId());
$attributeSetCollection->setOrder('attribute_set_id');
$attributeSetCollection->setPageSize(1);
$attributeSetCollection->load();
/** @var AttributeSetInterface $attributeSet */
$attributeSet = $attributeSetCollection->fetchItem();

if ($attributeSet) {
    $attributeSetRepository->delete($attributeSet);
}
