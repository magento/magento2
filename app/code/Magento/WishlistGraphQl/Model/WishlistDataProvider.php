<?php
declare(strict_types=1);
/**
 * WishlistItemTypeResolver
 *
 * @copyright Copyright © 2018 brandung GmbH & Co. KG. All rights reserved.
 * @author    david.verholen@brandung.de
 */

namespace Magento\WishlistGraphQl\Model;

use Magento\Wishlist\Model\ResourceModel\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;

class WishlistDataProvider
{
    /**
     * @var Wishlist
     */
    private $wishlistResource;
    /**
     * @var WishlistFactory
     */
    private $wishlistFactory;

    public function __construct(Wishlist $wishlistResource, WishlistFactory $wishlistFactory)
    {
        $this->wishlistResource = $wishlistResource;
        $this->wishlistFactory = $wishlistFactory;
    }

    public function getWishlistForCustomer(int $customerId): \Magento\Wishlist\Model\Wishlist
    {
        /** @var \Magento\Wishlist\Model\Wishlist $wishlist */
        $wishlist = $this->wishlistFactory->create();
        $this->wishlistResource->load($wishlist, $customerId, 'customer_id');
        return $wishlist;
    }
}
