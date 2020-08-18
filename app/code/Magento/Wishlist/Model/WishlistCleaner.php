<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Wishlist\Model\ResourceModel\Item as ItemResourceModel;
use Magento\Wishlist\Model\ResourceModel\Item\Option as ItemOptionResourceModel;

/**
 * Deletes wishlist items
 */
class WishlistCleaner
{
    /**
     * Wishlist Item Option resource model
     *
     * @var ItemOptionResourceModel
     */
    private $itemOptionResourceModel;

    /**
     * Wishlist Item Option resource model
     *
     * @var ItemResourceModel
     */
    private $itemResourceModel;

    /**
     * @param ItemOptionResourceModel $itemOptionResourceModel
     * @param ItemResourceModel $itemResourceModel
     */
    public function __construct(
        ItemOptionResourceModel $itemOptionResourceModel,
        ItemResourceModel $itemResourceModel
    ) {
        $this->itemOptionResourceModel = $itemOptionResourceModel;
        $this->itemResourceModel = $itemResourceModel;
    }

    /**
     * Deletes all wishlist items related the specified product
     *
     * @param ProductInterface $product
     * @throws LocalizedException
     */
    public function execute(ProductInterface $product)
    {
        $connection = $this->itemResourceModel->getConnection();

        $selectQuery = $connection
            ->select()
            ->from(['w_item' => $this->itemResourceModel->getMainTable()])
            ->join(
                ['w_item_option' => $this->itemOptionResourceModel->getMainTable()],
                'w_item.wishlist_item_id = w_item_option.wishlist_item_id'
            )
            ->where('w_item_option.product_id = ?', $product->getId());

        $connection->query($selectQuery->deleteFromSelect('w_item'));
    }
}
