<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Model\Wishlist\Data;

use Magento\Wishlist\Model\Wishlist;

/**
 * DTO represent output for \Magento\WishlistGraphQl\Model\Resolver\AddProductsToWishlistResolver
 */
class WishlistOutput
{
    /**
     * @var Wishlist
     */
    private $wishlist;

    /**
     * @var Error[]
     */
    private $errors;

    /**
     * @param Wishlist $wishlist
     * @param Error[] $errors
     */
    public function __construct(Wishlist $wishlist, array $errors)
    {
        $this->wishlist = $wishlist;
        $this->errors = $errors;
    }

    /**
     * Get Wishlist
     *
     * @return Wishlist
     */
    public function getWishlist(): Wishlist
    {
        return $this->wishlist;
    }

    /**
     * Get errors happened during adding products to wishlist
     *
     * @return Error[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
