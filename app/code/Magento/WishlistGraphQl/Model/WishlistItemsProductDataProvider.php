<?php
declare(strict_types=1);
/**
 * WishlistItemsProductsResolver
 *
 * @copyright Copyright © 2018 brandung GmbH & Co. KG. All rights reserved.
 * @author    david.verholen@brandung.de
 */

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
