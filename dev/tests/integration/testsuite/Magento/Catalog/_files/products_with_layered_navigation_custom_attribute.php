<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Eav\Model\Config;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Indexer\Model\Indexer;
use Magento\Indexer\Model\Indexer\Collection as IndexerCollection;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\TestFramework\Eav\Model\GetAttributeSetByName;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/attribute_set_based_on_default_set.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/categories.php');

$objectManager = Bootstrap::getObjectManager();
/** @var GetAttributeSetByName $getAttributeSetByName */
$getAttributeSetByName = $objectManager->get(GetAttributeSetByName::class);
$attributeSet = $getAttributeSetByName->execute('second_attribute_set');
/** @var Config $eavConfig */
$eavConfig = $objectManager->get(Config::class);
/** @var AttributeRepositoryInterface $attributeRepository */
$attributeRepository = $objectManager->create(AttributeRepositoryInterface::class);
/** @var CategorySetup $installer */
$installer = $objectManager->create(CategorySetup::class);
$eavConfig->clear();

$attribute = $eavConfig->getAttribute('catalog_product', 'test_configurable');
if (!$attribute->getId()) {
    /** @var $attribute Attribute */
    $attribute->setData([
        'attribute_code' => 'test_configurable',
        'entity_type_id' => $installer->getEntityTypeId('catalog_product'),
        'is_global' => 1,
        'is_user_defined' => 1,
        'frontend_input' => 'select',
        'is_unique' => 0,
        'is_required' => 0,
        'is_searchable' => 0,
        'is_visible_in_advanced_search' => 0,
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
    ]);
    $attributeRepository->save($attribute);

    /* Assign attribute to attribute set */
    $installer->addAttributeToGroup('catalog_product', 'Default', 'General', $attribute->getId());
}

// create a second attribute
/** @var Attribute $secondAttribute */
$secondAttribute = $eavConfig->getAttribute('catalog_product', ' second_test_configurable');
if (!$secondAttribute->getId()) {
    $secondAttribute->setData([
        'attribute_code' => 'second_test_configurable',
        'entity_type_id' => $installer->getEntityTypeId('catalog_product'),
        'is_global' => 1,
        'is_user_defined' => 1,
        'frontend_input' => 'select',
        'is_unique' => 0,
        'is_required' => 0,
        'is_searchable' => 0,
        'is_visible_in_advanced_search' => 0,
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
    ]);
    $attributeRepository->save($secondAttribute);

    /* Assign attribute to attribute set */
    $installer->addAttributeToGroup(
        'catalog_product',
        $attributeSet->getId(),
        $attributeSet->getDefaultGroupId(),
        $secondAttribute->getId()
    );
}

$eavConfig->clear();

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
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
    } catch (NoSuchEntityException $e) {

    }
}

/** @var IndexerCollection $indexerCollection */
$indexerCollection = $objectManager->get(IndexerCollection::class)->load();
/** @var Indexer $indexer */
foreach ($indexerCollection->getItems() as $indexer) {
    $indexer->reindexAll();
}
