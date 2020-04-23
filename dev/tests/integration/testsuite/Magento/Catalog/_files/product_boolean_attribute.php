<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Setup\CategorySetup;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var AttributeRepositoryInterface $attributeRepository */
$attributeRepository = $objectManager->get(AttributeRepositoryInterface::class);
/** @var Attribute $attribute */
$attribute = $objectManager->create(Attribute::class);
/** @var $installer CategorySetup */
$installer = $objectManager->create(CategorySetup::class);

$attribute->setData(
    [
        'attribute_code'                => 'boolean_attribute',
        'entity_type_id'                => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
        'is_global'                     => 0,
        'is_user_defined'               => 1,
        'frontend_input'                => 'boolean',
        'is_unique'                     => 0,
        'is_required'                   => 0,
        'is_searchable'                 => 1,
        'is_visible_in_advanced_search' => 1,
        'is_comparable'                 => 0,
        'is_filterable'                 => 1,
        'is_filterable_in_search'       => 1,
        'is_used_for_promo_rules'       => 0,
        'is_html_allowed_on_front'      => 1,
        'is_visible_on_front'           => 1,
        'used_in_product_listing'       => 1,
        'used_for_sort_by'              => 0,
        'frontend_label'                => ['Boolean Attribute'],
        'backend_type'                  => 'int',
        'source_model'                  => Boolean::class
    ]
);

$attributeRepository->save($attribute);

/* Assign attribute to attribute set */
$installer->addAttributeToGroup('catalog_product', 'Default', 'Attributes', $attribute->getId());
