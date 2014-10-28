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

/** @var $category \Magento\Catalog\Model\Category */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$categoryFirst = $objectManager->create('Magento\Catalog\Model\Category');
$categoryFirst->setName(
    'Category 1'
)->setPath(
    '1/2'
)->setLevel(
    2
)->setAvailableSortBy(
    'name'
)->setDefaultSortBy(
    'name'
)->setIsActive(
    true
)->setPosition(
    1
)->save();

$categorySecond = $objectManager->create('Magento\Catalog\Model\Category');
$categorySecond->setName(
    'Category 2'
)->setPath(
    '1/2'
)->setLevel(
    2
)->setAvailableSortBy(
    'name'
)->setDefaultSortBy(
    'name'
)->setIsActive(
    true
)->setPosition(
    2
)->save();

$categoryThird = $objectManager->create('Magento\Catalog\Model\Category');
$categoryThird->setName(
    'Category 3'
)->setPath(
    $categoryFirst->getPath()
)->setLevel(
    3
)->setAvailableSortBy(
    'name'
)->setDefaultSortBy(
    'name'
)->setIsActive(
    true
)->setPosition(
    2
)->save();


$categoryFourth = $objectManager->create('Magento\Catalog\Model\Category');
$categoryFourth->setName(
    'Category 4'
)->setPath(
    $categoryThird->getPath()
)->setLevel(
    4
)->setAvailableSortBy(
    'name'
)->setDefaultSortBy(
    'name'
)->setIsActive(
    true
)->setPosition(
    1
)->save();

$categoryFifth = $objectManager->create('Magento\Catalog\Model\Category');
$categoryFifth->setName(
    'Category 5'
)->setPath(
    $categorySecond->getPath()
)->setLevel(
    3
)->setAvailableSortBy(
    'name'
)->setDefaultSortBy(
    'name'
)->setIsActive(
    true
)->setPosition(
    2
)->save();


/** @var $productFirst \Magento\Catalog\Model\Product */
$productFirst = $objectManager->create('Magento\Catalog\Model\Product');
$productFirst->setTypeId(
    'simple'
)->setAttributeSetId(
    4
)->setWebsiteIds(
    array(1)
)->setName(
    'Simple Product 01'
)->setSku(
    'simple 01'
)->setPrice(
    10
)->setMetaTitle(
    'meta title'
)->setMetaKeyword(
    'meta keyword'
)->setMetaDescription(
    'meta description'
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
)->setStockData(
    array('use_config_manage_stock' => 0)
)->save();

/** @var $productSecond \Magento\Catalog\Model\Product */
$productSecond = $objectManager->create('Magento\Catalog\Model\Product');
$productSecond->setTypeId(
    'simple'
)->setAttributeSetId(
    4
)->setWebsiteIds(
    array(1)
)->setName(
    'Simple Product 02'
)->setSku(
    'simple 02'
)->setPrice(
    10
)->setMetaTitle(
    'meta title'
)->setMetaKeyword(
    'meta keyword'
)->setMetaDescription(
    'meta description'
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
)->setStockData(
    array('use_config_manage_stock' => 0)
)->save();

/** @var $productThird \Magento\Catalog\Model\Product */
$productThird = $objectManager->create('Magento\Catalog\Model\Product');
$productThird->setTypeId(
    'simple'
)->setAttributeSetId(
    4
)->setWebsiteIds(
    array(1)
)->setName(
    'Simple Product 03'
)->setSku(
    'simple 02'
)->setPrice(
    10
)->setMetaTitle(
    'meta title'
)->setMetaKeyword(
    'meta keyword'
)->setMetaDescription(
    'meta description'
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
)->setStockData(
    array('use_config_manage_stock' => 0)
)->save();
