<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* Create attribute */
/** @var $installer \Magento\Catalog\Model\Resource\Setup */
$installer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Catalog\Model\Resource\Setup',
    ['resourceName' => 'catalog_setup']
);
/** @var $attribute \Magento\Catalog\Model\Resource\Eav\Attribute */
$attribute = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Catalog\Model\Resource\Eav\Attribute'
);
$attribute->setData(
    [
        'attribute_code' => 'attribute_with_option',
        'entity_type_id' => $installer->getEntityTypeId('catalog_product'),
        'is_global' => 1,
        'frontend_input' => 'select',
        'is_filterable' => 1,
        'option' => ['value' => ['option_0' => [0 => 'Option Label']]],
        'backend_type' => 'int',
    ]
);
$attribute->save();

/* Assign attribute to attribute set */
$installer->addAttributeToGroup('catalog_product', 'Default', 'General', $attribute->getId());

/* Create simple products per each option */
/** @var $options \Magento\Eav\Model\Resource\Entity\Attribute\Option\Collection */
$options = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Eav\Model\Resource\Entity\Attribute\Option\Collection'
);
$options->setAttributeFilter($attribute->getId());

foreach ($options as $option) {
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
        10
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
        [$attribute->getAttributeCode() => $option->getId()],
        $product->getStoreId()
    );
}
