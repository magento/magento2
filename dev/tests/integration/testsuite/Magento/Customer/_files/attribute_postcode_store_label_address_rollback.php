<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$registry = $objectManager->get(Registry::class);
/** @var AttributeRepositoryInterface $attributeRepository */
$attributeRepository = $objectManager->get(AttributeRepositoryInterface::class);
/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$attribute = $attributeRepository->get(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, AddressInterface::POSTCODE);
$attribute->setStoreLabels([]);
$attributeRepository->save($attribute);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
