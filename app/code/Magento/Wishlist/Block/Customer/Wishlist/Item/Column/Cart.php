<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Block\Customer\Wishlist\Item\Column;

use Magento\Catalog\Block\Product\Context as ProductContext;
use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\StockDataFilter;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\ConfigInterface;
use Magento\Wishlist\Block\Customer\Wishlist\Item\Column;
use Magento\Wishlist\Model\Item;

/**
 * Wishlist block customer item cart column
 *
 * @api
 * @since 100.0.2
 */
class Cart extends Column
{
    /**
     * @param ProductContext $context
     * @param Context $httpContext
     * @param array $data
     * @param ConfigInterface|null $config
     * @param UrlBuilder|null $urlBuilder
     * @param View|null $productView
     */
    public function __construct(
        ProductContext   $context,
        Context          $httpContext,
        array            $data = [],
        ?ConfigInterface $config = null,
        ?UrlBuilder      $urlBuilder = null,
        private ?View    $productView = null
    ) {
        $this->productView = $productView ?: ObjectManager::getInstance()->get(View::class);
        parent::__construct($context, $httpContext, $data, $config, $urlBuilder);
    }

    /**
     * Returns qty to show visually to user
     *
     * @param Item $item
     * @return float
     */
    public function getAddToCartQty(Item $item)
    {
        $qty = $item->getQty();
        $qty = $qty < $this->productView->getProductDefaultQty($this->getProductItem())
                ? $this->productView->getProductDefaultQty($this->getProductItem()) : $qty;
        return $qty ?: 1;
    }

    /**
     * Return product for current item
     *
     * @return Product
     */
    public function getProductItem()
    {
        return $this->getItem()->getProduct();
    }
}
