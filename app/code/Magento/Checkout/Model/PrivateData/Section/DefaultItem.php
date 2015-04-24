<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Model\PrivateData\Section;

/**
 * Default item
 */
class DefaultItem extends AbstractItem
{
    /**
     * @var \Magento\Catalog\Model\Product\Image\View
     */
    protected $productImageView;

    /**
     * @var \Magento\Msrp\Helper\Data
     */
    protected $msrpHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Catalog\Helper\Product\Configuration
     */
    protected $configurationHelper;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * @param \Magento\Catalog\Model\Product\Image\View $productImageView
     * @param \Magento\Msrp\Helper\Data $msrpHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Catalog\Helper\Product\Configuration $configurationHelper
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Image\View $productImageView,
        \Magento\Msrp\Helper\Data $msrpHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Catalog\Helper\Product\Configuration $configurationHelper,
        \Magento\Checkout\Helper\Data $checkoutHelper
    ) {
        $this->configurationHelper = $configurationHelper;
        $this->productImageView = $productImageView;
        $this->msrpHelper = $msrpHelper;
        $this->urlBuilder = $urlBuilder;
        $this->checkoutHelper = $checkoutHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetItemData()
    {
        $this->productImageView->init($this->item->getProduct(), 'mini_cart_product_thumbnail', 'Magento_Catalog');
        return [
            'options' => $this->configurationHelper->getCustomOptions($this->item),
            'qty' => $this->getQty(),
            'item_id' => $this->item->getId(),
            'configure_url' => $this->getConfigureUrl(),
            'is_visible_in_site_visibility' => $this->item->getProduct()->isVisibleInSiteVisibility(),
            'product_name' => $this->getProductName(),
            'product_url' => $this->getProductUrl(),
            'product_has_url' => $this->hasProductUrl(),
            'product_price' => $this->checkoutHelper->formatPrice($this->item->getCalculationPrice()),
            'product_image' => [
                'src' => $this->productImageView->getUrl(),
                'alt' => $this->productImageView->getLabel(),
                'width' => $this->productImageView->getWidth(),
                'height' => $this->productImageView->getHeight(),
            ],
            'canApplyMsrp' => $this->msrpHelper->isShowBeforeOrderConfirm($this->item->getProduct())
                && $this->msrpHelper->isMinimalPriceLessMsrp($this->item->getProduct())
        ];
    }

    /**
     * Get item configure url
     * @param \Magento\Quote\Model\Quote\Item  $this->item
     *
     * @return string
     */
    public function getConfigureUrl()
    {
        return $this->urlBuilder->getUrl(
            'checkout/cart/configure',
            ['id' => $this->item->getId(), 'product_id' => $this->item->getProduct()->getId()]
        );
    }

    /**
     * Get quote item qty
     * @param \Magento\Quote\Model\Quote\Item  $this->item
     *
     * @return float|int
     */
    public function getQty()
    {
        return $this->item->getQty() * 1;
    }

    /**
     * Check Product has URL
     * @param \Magento\Quote\Model\Quote\Item  $this->item
     *
     * @return bool
     */
    public function hasProductUrl()
    {
        if ($this->item->getRedirectUrl()) {
            return true;
        }

        $product = $this->item->getProduct();
        $option = $this->item->getOptionByCode('product_type');
        if ($option) {
            $product = $option->getProduct();
        }

        if ($product->isVisibleInSiteVisibility()) {
            return true;
        } else {
            if ($product->hasUrlDataObject()) {
                $data = $product->getUrlDataObject();
                if (in_array($data->getVisibility(), $product->getVisibleInSiteVisibilities())) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Retrieve URL to item Product
     * @param \Magento\Quote\Model\Quote\Item  $this->item
     *
     * @return string
     */
    public function getProductUrl()
    {
        if ($this->item->getRedirectUrl()) {
            return $this->item->getRedirectUrl();
        }

        $product = $this->item->getProduct();
        $option = $this->item->getOptionByCode('product_type');
        if ($option) {
            $product = $option->getProduct();
        }

        return $product->getUrlModel()->getUrl($product);
    }

    /**
     * Get item product name
     * @param \Magento\Quote\Model\Quote\Item  $this->item
     *
     * @return string
     */
    public function getProductName()
    {
        return $this->item->getProduct()->getName();
    }
}
