<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model\Cart;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 2.0.0
 */
class ImageProvider
{
    /**
     * @var \Magento\Quote\Api\CartItemRepositoryInterface
     * @since 2.0.0
     */
    protected $itemRepository;

    /**
     * @var \Magento\Checkout\CustomerData\ItemPoolInterface
     * @since 2.0.0
     */
    protected $itemPool;

    /**
     * @param \Magento\Quote\Api\CartItemRepositoryInterface $itemRepository
     * @param \Magento\Checkout\CustomerData\ItemPoolInterface $itemPool
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Quote\Api\CartItemRepositoryInterface $itemRepository,
        \Magento\Checkout\CustomerData\ItemPoolInterface $itemPool
    ) {
        $this->itemRepository = $itemRepository;
        $this->itemPool = $itemPool;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getImages($cartId)
    {
        $itemData = [];

        /** @see code/Magento/Catalog/Helper/Product.php */
        $items = $this->itemRepository->getList($cartId);
        /** @var \Magento\Quote\Model\Quote\Item $cartItem */
        foreach ($items as $cartItem) {
            $allData = $this->itemPool->getItemData($cartItem);
            $itemData[$cartItem->getItemId()] = $allData['product_image'];
        }
        return $itemData;
    }
}
