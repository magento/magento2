<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

// phpcs:ignore Magento2.Security.IncludeFile
require __DIR__ . '/../../Catalog/_files/attribute_set_based_on_default_set.php';
// phpcs:ignore Magento2.Security.IncludeFile
require __DIR__ . '/../../Catalog/_files/categories.php';

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Eav\Api\AttributeRepositoryInterface;

$eavConfig = Bootstrap::getObjectManager()->get(\Magento\Eav\Model\Config::class);
$attribute = $eavConfig->getAttribute('catalog_product', 'test_configurable');

$eavConfig->clear();

$attribute1 = $eavConfig->getAttribute('catalog_product', ' second_test_configurable');
$eavConfig->clear();

/** @var $installer \Magento\Catalog\Setup\CategorySetup */
$installer = Bootstrap::getObjectManager()->create(\Magento\Catalog\Setup\CategorySetup::class);

if (!$attribute->getId()) {

    /** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
    $attribute = Bootstrap::getObjectManager()->create(
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class
    );

    /** @var AttributeRepositoryInterface $attributeRepository */
    $attributeRepository = Bootstrap::getObjectManager()->create(AttributeRepositoryInterface::class);

    $attribute->setData(
        [
            'attribute_code' => 'test_configurable',
            'entity_type_id' => $installer->getEntityTypeId('catalog_product'),
            'is_global' => 1,
            'is_user_defined' => 1,
            'frontend_input' => 'select',
            'is_unique' => 0,
            'is_required' => 0,
            'is_searchable' => 1,
            'is_visible_in_advanced_search' => 1,
            'is_comparable' => 1,
            'is_filterable' => 1,
            'is_filterable_in_search' => 1,
            'is_used_for_promo_rules' => 0,
            'is_html_allowed_on_front' => 1,
            'is_visible_on_front' => 1,
            'used_in_product_listing' => 1,
            'used_for_sort_by' => 1,
            'frontend_label' => ['Test Configurable'],
            'backend_type' => 'int',
            'option' => [
                'value' => ['option_0' => ['Option 1'], 'option_1' => ['Option 2']],
                'order' => ['option_0' => 1, 'option_1' => 2],
            ],
            'default_value' => 'option_0'
        ]
    );

    $attributeRepository->save($attribute);

    /* Assign attribute to attribute set */
    $installer->addAttributeToGroup('catalog_product', 'Default', 'General', $attribute->getId());
}
// create a second attribute
if (!$attribute1->getId()) {

    /** @var $attribute1 \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
    $attribute1 = Bootstrap::getObjectManager()->create(
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class
    );

    /** @var AttributeRepositoryInterface $attributeRepository */
    $attributeRepository = Bootstrap::getObjectManager()->create(AttributeRepositoryInterface::class);

    $attribute1->setData(
        [
            'attribute_code' => 'second_test_configurable',
            'entity_type_id' => $installer->getEntityTypeId('catalog_product'),
            'is_global' => 1,
            'is_user_defined' => 1,
            'frontend_input' => 'select',
            'is_unique' => 0,
            'is_required' => 0,
            'is_searchable' => 1,
            'is_visible_in_advanced_search' => 1,
            'is_comparable' => 1,
            'is_filterable' => 1,
            'is_filterable_in_search' => 1,
            'is_used_for_promo_rules' => 0,
            'is_html_allowed_on_front' => 1,
            'is_visible_on_front' => 1,
            'used_in_product_listing' => 1,
            'used_for_sort_by' => 1,
            'frontend_label' => ['Second Test Configurable'],
            'backend_type' => 'int',
            'option' => [
                'value' => ['option_0' => ['Option 3'], 'option_1' => ['Option 4']],
                'order' => ['option_0' => 1, 'option_1' => 2],
            ],
            'default' => ['option_0']
        ]
    );

    $attributeRepository->save($attribute1);

    /* Assign attribute to attribute set */
    $installer->addAttributeToGroup(
        'catalog_product',
        $attributeSet->getId(),
        $attributeSet->getDefaultGroupId(),
        $attribute1->getId()
    );
}

$eavConfig->clear();

/** @var \Magento\Framework\ObjectManagerInterface $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var  $productRepository \Magento\Catalog\Api\ProductRepositoryInterface */
$productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$productsWithNewAttributeSet = ['simple', '12345', 'simple-4'];

foreach ($productsWithNewAttributeSet as $sku) {
    try {
        $product = $productRepository->get($sku, false, null, true);
        $product->setAttributeSetId($attributeSet->getId());
        $product->setStockData(
            ['use_config_manage_stock' => 1,
                'qty' => 50,
                'is_qty_decimal' => 0,
                'is_in_stock' => 1]
        );
        $productRepository->save($product);
    } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {

    }
}
/** @var \Magento\Indexer\Model\Indexer\Collection $indexerCollection */
$indexerCollection = Bootstrap::getObjectManager()->get(\Magento\Indexer\Model\Indexer\Collection::class);
$indexerCollection->load();
/** @var \Magento\Indexer\Model\Indexer $indexer */
foreach ($indexerCollection->getItems() as $indexer) {
    $indexer->reindexAll();
}
