<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * Wishlist section
 */
class Wishlist implements SectionSourceInterface
{
    /**
     * @var string
     */
    const SIDEBAR_ITEMS_NUMBER = 3;

    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $wishlistHelper;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Framework\App\ViewInterface
     */
    protected $view;

    /**
     * @var \Magento\Wishlist\Block\Customer\Sidebar
     */
    protected $block;

    /**
     * @param \Magento\Wishlist\Helper\Data $wishlistHelper
     * @param \Magento\Wishlist\Block\Customer\Sidebar $block
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\App\ViewInterface $view
     */
    public function __construct(
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\Wishlist\Block\Customer\Sidebar $block,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\App\ViewInterface $view
    ) {
        $this->wishlistHelper = $wishlistHelper;
        $this->imageHelper = $imageHelper;
        $this->block = $block;
        $this->view = $view;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $counter = $this->getCounter();
        return [
            'counter' => $counter,
            'items' => $counter ? $this->getItems() : [],
        ];
    }

    /**
     * @return string
     */
    protected function getCounter()
    {
        return $this->createCounter($this->wishlistHelper->getItemCount());
    }

    /**
     * Create button label based on wishlist item quantity
     *
     * @param int $count
     * @return \Magento\Framework\Phrase|null
     */
    protected function createCounter($count)
    {
        if ($count > 1) {
            return __('%1 items', $count);
        } elseif ($count == 1) {
            return __('1 item');
        }
        return null;
    }

    /**
     * Get wishlist items
     *
     * @return array
     */
    protected function getItems()
    {
        $this->view->loadLayout();
        $collection = $this->wishlistHelper->getWishlistItemCollection();
        $collection->clear()->setPageSize(self::SIDEBAR_ITEMS_NUMBER)
            ->setInStockFilter(true)->setOrder('added_at');
        $items = [];
        /** @var \Magento\Wishlist\Model\Item $wishlistItem */
        foreach ($collection as $wishlistItem) {
            $product = $wishlistItem->getProduct();
            $this->imageHelper->init($product, 'wishlist_sidebar_block');
            $items[] = [
                'image' => [
                    'src' => $this->imageHelper->getUrl(),
                    'alt' => $this->imageHelper->getLabel(),
                    'width' => $this->imageHelper->getWidth(),
                    'height' => $this->imageHelper->getHeight(),
                ],
                'product_url' => $this->wishlistHelper->getProductUrl($wishlistItem),
                'product_name' => $product->getName(),
                'product_price' => $this->block->getProductPriceHtml(
                    $product,
                    \Magento\Catalog\Pricing\Price\ConfiguredPriceInterface::CONFIGURED_PRICE_CODE,
                    \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
                    ['item' => $wishlistItem]
                ),
                'product_is_saleable_and_visible' => $product->isSaleable() && $product->isVisibleInSiteVisibility(),
                'product_has_required_options' => $product->getTypeInstance()->hasRequiredOptions($product),
                'add_to_cart_params' => $this->wishlistHelper->getAddToCartParams($wishlistItem, true),
                'delete_item_params' => $this->wishlistHelper->getRemoveParams($wishlistItem, true),
            ];
        }
        return $items;
    }
}
