<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Plugin\Model\ResourceModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Wishlist\Model\WishlistCleaner;

/**
 * Cleans up wishlist items referencing the product being deleted
 */
class Product
{
    /**
     * @var WishlistCleaner
     */
    private $wishlistCleaner;

    /**
     * @param WishlistCleaner $wishlistCleaner
     */
    public function __construct(
        WishlistCleaner $wishlistCleaner
    ) {
        $this->wishlistCleaner = $wishlistCleaner;
    }

    /**
     * Cleans up wishlist items referencing the product being deleted
     *
     * @param ProductResourceModel $productResourceModel
     * @param mixed $product
     * @return void
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDelete(
        ProductResourceModel $productResourceModel,
        $product
    ) {
        if ($product instanceof ProductInterface) {
            $this->wishlistCleaner->execute($product);
        }
    }
}
