<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var AttributeRepositoryInterface $attributeRepository */
$attributeRepository = $objectManager->get(AttributeRepositoryInterface::class);
/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);
$attribute = $attributeRepository->get(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, AddressInterface::POSTCODE);
$storeLabels = $attribute->getStoreLabels();
$stores = $storeManager->getStores();
foreach ($stores as $store) {
    $storeLabels[$store->getId()] = $store->getCode() . ' store postcode label';
}
$attribute->setStoreLabels($storeLabels);
$attributeRepository->save($attribute);
