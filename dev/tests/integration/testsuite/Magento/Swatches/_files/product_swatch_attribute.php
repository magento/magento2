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
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Swatches/_files/swatch_attribute.php');

$objectManager = Bootstrap::getObjectManager();
$installer = $objectManager->create(CategorySetup::class);
$attributeRepository = $objectManager->create(ProductAttributeRepositoryInterface::class);
$entityType = $installer->getEntityTypeId(ProductAttributeInterface::ENTITY_TYPE_CODE);
$attribute = $objectManager->create(AttributeFactory::class)->create()
    ->loadByCode($entityType, 'color_swatch');

if ($attribute->getAttributeId()) {
    /* Assign attribute to attribute set */
    $installer->addAttributeToGroup(
        ProductAttributeInterface::ENTITY_TYPE_CODE,
        'Default',
        'General',
        $attribute->getAttributeId()
    );
}
