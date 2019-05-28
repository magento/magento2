<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Block\Customer\Wishlist\Item\Column;

use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\StockDataFilter;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\ConfigInterface;

/**
 * Wishlist block customer item cart column
 *
 * @api
 * @since 100.0.2
 */
class Cart extends \Magento\Wishlist\Block\Customer\Wishlist\Item\Column
{
    /**
     * @var View
     */
    private $productView;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     * @param ConfigInterface|null $config
     * @param UrlBuilder|null $urlBuilder
     * @param View|null $productView
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = [],
        ?ConfigInterface $config = null,
        ?UrlBuilder $urlBuilder = null,
        ?View $productView = null
    ) {
        $this->productView = $productView ?: ObjectManager::getInstance()->get(View::class);
        parent::__construct($context, $httpContext, $data, $config, $urlBuilder);
    }

    /**
     * Returns qty to show visually to user
     *
     * @param \Magento\Wishlist\Model\Item $item
     * @return float
     */
    public function getAddToCartQty(\Magento\Wishlist\Model\Item $item)
    {
        $qty = $item->getQty();
        $qty = $qty < $this->productView->getProductDefaultQty($this->getProductItem())
                ? $this->productView->getProductDefaultQty($this->getProductItem()) : $qty;
        return $qty ?: 1;
    }

    /**
     * Return product for current item
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProductItem()
    {
        return $this->getItem()->getProduct();
    }
}
