<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require __DIR__ . '/configurable_attribute.php';

/** @var $installer \Magento\Catalog\Model\Resource\Setup */
$installer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Catalog\Model\Resource\Setup',
    array('resourceName' => 'catalog_setup')
);

/* Create simple products per each option */
/** @var $options \Magento\Eav\Model\Resource\Entity\Attribute\Option\Collection */
$options = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Eav\Model\Resource\Entity\Attribute\Option\Collection'
);
$options->setAttributeFilter($attribute->getId());

$attributeValues = array();
$productIds = array();
$attributeSetId = $installer->getAttributeSetId('catalog_product', 'Default');
$productIds = array(10, 20);
foreach ($options as $option) {
    /** @var $product \Magento\Catalog\Model\Product */
    $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
    $productId = array_shift($productIds);
    $product->setTypeId(
        \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
    )->setId(
        $productId
    )->setAttributeSetId(
        $attributeSetId
    )->setWebsiteIds(
        array(1)
    )->setName(
        'Configurable Option' . $option->getId()
    )->setSku(
        'simple_' . $productId
    )->setPrice(
        10
    )->setTestConfigurable(
        $option->getId()
    )->setVisibility(
        \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE
    )->setStatus(
        \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
    )->setStockData(
        array('use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1)
    )->save();

    $attributeValues[] = array(
        'label' => 'test',
        'attribute_id' => $attribute->getId(),
        'value_index' => $option->getId(),
        'is_percent' => false,
        'pricing_value' => 5
    );
    $productIds[] = $product->getId();
}

/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->setTypeId(
    \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
)->setId(
    1
)->setAttributeSetId(
    $attributeSetId
)->setWebsiteIds(
    array(1)
)->setName(
    'Configurable Product'
)->setSku(
    'configurable'
)->setPrice(
    100
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
)->setStockData(
    array('use_config_manage_stock' => 1, 'is_in_stock' => 1)
)->setAssociatedProductIds(
    $productIds
)->setConfigurableAttributesData(
    array(
        array(
            'attribute_id' => $attribute->getId(),
            'attribute_code' => $attribute->getAttributeCode(),
            'frontend_label' => 'test',
            'values' => $attributeValues
        )
    )
)->save();
