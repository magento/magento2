<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);

/** @var \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection $urlRewriteCollection */
$urlRewriteCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection::class);
$collection = $urlRewriteCollection
    ->addFieldToFilter('entity_type', 'custom')
    ->addFieldToFilter('request_path', ['page-a', 'page-b', 'page-c'])
    ->addFieldToFilter('entity_id', '333')
    ->load()
    ->walk('delete');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
