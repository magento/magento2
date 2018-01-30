<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* Create attribute */

/** @var \Magento\Framework\ObjectManagerInterface $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var $installer \Magento\Catalog\Setup\CategorySetup */
$installer = $objectManager->create(
    'Magento\Catalog\Setup\CategorySetup',
    ['resourceName' => 'catalog_setup']
);

/** @var $selectAttribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
$dateAttribute = $objectManager->create(
    'Magento\Catalog\Model\ResourceModel\Eav\Attribute'
);
$dateAttribute->setData(
    [
        'attribute_code' => 'date_attribute',
        'entity_type_id' => $installer->getEntityTypeId('catalog_product'),
        'is_global' => 1,
        'is_filterable' => 1,
        'backend_type' => 'datetime',
        'frontend_input' => 'date',
        'frontend_label' => 'Test Date',
    ]
);
$dateAttribute->save();
/* Assign attribute to attribute set */
$installer->addAttributeToGroup('catalog_product', 'Default', 'General', $dateAttribute->getId());

/** @var $product \Magento\Catalog\Model\Product */
$product = $objectManager->create('Magento\Catalog\Model\Product');
$product
    ->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId($installer->getAttributeSetId('catalog_product', 'Default'))
    ->setWebsiteIds([1])
    ->setName('Simple Product with date attribute')
    ->setSku('simple_product_with_date_attribute')
    ->setPrice(1)
    ->setCategoryIds([2])
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 5, 'is_in_stock' => 1])
    ->save();

$objectManager->get('Magento\Catalog\Model\Product\Action')
    ->updateAttributes(
        [$product->getId()],
        [
            $dateAttribute->getAttributeCode() => '01/01/2000' // m/d/Y
        ],
        $product->getStoreId()
    );
