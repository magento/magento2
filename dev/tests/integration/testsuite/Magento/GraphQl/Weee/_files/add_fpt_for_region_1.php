<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\QuoteFactory;
use Magento\TestFramework\Helper\Bootstrap;

/** @var ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
/** @var $product Product */
$product = $productRepository->get('simple-with-ftp', true);
if ($product && $product->getId()) {
    $product->setFixedProductAttribute(
        array_merge(
            $product->getFixedProductAttribute() ?? [],
            [
                [
                    'website_id' => 0,
                    'country' => 'US',
                    'state' => 1,
                    'price' => 5.00,
                    'delete' => ''
                ]
            ]
        )
    );
    $productRepository->save($product);
}
