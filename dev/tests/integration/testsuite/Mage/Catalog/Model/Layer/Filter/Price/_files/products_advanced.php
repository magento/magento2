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

/**
 * Products generation to test base data
 */

$prices = array(5, 10, 15, 20, 50, 100, 150);

$installer = new Mage_Catalog_Model_Resource_Setup('catalog_setup');
/**
 * After installation system has two categories: root one with ID:1 and Default category with ID:2
 */
$category = new Mage_Catalog_Model_Category();

$category->setId(3)
    ->setName('Root Category')
    ->setParentId(2) /**/
    ->setPath('1/2/3')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1)
    ->save();

$category = new Mage_Catalog_Model_Category();
$category->setId(4)
    ->setName('PLN Category')
    ->setParentId(3)
    ->setPath('1/2/3/4')
    ->setLevel(3)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setIsAnchor(true)
    ->setPosition(1)
    ->save();

$lastProductId = 0;
foreach ($prices as $price) {
    $product = new Mage_Catalog_Model_Product();
    $productId = $lastProductId + 1;
    $product->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
        ->setId($productId)
        ->setAttributeSetId($installer->getAttributeSetId('catalog_product', 'Default'))
        ->setStoreId(1)
        ->setWebsiteIds(array(1))
        ->setName('Simple Product ' . $productId)
        ->setSku('simple-' . $productId)
        ->setPrice($price)
        ->setWeight(18)
        ->setCategoryIds(array(4))
        ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
        ->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
        ->save();
    ++$lastProductId;
}
