<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterfaceFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CacheCleaner;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\TestFramework\Eav\Model\GetAttributeSetByName;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/attribute_set_based_on_default_set.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/categories.php');

$objectManager = Bootstrap::getObjectManager();

/** @var Config $eavConfig */
$eavConfig = $objectManager->get(Config::class);
/** @var ProductAttributeRepositoryInterface $attributeRepository */
$attributeRepository = $objectManager->get(ProductAttributeRepositoryInterface::class);
/** @var ProductAttributeInterfaceFactory $attributeFactory */
$attributeFactory = $objectManager->get(ProductAttributeInterfaceFactory::class);

/** @var GetAttributeSetByName $getAttributeSetByName */
$getAttributeSetByName = $objectManager->get(GetAttributeSetByName::class);
$secondAttributeSet = $getAttributeSetByName->execute('second_attribute_set');

/** @var $installer EavSetup */
$installer = $objectManager->get(EavSetup::class);
$defaultAttributeSetId = $installer->getAttributeSetId(Product::ENTITY, 'Default');
$defaultGroupId = $installer->getDefaultAttributeGroupId(Product::ENTITY, $defaultAttributeSetId);

$attributeModel = $attributeFactory->create();
$attributeModel->setData(
    [
        'attribute_code' => 'test_configurable',
        'entity_type_id' => $installer->getEntityTypeId(Product::ENTITY),
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
        'default' => ['option_0']
    ]
);
$attribute = $attributeRepository->save($attributeModel);
$installer->addAttributeToGroup(
    Product::ENTITY,
    $defaultAttributeSetId,
    $defaultGroupId,
    $attribute->getId()
);
$eavConfig->clear();

/** @var ProductAttributeInterface $attribute */
$attributeModel2 = $attributeFactory->create();
$attributeModel2->setData(
    [
        'attribute_code' => 'second_test_configurable',
        'entity_type_id' => $installer->getEntityTypeId(Product::ENTITY),
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
        'position' => 1,
        'frontend_label' => ['Second Test Configurable'],
        'backend_type' => 'int',
        'option' => [
            'value' => ['option_0' => ['Option 3'], 'option_1' => ['Option 4']],
            'order' => ['option_0' => 1, 'option_1' => 2],
        ],
        'default' => ['option_0'],
    ]
);
$attribute2 = $attributeRepository->save($attributeModel2);
$installer->addAttributeToGroup(
    Product::ENTITY,
    $secondAttributeSet->getId(),
    $secondAttributeSet->getDefaultGroupId(),
    $attribute2->getId()
);

/** @var  $productRepository \Magento\Catalog\Api\ProductRepositoryInterface */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productsWithNewAttributeSet = ['simple', '12345', 'simple-4'];

foreach ($productsWithNewAttributeSet as $sku) {
    try {
        $product = $productRepository->get($sku, false, null, true);
        $product->setAttributeSetId($secondAttributeSet->getId());
        $product->setStockData(
            [
                'use_config_manage_stock' => 1,
                'qty' => 50,
                'is_qty_decimal' => 0,
                'is_in_stock' => 1,
            ]
        );
        $productRepository->save($product);
    } catch (NoSuchEntityException $e) {

    }
}
CacheCleaner::cleanAll();
