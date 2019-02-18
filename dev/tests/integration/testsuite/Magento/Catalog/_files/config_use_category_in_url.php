<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Helper\Product;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var \Magento\Config\Model\Config $config */
$config = $objectManager->get(\Magento\Config\Model\Config::class);
$config->setDataByPath(Product::XML_PATH_PRODUCT_URL_USE_CATEGORY, 1);
$config->save();
