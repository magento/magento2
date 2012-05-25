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

$product = new Mage_Catalog_Model_Product();
$product->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
    ->setId(1)
    ->setAttributeSetId(4)
    ->setWebsiteIds(array(1))
    ->setName('Simple Product')
    ->setSku('simple')
    ->setPrice(10)
    ->setDescription('Description with <b>html tag</b>')

    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')

    ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
    ->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)

    ->setCategoryIds(array(2))

    ->setStockData(
        array(
            'use_config_manage_stock'   => 1,
            'qty'                       => 100,
            'is_qty_decimal'            => 0,
            'is_in_stock'               => 1,
        )
    )
    ->setCanSaveCustomOptions(true)
    ->setHasOptions(true)
    ->save();

/** @var $product Mage_Catalog_Model_Product */
$product = Mage::getModel('Mage_Catalog_Model_Product');
$product->load(1);

/** @var $cart Mage_Checkout_Model_Cart */
$cart = Mage::getSingleton('Mage_Checkout_Model_Cart');
$requestInfo = new Varien_Object(array(
    'qty' => 1
));

$cart->addProduct($product, $requestInfo);
$cart->save();
$cart = Mage::getSingleton('Mage_Checkout_Model_Cart');

$quoteItemId = $cart->getQuote()->getItemByProduct($product)->getId();
Mage::register('product/quoteItemId', $quoteItemId);
Mage::unregister('_singleton/Mage_Checkout_Model_Session');
Mage::unregister('_singleton/Mage_Checkout_Model_Cart');
