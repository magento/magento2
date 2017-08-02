<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block\Product;

/**
 * Product price block
 * @since 2.0.0
 */
class Price extends \Magento\Framework\View\Element\Template implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * @var null
     * @since 2.0.0
     */
    protected $_priceDisplayType = null;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_idSuffix = '';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     * @since 2.0.0
     */
    protected $_catalogData = null;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     * @since 2.0.0
     */
    protected $string;

    /**
     * @var \Magento\Framework\Math\Random
     * @since 2.0.0
     */
    protected $mathRandom;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     * @since 2.0.0
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Checkout\Helper\Cart
     * @since 2.0.0
     */
    protected $_cartHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Checkout\Helper\Cart $cartHelper,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_catalogData = $catalogData;
        $this->_coreRegistry = $registry;
        $this->string = $string;
        $this->mathRandom = $mathRandom;
        $this->_cartHelper = $cartHelper;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve product
     *
     * @return \Magento\Catalog\Model\Product
     * @since 2.0.0
     */
    public function getProduct()
    {
        $product = $this->_getData('product');
        if (!$product) {
            $product = $this->_coreRegistry->registry('product');
        }
        return $product;
    }

    /**
     * @return mixed
     * @since 2.0.0
     */
    public function getDisplayMinimalPrice()
    {
        return $this->_getData('display_minimal_price');
    }

    /**
     * @param string $idSuffix
     * @return $this
     * @since 2.0.0
     */
    public function setIdSuffix($idSuffix)
    {
        $this->_idSuffix = $idSuffix;
        return $this;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getIdSuffix()
    {
        return $this->_idSuffix;
    }

    /**
     * Retrieve url for direct adding product to cart
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     * @return string
     * @since 2.0.0
     */
    public function getAddToCartUrl($product, $additional = [])
    {
        return $this->_cartHelper->getAddUrl($product, $additional);
    }

    /**
     * Prevent displaying if the price is not available
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        if (!$this->getProduct() || $this->getProduct()->getCanShowPrice() === false) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Get Product Price valid JS string
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     * @since 2.0.0
     */
    public function getRealPriceJs($product)
    {
        $html = $this->hasRealPriceHtml() ? $this->getRealPriceHtml() : $product->getRealPriceHtml();
        return $this->_jsonEncoder->encode($html);
    }

    /**
     * Prepare SKU
     *
     * @param string $sku
     * @return string
     * @since 2.0.0
     */
    public function prepareSku($sku)
    {
        return $this->escapeHtml($this->string->splitInjection($sku));
    }

    /**
     * Get random string
     *
     * @param int $length
     * @param string|null $chars
     * @return string
     * @since 2.0.0
     */
    public function getRandomString($length, $chars = null)
    {
        return $this->mathRandom->getRandomString($length, $chars);
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     * @since 2.0.0
     */
    public function getIdentities()
    {
        return $this->getProduct()->getIdentities();
    }
}
