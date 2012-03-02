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

$installer = new Mage_Catalog_Model_Resource_Setup('catalog_setup');
/**
 * After installation system has two categories: root one with ID:1 and Default category with ID:2
 */
$category = new Mage_Catalog_Model_Category();

$category->setId(3)
    ->setName('Category 1')
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
        ->setName('Category 1.1')
        ->setParentId(3) /**/
        ->setPath('1/2/3/4')
        ->setLevel(3)
        ->setAvailableSortBy('name')
        ->setDefaultSortBy('name')
        ->setIsActive(true)
        ->setIsAnchor(true)
        ->setPosition(1)
        ->save();

        $category = new Mage_Catalog_Model_Category();
        $category->setId(5)
            ->setName('Category 1.1.1')
            ->setParentId(4) /**/
            ->setPath('1/2/3/4/5')
            ->setLevel(4)
            ->setAvailableSortBy('name')
            ->setDefaultSortBy('name')
            ->setIsActive(true)
            ->setPosition(2)
            ->setCustomUseParentSettings(0)
            ->setCustomDesign('default/default/blue')
            ->save();

$category = new Mage_Catalog_Model_Category();
$category->setId(6)
    ->setName('Category 2')
    ->setParentId(2) /**/
    ->setPath('1/2/6')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(2)
    ->save();

$category = new Mage_Catalog_Model_Category();
$category->setId(7)
    ->setName('Movable')
    ->setParentId(2) /**/
    ->setPath('1/2/7')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(3)
    ->save();

$category = new Mage_Catalog_Model_Category();
$category->setId(8)
    ->setName('Inactive')
    ->setParentId(2) /**/
    ->setPath('1/2/8')
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(false)
    ->setPosition(4)
    ->save();


$product = new Mage_Catalog_Model_Product();
$product->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
    ->setId(1)
    ->setAttributeSetId($installer->getAttributeSetId('catalog_product', 'Default'))
    ->setStoreId(1)
    ->setWebsiteIds(array(1))
    ->setName('Simple Product')
    ->setSku('simple')
    ->setPrice(10)
    ->setWeight(18)
    ->setCategoryIds(array(2,3,4))
    ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
    ->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
    ->save();

$product = new Mage_Catalog_Model_Product();
$product->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
    ->setId(2)
    ->setAttributeSetId($installer->getAttributeSetId('catalog_product', 'Default'))
    ->setStoreId(1)
    ->setWebsiteIds(array(1))
    ->setName('Simple Product Two')
    ->setSku('simple_product_two')
    ->setPrice(45.67)
    ->setWeight(56)
    ->setCategoryIds(array(5))
    ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
    ->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
    ->save();
