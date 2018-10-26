<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WishlistGraphQl\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;

class WishlistItemsProductDataProvider
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getProductDataById(int $productId) {
        $product = $this->productRepository->getById($productId);
        $productData = $product->toArray();
        $productData['model'] = $product;
        return $productData;
    }
}
