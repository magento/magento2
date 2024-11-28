<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\CartItem;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory as ItemCollectionFactory;

/**
 * Fetch Cart items and product models corresponding to a cart
 */
class GetPaginatedCartItems
{
    /**
     * @param ItemCollectionFactory $itemCollectionFactory
     */
    public function __construct(
        private readonly ItemCollectionFactory $itemCollectionFactory
    ) {
    }

    /**
     * Get visible cart items and product data for cart items
     *
     * @param Quote $cart
     * @param int $pageSize
     * @param int $currentPage
     * @param string $orderBy
     * @param string $order
     * @return array
     */
    public function execute(Quote $cart, int $pageSize, int $currentPage, string $orderBy, string $order): array
    {
        if (!$cart->getId()) {
            return [
                'total' => 0,
                'items' => []
            ];
        }
        /** @var \Magento\Framework\Data\Collection $itemCollection */
        $itemCollection =  $this->itemCollectionFactory->create()
            ->addFieldToFilter('parent_item_id', ['null' => true])
            ->addFieldToFilter('quote_id', $cart->getId())
            ->setOrder($orderBy, $order)
            ->setCurPage($currentPage)
            ->setPageSize($pageSize)
            ->setQuote($cart);

        $items = [];
        $itemDeletedCount = 0;
        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($itemCollection->getItems() as $item) {
            if (!$item->isDeleted()) {
                $items[] = $item;
            } else {
                $itemDeletedCount++;
            }
        }

        return [
            'total' => $itemCollection->getSize() - $itemDeletedCount,
            'items' => $items
        ];
    }
}
