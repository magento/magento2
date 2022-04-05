<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CacheCleaner;

$objectManager = Bootstrap::getObjectManager();

$attributeRepository = $objectManager->get(ProductAttributeRepositoryInterface::class);
try {
    $attribute = $attributeRepository->get('test_configurable');
} catch (NoSuchEntityException $e) {
    $installer = $objectManager->get(EavSetup::class);

    $attributeModel = $objectManager->create(ProductAttributeInterface::class);
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

    $attributeSetId = $installer->getAttributeSetId(Product::ENTITY, 'Default');
    $groupId = $installer->getDefaultAttributeGroupId(Product::ENTITY, $attributeSetId);
    $installer->addAttributeToGroup(Product::ENTITY, $attributeSetId, $groupId, $attribute->getId());

    CacheCleaner::cleanAll();
    $eavConfig = $objectManager->get(Config::class);
    $eavConfig->clear();
}
