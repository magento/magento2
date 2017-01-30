<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Extends valid Url rewrites
 */
require __DIR__ . '/url_rewrites.php';

/**
 * Invalid rewrite for product assigned to different category
 */
/** @var $rewrite \Magento\UrlRewrite\Model\UrlRewrite */
$rewrite = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\UrlRewrite\Model\UrlRewrite');
$rewrite->setStoreId(
    1
)->setIdPath(
    'product/1/4'
)->setRequestPath(
    'category-2/simple-product.html'
)->setTargetPath(
    'catalog/product/view/id/1'
)->setIsSystem(
    1
)->setCategoryId(
    4
)->setProductId(
    1
)->save();

/**
 * Invalid rewrite for product assigned to category that doesn't belong to store
 */
$rewrite = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\UrlRewrite\Model\UrlRewrite');
$rewrite->setStoreId(
    1
)->setIdPath(
    'product/1/5'
)->setRequestPath(
    'category-5/simple-product.html'
)->setTargetPath(
    'catalog/product/view/id/1'
)->setIsSystem(
    1
)->setCategoryId(
    5
)->setProductId(
    1
)->save();
