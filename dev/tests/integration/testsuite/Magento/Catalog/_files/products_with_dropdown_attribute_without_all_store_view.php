<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;

$objectManager = Bootstrap::getObjectManager();
$storeManager = $objectManager->get(StoreManagerInterface::class);
/** @var StoreInterface $store */
$store = $storeManager->getStore();
$eavConfig = $objectManager->get(EavConfig::class);
$eavConfig->clear();
$attribute = $eavConfig->getAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, 'dropdown_without_default');
/** @var CategorySetup $installer */
$installer = $objectManager->get(CategorySetup::class);
$attributeSetId = $installer->getAttributeSetId(ProductAttributeInterface::ENTITY_TYPE_CODE, 'Default');

/** @var ProductInterface $product */
$product = $objectManager->get(ProductInterface::class);
$product->setTypeId(ProductType::TYPE_SIMPLE)
    ->setAttributeSetId($attributeSetId)
    ->setName('Simple Product1')
    ->setSku('test_attribute_dropdown_without_default')
    ->setPrice(10)
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$product = $productRepository->save($product);

if (!$attribute->getId()) {
    /** @var $attribute */
    $attribute = $objectManager->get(EavAttribute::class);
    /** @var AttributeRepositoryInterface $attributeRepository */
    $attributeRepository = $objectManager->get(AttributeRepositoryInterface::class);
    $attribute->setData(
        [
            'attribute_code' => 'dropdown_without_default',
            'entity_type_id' => $installer->getEntityTypeId(ProductAttributeInterface::ENTITY_TYPE_CODE),
            'is_global' => 0,
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
        ]
    );
    $attributeRepository->save($attribute);
    /* Assign attribute to attribute set */
    $installer->addAttributeToGroup(
        ProductAttributeInterface::ENTITY_TYPE_CODE,
        'Default',
        'General',
        $attribute->getId()
    );
}
/** @var AttributeOptionManagementInterface $options */
$attributeOption = $objectManager->get(AttributeOptionManagementInterface::class);
/* Getting the first nonempty option */
/** @var AttributeOptionInterface $option */
$option = $attributeOption->getItems($attribute->getEntityTypeId(), $attribute->getAttributeCode())[1];
$product->setStoreId($store->getId())
    ->setData('dropdown_without_default', $option->getValue());
$productRepository->save($product);
