<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\ProductAlert\Block\Email;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Product Alert Abstract Email Block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
abstract class AbstractEmail extends \Magento\Framework\View\Element\Template
{
    /**
     * Product collection array
     *
     * @var array
     * @since 2.0.0
     */
    protected $_products = [];

    /**
     * Current Store scope object
     *
     * @var \Magento\Store\Model\Store
     * @since 2.0.0
     */
    protected $_store;

    /**
     * @var \Magento\Framework\Filter\Input\MaliciousCode
     * @since 2.0.0
     */
    protected $_maliciousCode;

    /**
     * @var PriceCurrencyInterface
     * @since 2.0.0
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Catalog\Helper\Image
     * @since 2.0.0
     */
    protected $imageBuilder;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Filter\Input\MaliciousCode $maliciousCode
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Filter\Input\MaliciousCode $maliciousCode,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        array $data = []
    ) {
        $this->imageBuilder = $imageBuilder;
        $this->priceCurrency = $priceCurrency;
        $this->_maliciousCode = $maliciousCode;
        parent::__construct($context, $data);
    }

    /**
    * Filter malicious code before insert content to email
    *
    * @param  string|array $content
    * @return string|array
     * @since 2.0.0
    */
    public function getFilteredContent($content)
    {
        return $this->_maliciousCode->filter($content);
    }

    /**
     * Set Store scope
     *
     * @param int|string|\Magento\Store\Model\Website|\Magento\Store\Model\Store $store
     * @return $this
     * @since 2.0.0
     */
    public function setStore($store)
    {
        if ($store instanceof \Magento\Store\Model\Website) {
            $store = $store->getDefaultStore();
        }
        if (!$store instanceof \Magento\Store\Model\Store) {
            $store = $this->_storeManager->getStore($store);
        }

        $this->_store = $store;

        return $this;
    }

    /**
     * Retrieve current store object
     *
     * @return \Magento\Store\Model\Store
     * @since 2.0.0
     */
    public function getStore()
    {
        if (is_null($this->_store)) {
            $this->_store = $this->_storeManager->getStore();
        }
        return $this->_store;
    }

    /**
     * Convert price from default currency to current currency
     *
     * @param float $price
     * @param boolean $format             Format price to currency format
     * @param boolean $includeContainer   Enclose into <span class="price"><span>
     * @return float
     * @since 2.0.0
     */
    public function formatPrice($price, $format = true, $includeContainer = true)
    {
        return $format
            ? $this->priceCurrency->convertAndFormat($price, $includeContainer)
            : $this->priceCurrency->convert($price);
    }

    /**
     * Reset product collection
     *
     * @return void
     * @since 2.0.0
     */
    public function reset()
    {
        $this->_products = [];
    }

    /**
     * Add product to collection
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     * @since 2.0.0
     */
    public function addProduct(\Magento\Catalog\Model\Product $product)
    {
        $this->_products[$product->getId()] = $product;
    }

    /**
     * Retrieve product collection array
     *
     * @return array
     * @since 2.0.0
     */
    public function getProducts()
    {
        return $this->_products;
    }

    /**
     * Get store url params
     *
     * @return array
     * @since 2.0.0
     */
    protected function _getUrlParams()
    {
        return ['_scope' => $this->getStore(), '_scope_to_url' => true];
    }

    /**
     * @return \Magento\Framework\Pricing\Render
     * @since 2.0.0
     */
    protected function getPriceRender()
    {
        return $this->_layout->createBlock(
            \Magento\Framework\Pricing\Render::class,
            '',
            ['data' => ['price_render_handle' => 'catalog_product_prices']]
        );
    }

    /**
     * Return HTML block with tier price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $priceType
     * @param string $renderZone
     * @param array $arguments
     * @return string
     * @since 2.0.0
     */
    public function getProductPriceHtml(
        \Magento\Catalog\Model\Product $product,
        $priceType,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }

        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getPriceRender();
        $price = '';

        if ($priceRender) {
            $price = $priceRender->render(
                $priceType,
                $product,
                $arguments
            );
        }
        return $price;
    }

    /**
     * Retrieve product image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     * @since 2.0.0
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
    }
}
