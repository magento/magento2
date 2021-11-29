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
 * @since 100.0.2
 */
class ImageProvider
{
    /**
     * @var \Magento\Quote\Api\CartItemRepositoryInterface
     */
    protected $itemRepository;

    /**
     * @var \Magento\Checkout\CustomerData\ItemPoolInterface
     * @deprecated 100.2.7 No need for the pool as images are resolved in the default item implementation
     * @see \Magento\Checkout\CustomerData\DefaultItem::getProductForThumbnail
     */
    protected $itemPool;

    /**
     * @var \Magento\Checkout\CustomerData\DefaultItem
     * @since 100.2.7
     */
    protected $customerDataItem;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;

    /**
     * @var \Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface
     */
    private $itemResolver;

    /**
     * @param \Magento\Quote\Api\CartItemRepositoryInterface $itemRepository
     * @param \Magento\Checkout\CustomerData\ItemPoolInterface $itemPool
     * @param DefaultItem|null $customerDataItem
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface $itemResolver
     */
    public function __construct(
        \Magento\Quote\Api\CartItemRepositoryInterface $itemRepository,
        \Magento\Checkout\CustomerData\ItemPoolInterface $itemPool,
        \Magento\Checkout\CustomerData\DefaultItem $customerDataItem = null,
        \Magento\Catalog\Helper\Image $imageHelper = null,
        \Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface $itemResolver = null
    ) {
        $this->itemRepository = $itemRepository;
        $this->itemPool = $itemPool;
        $this->customerDataItem = $customerDataItem ?: ObjectManager::getInstance()->get(DefaultItem::class);
        $this->imageHelper = $imageHelper ?: ObjectManager::getInstance()->get(\Magento\Catalog\Helper\Image::class);
        $this->itemResolver = $itemResolver ?: ObjectManager::getInstance()->get(
            \Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface::class
        );
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
            $itemData[$cartItem->getItemId()] = $this->getProductImageData($cartItem);
        }
        return $itemData;
    }

    /**
     * Get product image data
     *
     * @param \Magento\Quote\Model\Quote\Item $cartItem
     *
     * @return array
     */
    private function getProductImageData($cartItem)
    {
        $imageHelper = $this->imageHelper->init(
            $this->itemResolver->getFinalProduct($cartItem),
            'mini_cart_product_thumbnail'
        );
        $imageData = [
            'src' => $imageHelper->getUrl(),
            'alt' => $imageHelper->getLabel(),
            'width' => $imageHelper->getWidth(),
            'height' => $imageHelper->getHeight(),
        ];
        return $imageData;
    }
}
