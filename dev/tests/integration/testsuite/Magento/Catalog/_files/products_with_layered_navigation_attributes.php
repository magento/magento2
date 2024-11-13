<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\CategoryInterfaceFactory;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetup;
use Magento\Indexer\Model\Indexer;
use Magento\Indexer\Model\Indexer\Collection;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CacheCleaner;

$objectManager = Bootstrap::getObjectManager();

/** @var Config $eavConfig */
$eavConfig = $objectManager->get(Config::class);
/** @var ProductAttributeRepositoryInterface $attributeRepository */
$attributeRepository = $objectManager->get(ProductAttributeRepositoryInterface::class);
/** @var ProductAttributeInterfaceFactory $attributeFactory */
$attributeFactory = $objectManager->get(ProductAttributeInterfaceFactory::class);

/** @var $installer EavSetup */
$installer = $objectManager->get(EavSetup::class);
$attributeSetId = $installer->getAttributeSetId(Product::ENTITY, 'Default');
$groupId = $installer->getDefaultAttributeGroupId(Product::ENTITY, $attributeSetId);

/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$baseWebsite = $websiteRepository->get('base');

$attributes = [];
for ($i = 1; $i <= 2; $i++) {
    $attributeCode = 'test_attribute_' . $i;
    $attributeModel = $attributeFactory->create();
    $attributeModel->setData(
        [
            'attribute_code' => $attributeCode,
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
            'frontend_label' => ['Test Attribute ' . $i],
            'backend_type' => 'int',
            'option' => [
                'value' => ['option_1' => ['Option 1'], 'option_2' => ['Option 2'], 'option_3' => ['Option 3']],
                'order' => ['option_1' => 1, 'option_2' => 2, 'option_3' => 3],
            ],
            'position' => 3 - $i
        ]
    );
    $attribute = $attributeRepository->save($attributeModel);
    $installer->addAttributeToGroup(Product::ENTITY, $attributeSetId, $groupId, $attribute->getId());
    $attributes[$attributeCode] = $attribute;
}

/** @var ProductAttributeInterface $attribute1 */
$attribute1 = $attributes['test_attribute_1'];
/** @var ProductAttributeInterface $attribute2 */
$attribute2 = $attributes['test_attribute_2'];

CacheCleaner::cleanAll();
$eavConfig->clear();

/** @var CategoryInterfaceFactory $categoryInterfaceFactory */
$categoryInterfaceFactory = $objectManager->get(CategoryInterfaceFactory::class);

/** @var Magento\Catalog\Model\Category $category */
$category = $categoryInterfaceFactory->create();
$category->isObjectNew(true);
$category->setId(3334)
    ->setName('Category 1')
    ->setParentId(2)
    ->setPath('1/2/3334')
    ->setLevel(2)
    ->setAvailableSortBy(['position', 'name'])
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1);
$category->save();

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
/** @var ProductInterfaceFactory $productInterfaceFactory */
$productInterfaceFactory = $objectManager->get(ProductInterfaceFactory::class);
$products = [];
for ($i = 1; $i <= 6; $i++) {
    $sku = 'simple' . $i;
    /** @var Product $product */
    $product = $productInterfaceFactory->create();
    $product->setTypeId(Type::TYPE_SIMPLE)
        ->setAttributeSetId($product->getDefaultAttributeSetId())
        ->setName('Simple Product ' . $i)
        ->setSku($sku)
        ->setUrlKey('simple-product-' . $i)
        ->setTaxClassId('none')
        ->setDescription('description')
        ->setShortDescription('short description')
        ->setPrice($i * 10)
        ->setWeight(1)
        ->setMetaTitle('meta title')
        ->setMetaKeyword('meta keyword')
        ->setMetaDescription('meta description')
        ->setVisibility(Visibility::VISIBILITY_BOTH)
        ->setStatus(Status::STATUS_ENABLED)
        ->setWebsiteIds([$baseWebsite->getId()])
        ->setCategoryIds([$category->getId()])
        ->setStockData(['use_config_manage_stock' => 1, 'qty' => 50, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);
    $product->setData($attribute1->getAttributeCode(), getAttributeOptionValue($attribute1, 'Option 1'));
    $optionForSecondAttribute = 'Option ' . ($i === 1 ? 1 : ($i <= 3 ? 2 : 3));
    $product->setData($attribute2->getAttributeCode(), getAttributeOptionValue($attribute2, $optionForSecondAttribute));

    $products[$sku] = $productRepository->save($product);
}

/** @var Collection $indexerCollection */
$indexerCollection = $objectManager->get(Collection::class);
$indexerCollection->load();
/** @var Indexer $indexer */
foreach ($indexerCollection->getItems() as $indexer) {
    $indexer->reindexAll();
}

/**
 * @param ProductAttributeInterface $attribute
 * @param string $label
 * @return int|null
 */
function getAttributeOptionValue(ProductAttributeInterface $attribute, string $label): ?int
{
    foreach ($attribute->getOptions() as $option) {
        if ($option->getLabel() === $label) {
            return (int)$option->getValue();
        }
    }
    return null;
}
