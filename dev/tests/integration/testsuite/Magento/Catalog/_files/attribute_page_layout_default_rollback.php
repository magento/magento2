<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Catalog\Setup\CategorySetup;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$installer = $objectManager->create(CategorySetup::class);
$attribute = $objectManager->create(AttributeFactory::class)->create();
$attributeRepository = $objectManager->create(ProductAttributeRepositoryInterface::class);
$entityType = $installer->getEntityTypeId(ProductAttributeInterface::ENTITY_TYPE_CODE);
$attribute->loadByCode($entityType, 'page_layout');
$attribute->setData('default_value', null);
$attributeRepository->save($attribute);
