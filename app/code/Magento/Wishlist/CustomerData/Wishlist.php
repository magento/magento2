<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\CustomerData;

use Magento\Catalog\Model\Product\Image\NotLoadInfoImageException;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\App\ObjectManager;

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
     * @var \Magento\Catalog\Helper\ImageFactory
     */
    protected $imageHelperFactory;

    /**
     * @var \Magento\Framework\App\ViewInterface
     */
    protected $view;

    /**
     * @var \Magento\Wishlist\Block\Customer\Sidebar
     */
    protected $block;

    /**
     * @var \Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface
     */
    private $itemResolver;

    /**
     * @param \Magento\Wishlist\Helper\Data $wishlistHelper
     * @param \Magento\Wishlist\Block\Customer\Sidebar $block
     * @param \Magento\Catalog\Helper\ImageFactory $imageHelperFactory
     * @param \Magento\Framework\App\ViewInterface $view
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface|null $itemResolver
     */
    public function __construct(
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\Wishlist\Block\Customer\Sidebar $block,
        \Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
        \Magento\Framework\App\ViewInterface $view,
        \Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface $itemResolver = null
    ) {
        $this->wishlistHelper = $wishlistHelper;
        $this->imageHelperFactory = $imageHelperFactory;
        $this->block = $block;
        $this->view = $view;
        $this->itemResolver = $itemResolver ?: ObjectManager::getInstance()->get(
            \Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface::class
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
     * Get number of Wishlist items
     *
     * @return string
     */
    protected function getCounter()
    {
        return (string) $this->wishlistHelper->getItemCount();
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
     * @param \Magento\Wishlist\Model\Item $wishlistItem
     * @return array
     */
    protected function getItemData(\Magento\Wishlist\Model\Item $wishlistItem)
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
                \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
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
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    protected function getImageData($product)
    {
        /** @var \Magento\Catalog\Helper\Image $helper */
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
