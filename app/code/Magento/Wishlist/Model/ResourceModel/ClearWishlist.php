<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Model\ResourceModel;

use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\Wishlist\Data\Error;
use Magento\Wishlist\Model\Wishlist\Data\WishlistOutput;
use Psr\Log\LoggerInterface;

class ClearWishlist
{
    private const ERROR_UNDEFINED = 'UNDEFINED';

    /**
     * ClearWishlist Constructor
     *
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly ResourceConnection $resourceConnection,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Remove all items from specified wishlist
     *
     * @param Wishlist $wishlist
     * @return WishlistOutput
     */
    public function execute(Wishlist $wishlist): WishlistOutput
    {
        try {
            $this->resourceConnection->getConnection()->delete(
                $this->resourceConnection->getTableName('wishlist_item'),
                ['wishlist_id = ?' => $wishlist->getId()]
            );

            return new WishlistOutput($wishlist, []);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());

            return new WishlistOutput($wishlist, [
                new Error(
                    "Could not delete wishlist items for WishlistId '{$wishlist->getId()}'.",
                    self::ERROR_UNDEFINED
                )
            ]);
        }
    }
}
