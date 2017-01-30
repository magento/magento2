<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

include 'simple_product.php';

/** @var $product \Magento\Catalog\Model\Product */
$product->setStockData(['use_config_manage_stock' => 0, 'min_sale_qty' => 3]);
$product->save();
