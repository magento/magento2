<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\CustomerData;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;
use Magento\Catalog\Model\Product\Image\NotLoadInfoImageException;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\Render;
use Magento\Wishlist\Block\Customer\Sidebar;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Model\Item;

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
     * @param Data $wishlistHelper
     * @param Sidebar $block
     * @param ImageFactory $imageHelperFactory
     * @param ViewInterface $view
     * @param ItemResolverInterface|null $itemResolver
     */
    public function __construct(
        protected readonly Data $wishlistHelper,
        protected readonly Sidebar $block,
        protected readonly ImageFactory $imageHelperFactory,
        protected readonly ViewInterface $view,
        private ?ItemResolverInterface $itemResolver = null
    ) {
        $this->itemResolver = $itemResolver ?: ObjectManager::getInstance()->get(
            ItemResolverInterface::class
        );
    }

    /**
     * @inheritdoc
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
     * Get counter
     *
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
     * @return Phrase|null
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
        foreach ($collection as $wishlistItem) {
            $items[] = $this->getItemData($wishlistItem);
        }
        return $items;
    }

    /**
     * Retrieve wishlist item data
     *
     * @param Item $wishlistItem
     * @return array
     */
    protected function getItemData(Item $wishlistItem)
    {
        $product = $wishlistItem->getProduct();
        return [
            'image' => $this->getImageData($this->itemResolver->getFinalProduct($wishlistItem)),
            'product_sku' => $product->getSku(),
            'product_id' => $product->getId(),
            'product_url' => $this->wishlistHelper->getProductUrl($wishlistItem),
            'product_name' => $product->getName(),
            'product_price' => $this->block->getProductPriceHtml(
                $product,
                'wishlist_configured_price',
                Render::ZONE_ITEM_LIST,
                ['item' => $wishlistItem]
            ),
            'product_is_saleable_and_visible' => $product->isSaleable() && $product->isVisibleInSiteVisibility(),
            'product_has_required_options' => $product->getTypeInstance()->hasRequiredOptions($product),
            'add_to_cart_params' => $this->wishlistHelper->getAddToCartParams($wishlistItem),
            'delete_item_params' => $this->wishlistHelper->getRemoveParams($wishlistItem),
        ];
    }

    /**
     * Retrieve product image data
     *
     * @param Product $product
     * @return array
     */
    protected function getImageData($product)
    {
        /** @var Image $helper */
        $helper = $this->imageHelperFactory->create()
            ->init($product, 'wishlist_sidebar_block');

        return [
            'template' => 'Magento_Catalog/product/image_with_borders',
            'src' => $helper->getUrl(),
            'width' => $helper->getWidth(),
            'height' => $helper->getHeight(),
            'alt' => $helper->getLabel(),
        ];
    }
}
