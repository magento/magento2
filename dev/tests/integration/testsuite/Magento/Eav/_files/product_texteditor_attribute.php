<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Model\Product\Attribute\Frontend\Inputtype\Presentation;

$objectManager = Bootstrap::getObjectManager();
/** @var AttributeFactory $attributeFactory */
$attributeFactory = $objectManager->get(AttributeFactory::class);
/** @var ProductAttributeRepositoryInterface $productAttributeRepository */
$productAttributeRepository = $objectManager->get(ProductAttributeRepositoryInterface::class);
$attribute = $attributeFactory->create()->loadByCode(Product::ENTITY, 'text_editor_attribute');
if (!$attribute->getId()) {
    /** @var Presentation $presentation */
    $presentation = $objectManager->get(Presentation::class);
    /** @var EavSetup $installer */
    $installer = $objectManager->create(EavSetup::class);
    $attributeData = [
        'attribute_code' => 'text_editor_attribute',
        'is_global' => 1,
        'is_user_defined' => 1,
        'frontend_input' => 'texteditor',
        'is_unique' => 0,
        'is_required' => 0,
        'is_searchable' => 0,
        'is_visible_in_advanced_search' => 0,
        'is_comparable' => 0,
        'is_filterable' => 0,
        'is_filterable_in_search' => 0,
        'is_used_for_promo_rules' => 0,
        'is_html_allowed_on_front' => 1,
        'is_visible_on_front' => 0,
        'used_in_product_listing' => 0,
        'used_for_sort_by' => 0,
        'frontend_label' => ['Text editor attribute'],
        'backend_type' => 'text',
    ];
    $attribute->setData($presentation->convertPresentationDataToInputType($attributeData));
    $productAttributeRepository->save($attribute);
    $attribute = $productAttributeRepository->get('text_editor_attribute');
    /* Assign attribute to attribute set */
    $installer->addAttributeToGroup(Product::ENTITY, 'Default', 'Attributes', $attribute->getId());
}
