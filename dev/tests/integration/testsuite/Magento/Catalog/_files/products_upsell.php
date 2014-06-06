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

/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->setTypeId(
    \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
)->setId(
    1
)->setAttributeSetId(
    4
)->setName(
    'Simple Up Sell'
)->setSku(
    'simple'
)->setPrice(
    100
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
)->setWebsiteIds(
    array(1)
)->setStockData(
    array('qty' => 100, 'is_in_stock' => 1)
)->save();

$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->setTypeId(
    \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
)->setId(
    2
)->setAttributeSetId(
    4
)->setName(
    'Simple Product With Up Sell'
)->setSku(
    'simple_with_upsell'
)->setPrice(
    10
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
)->setWebsiteIds(
    array(1)
)->setStockData(
    array('qty' => 100, 'is_in_stock' => 1)
)->setUpSellLinkData(
    array(1 => array('position' => 1))
)->save();
