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
require __DIR__ . '/../../Core/_files/store.php';
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var Magento\Store\Model\Store $store */
$store = $objectManager->create('Magento\Store\Model\Store');
$store->load('fixturestore', 'code');

/** @var $product \Magento\Catalog\Model\Product */
$product = $objectManager->create('Magento\Catalog\Model\Product');
//$product->isObjectNew(true);
$product->setTypeId(
    \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
)->setId(
    1
)->setAttributeSetId(
    4
)->setStoreId(
    1
)->setWebsiteIds(
    array(1)
)->setName(
    'Simple Product One'
)->setSku(
    'simple'
)->setPrice(
    10
)->setWeight(
    18
)->setStockData(
    array('use_config_manage_stock' => 0)
)->setCategoryIds(
    array(9)
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
)->save();

$product = $objectManager->create('Magento\Catalog\Model\Product');
$product->setStoreId(1)
    ->load(1)
    ->setStoreId($store->getId())
    ->setName('StoreTitle')
    ->save();

