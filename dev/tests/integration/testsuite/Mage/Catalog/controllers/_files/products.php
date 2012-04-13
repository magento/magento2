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
 * @package     Mage_Catalog
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$productOne = new Mage_Catalog_Model_Product();
$productOne->setId(1)
    ->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
    ->setAttributeSetId(4)
    ->setWebsiteIds(array(Mage::app()->getStore()->getWebsiteId()))

    ->setSku('simple_product_1')
    ->setName('Simple Product 1 Name')
    ->setDescription('Simple Product 1 Full Description')
    ->setShortDescription('Simple Product 1 Short Description')

    ->setPrice(1234.56)
    ->setTaxClassId(2)
    ->setStockData(array(
        'use_config_manage_stock'   => 1,
        'qty'                       => 99,
        'is_in_stock'               => 1,
    ))

    ->setMetaTitle('Simple Product 1 Meta Title')
    ->setMetaKeyword('Simple Product 1 Meta Keyword')
    ->setMetaDescription('Simple Product 1 Meta Description')

    ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
    ->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)

    ->addImageToMediaGallery(dirname(__FILE__) . '/product_image.png', null, false, false)

    ->save();

$productTwo = new Mage_Catalog_Model_Product();
$productTwo->setId(2)
    ->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
    ->setAttributeSetId(4)
    ->setWebsiteIds(array(Mage::app()->getStore()->getWebsiteId()))

    ->setSku('simple_product_2')
    ->setName('Simple Product 2 Name')
    ->setDescription('Simple Product 2 Full Description')
    ->setShortDescription('Simple Product 2 Short Description')

    ->setPrice(987.65)
    ->setTaxClassId(2)
    ->setStockData(array(
        'use_config_manage_stock'   => 1,
        'qty'                       => 24,
        'is_in_stock'               => 1,
    ))

    ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
    ->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)

    ->save();
