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
 * @category    Magento
 * @package     Mage_Checkout
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require __DIR__ . '/../../Checkout/_files/simple_product.php';

$bundleProduct = new Mage_Catalog_Model_Product();
$bundleProduct->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_BUNDLE)
    ->setId(3)
    ->setAttributeSetId(4)
    ->setWebsiteIds(array(1))
    ->setName('Bundle Product')
    ->setSku('bundle-product')
    ->setDescription('Description with <b>html tag</b>')
    ->setShortDescription('Bundle')
    ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
    ->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
    ->setStockData(array(
    'use_config_manage_stock'   => 0,
    'manage_stock'              => 0,
    'use_config_enable_qty_increments' => 1,
    'use_config_qty_increments' => 1,
    'is_in_stock' => 0
))
    ->setBundleOptionsData(array(
    array(
        'title'    => 'Bundle Product Items',
        'default_title' => 'Bundle Product Items',
        'type'     => 'select',
        'required' => 1,
        'delete'   => '',
        'position' => 0,
        'option_id' => '',
    ),
))
    ->setBundleSelectionsData(array(
    array(
        array(
            'product_id'               => 1, // fixture product
            'selection_qty'            => 1,
            'selection_can_change_qty' => 1,
            'delete'                   => '',
            'position'                 => 0,
            'selection_price_type'     => 0,
            'selection_price_value'    => 0.0,
            'option_id'                => '',
            'selection_id'             => '',
            'is_default' => 1
        ),
    ),
))
    ->setCanSaveBundleSelections(true)
    ->setAffectBundleProductSelections(true)
    ->save();

/** @var $product Mage_Catalog_Model_Product */
$product = Mage::getModel('Mage_Catalog_Model_Product');
$product->load($bundleProduct->getId());

/** @var $typeInstance Mage_Bundle_Model_Product_Type */
//Load options
$typeInstance = $product->getTypeInstance();
$typeInstance->setStoreFilter($product->getStoreId(), $product);
$optionCollection = $typeInstance->getOptionsCollection($product);
$selectionCollection = $typeInstance->getSelectionsCollection($typeInstance->getOptionsIds($product), $product);

$bundleOptions = array();
$bundleOptionsQty = array();
/** @var $option Mage_Bundle_Model_Option */
foreach ($optionCollection as $option) {
    /** @var $selection Mage_Bundle_Model_Selection */
    $selection = $selectionCollection->getFirstItem();
    $bundleOptions[$option->getId()] = $selection->getSelectionId();
    $bundleOptionsQty[$option->getId()] = 1;
}

$requestInfo = new Varien_Object(array(
    'qty' => 1,
    'bundle_option' => $bundleOptions,
    'bundle_option_qty' => $bundleOptionsQty
));
$product->setSkipCheckRequiredOption(true);

require __DIR__ . '/../../Checkout/_files/cart.php';
