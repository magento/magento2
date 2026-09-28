<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
$product->load(1);
$product->setAssociatedProductIds([20]);
$product->save();
