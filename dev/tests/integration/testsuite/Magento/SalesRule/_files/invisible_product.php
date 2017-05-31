<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

include '../../Checkout/_files/simple_product.php';
/** @var $product \Magento\Catalog\Model\Product */
$product->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE);
$product->save();
