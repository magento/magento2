<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* Create attribute */
/** @var $installer \Magento\Catalog\Setup\CategorySetup */
$installer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Catalog\Setup\CategorySetup',
    ['resourceName' => 'catalog_setup']
);
/** @var $selectAttribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
$selectAttribute = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Catalog\Model\ResourceModel\Eav\Attribute'
);
$selectAttribute->setData(
    [
        'attribute_code' => 'select_attribute',
        'entity_type_id' => $installer->getEntityTypeId('catalog_product'),
        'is_global' => 1,
        'frontend_input' => 'select',
        'is_filterable' => 1,
        'option' => [
            'value' => ['option_0' => ['Option 1'], 'option_1' => ['Option 2']],
            'order' => ['option_0' => 1, 'option_1' => 2],
        ],
        'backend_type' => 'int',
    ]
);
$selectAttribute->save();
/* Assign attribute to attribute set */
$installer->addAttributeToGroup('catalog_product', 'Default', 'General', $selectAttribute->getId());

/** @var $selectOptions \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection */
$selectOptions = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection'
);
$selectOptions->setAttributeFilter($selectAttribute->getId());

$multiselectAttribute = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Catalog\Model\ResourceModel\Eav\Attribute'
);
$multiselectAttribute->setData(
    [
        'attribute_code' => 'multiselect_attribute',
        'entity_type_id' => $installer->getEntityTypeId('catalog_product'),
        'is_global' => 1,
        'frontend_input' => 'multiselect',
        'is_filterable' => 1,
        'option' => [
            'value' => ['option_0' => ['Option 1'], 'option_1' => ['Option 2']],
            'order' => ['option_0' => 1, 'option_1' => 2],
        ],
        'backend_type' => 'varchar',
    ]
);
$multiselectAttribute->save();
/* Assign attribute to attribute set */
$installer->addAttributeToGroup('catalog_product', 'Default', 'General', $multiselectAttribute->getId());

/** @var $multiselectOptions \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection */
$multiselectOptions = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection'
);
$multiselectOptions->setAttributeFilter($multiselectAttribute->getId());


/* Create simple products per each select(dropdown) option */
foreach ($selectOptions as $option) {
    /** @var $product \Magento\Catalog\Model\Product */
    $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
    $product->setTypeId(
        \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
    )->setAttributeSetId(
        $installer->getAttributeSetId('catalog_product', 'Default')
    )->setWebsiteIds(
        [1]
    )->setName(
        'Simple Product ' . $option->getId()
    )->setSku(
        'simple_product_' . $option->getId()
    )->setPrice(
        99
    )->setCategoryIds(
        [2]
    )->setVisibility(
        \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
    )->setStatus(
        \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
    )->setStockData(
        ['use_config_manage_stock' => 1, 'qty' => 5, 'is_in_stock' => 1]
    )->save();

    \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
        'Magento\Catalog\Model\Product\Action'
    )->updateAttributes(
        [$product->getId()],
        [
            $selectAttribute->getAttributeCode() => $option->getId(),
            $multiselectAttribute->getAttributeCode() => $multiselectOptions->getLastItem()->getId()
        ],
        $product->getStoreId()
    );
}
