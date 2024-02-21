<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained from
 * Adobe.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\CartItem;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory as ItemCollectionFactory;

/**
 * Fetch Cart items and product models corresponding to a cart
 */
class GetPaginatedCartItems
{
    /**
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ItemCollectionFactory $itemCollectionFactory
     */
    public function __construct(
        private readonly ProductCollectionFactory $productCollectionFactory,
        private readonly ItemCollectionFactory $itemCollectionFactory
    ) {
    }

    /**
     * Get product models based on items in cart
     *
     * @param array $cartProductsIds
     * @return ProductInterface[]
     */
    private function getCartProduct(array $cartProductsIds): array
    {
        if (empty($cartProductsIds)) {
            return [];
        }
        /** @var \Magento\Framework\Data\Collection $productCollection */
        $productCollection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addIdFilter($cartProductsIds)
            ->setFlag('has_stock_status_filter', true);

        return $productCollection->getItems();
    }

    /**
     * Get visible cart items and product data for cart items
     *
     * @param Quote $cart
     * @param int $pageSize
     * @param int $offset
     * @param string $orderBy
     * @param string $order
     * @return array
     */
    public function execute(Quote $cart, int $pageSize, int $offset, string $orderBy, string $order): array
    {
        $result = [];
        if (!$cart->getId()) {
            return $result;
        }
        /** @var \Magento\Framework\Data\Collection $itemCollection */
        $itemCollection =  $this->itemCollectionFactory->create()
            ->addFieldToFilter('parent_item_id', ['null' => true])
            ->addFieldToFilter('quote_id', $cart->getId())
            ->setOrder($orderBy, $order)
            ->setCurPage($offset)
            ->setPageSize($pageSize);

        $items = [];
        $cartProductsIds = [];
        $itemDeletedCount = 0;
        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($itemCollection->getItems() as $item) {
            if (!$item->isDeleted()) {
                $items[] = $item;
                $cartProductsIds[] = $item->getProduct()->getId();
            } else {
                $itemDeletedCount++;
            }
        }
        $result['total'] = $itemCollection->getSize() - $itemDeletedCount;
        $result['items'] = $items;
        $result['products'] = $this->getCartProduct($cartProductsIds);
        return $result;
    }
}
