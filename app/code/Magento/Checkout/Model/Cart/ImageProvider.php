<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model\Cart;

use Magento\Checkout\CustomerData\DefaultItem;
use Magento\Framework\App\ObjectManager;

/**
 * @api
 */
class ImageProvider
{
    /**
     * @var \Magento\Quote\Api\CartItemRepositoryInterface
     */
    protected $itemRepository;

    /**
     * @var \Magento\Checkout\CustomerData\ItemPoolInterface
     * @deprecated No need for the pool as images are resolved in the default item implementation
     * @see \Magento\Checkout\CustomerData\DefaultItem::getProductForThumbnail
     */
    protected $itemPool;

    /** @var \Magento\Checkout\CustomerData\DefaultItem */
    protected $customerDataItem;

    /**
     * @param \Magento\Quote\Api\CartItemRepositoryInterface $itemRepository
     * @param \Magento\Checkout\CustomerData\ItemPoolInterface $itemPool
     * @param DefaultItem|null $customerDataItem
     */
    public function __construct(
        \Magento\Quote\Api\CartItemRepositoryInterface $itemRepository,
        \Magento\Checkout\CustomerData\ItemPoolInterface $itemPool,
        \Magento\Checkout\CustomerData\DefaultItem $customerDataItem = null
    ) {
        $this->itemRepository = $itemRepository;
        $this->itemPool = $itemPool;
        $this->customerDataItem = $customerDataItem ?: ObjectManager::getInstance()->get(DefaultItem::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getImages($cartId)
    {
        $itemData = [];

        /** @see code/Magento/Catalog/Helper/Product.php */
        $items = $this->itemRepository->getList($cartId);
        /** @var \Magento\Quote\Model\Quote\Item $cartItem */
        foreach ($items as $cartItem) {
            $allData = $this->customerDataItem->getItemData($cartItem);
            $itemData[$cartItem->getItemId()] = $allData['product_image'];
        }
        return $itemData;
    }
}
