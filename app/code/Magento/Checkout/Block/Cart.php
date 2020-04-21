<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block;

use Magento\Customer\Model\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Shopping cart block
 *
 * @api
 */
class Cart extends \Magento\Checkout\Block\Cart\AbstractCart
{
    /**
     * Config settings path to determine is clear cart action enabled
     */
    public const XML_CONFIG_CLEAR_CART_ENABLED = 'checkout/cart/clear_cart_enabled';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Url
     */
    protected $_catalogUrlBuilder;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $_cartHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Model\ResourceModel\Url $catalogUrlBuilder
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrlBuilder,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        $this->_cartHelper = $cartHelper;
        $this->_catalogUrlBuilder = $catalogUrlBuilder;
        parent::__construct($context, $customerSession, $checkoutSession, $data);
        $this->_isScopePrivate = true;
        $this->httpContext = $httpContext;
    }

    /**
     * Prepare Quote Item Product URLs
     *
     * @codeCoverageIgnore
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->prepareItemUrls();
    }

    /**
     * Prepare cart items URLs
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function prepareItemUrls()
    {
        $products = [];
        /* @var $item \Magento\Quote\Model\Quote\Item */
        foreach ($this->getItems() as $item) {
            $product = $item->getProduct();
            $option = $item->getOptionByCode('product_type');
            if ($option) {
                $product = $option->getProduct();
            }

            if ($item->getStoreId() != $this->_storeManager->getStore()->getId() &&
                !$item->getRedirectUrl() &&
                !$product->isVisibleInSiteVisibility()
            ) {
                $products[$product->getId()] = $item->getStoreId();
            }
        }

        if ($products) {
            $products = $this->_catalogUrlBuilder->getRewriteByProductStore($products);
            foreach ($this->getItems() as $item) {
                $product = $item->getProduct();
                $option = $item->getOptionByCode('product_type');
                if ($option) {
                    $product = $option->getProduct();
                }

                if (isset($products[$product->getId()])) {
                    $object = new \Magento\Framework\DataObject($products[$product->getId()]);
                    $item->getProduct()->setUrlDataObject($object);
                }
            }
        }
    }

    /**
     * Checks is quote has error
     *
     * @codeCoverageIgnore
     * @return bool
     */
    public function hasError()
    {
        return $this->getQuote()->getHasError();
    }

    /**
     * Returns quote items summary qty
     *
     * @codeCoverageIgnore
     * @return int
     */
    public function getItemsSummaryQty()
    {
        return $this->getQuote()->getItemsSummaryQty();
    }

    /**
     * Checks is wishlist is active
     *
     * @codeCoverageIgnore
     * @return bool
     */
    public function isWishlistActive()
    {
        $isActive = $this->_getData('is_wishlist_active');
        if ($isActive === null) {
            $isActive = $this->_scopeConfig->getValue(
                'wishlist/general/active',
                ScopeInterface::SCOPE_STORE
            ) && $this->httpContext->getValue(
                Context::CONTEXT_AUTH
            );
            $this->setIsWishlistActive($isActive);
        }
        return $isActive;
    }

    /**
     * Returns checkout url
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->getUrl('checkout', ['_secure' => true]);
    }

    /**
     * Returns continue shopping url
     *
     * @return string
     */
    public function getContinueShoppingUrl()
    {
        $url = $this->getData('continue_shopping_url');
        if ($url === null) {
            $url = $this->_checkoutSession->getContinueShoppingUrl(true);
            if (!$url) {
                $url = $this->_urlBuilder->getUrl();
            }
            $this->setData('continue_shopping_url', $url);
        }
        return $url;
    }

    /**
     * Checks is quote is virtual
     *
     * @return bool
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsVirtual()
    {
        return $this->_cartHelper->getIsVirtualQuote();
    }

    /**
     * Return list of available checkout methods
     *
     * @param string $alias Container block alias in layout
     * @return array
     */
    public function getMethods($alias)
    {
        $childName = $this->getLayout()->getChildName($this->getNameInLayout(), $alias);
        if ($childName) {
            return $this->getLayout()->getChildNames($childName);
        }
        return [];
    }

    /**
     * Return HTML of checkout method (link, button etc.)
     *
     * @param string $name Block name in layout
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMethodHtml($name)
    {
        $block = $this->getLayout()->getBlock($name);
        if (!$block) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid method: %1', $name));
        }
        return $block->toHtml();
    }

    /**
     * Return customer quote items
     *
     * @return array
     */
    public function getItems()
    {
        if ($this->getCustomItems()) {
            return $this->getCustomItems();
        }

        return parent::getItems();
    }

    /**
     * Returns quote items count
     *
     * @codeCoverageIgnore
     * @return int
     */
    public function getItemsCount()
    {
        return $this->getQuote()->getItemsCount();
    }

    /**
     * Render pagination HTML
     *
     * @return string
     * @since 100.2.0
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Checks is clear cart action enabled
     *
     * @codeCoverageIgnore
     * @return boolean
     */
    public function isClearCartEnabled()
    {
        $isEnabled = $this->_getData('clear_cart_enabled');
        if ($isEnabled === null) {
            $isEnabled = $this->_scopeConfig->getValue(
                self::XML_CONFIG_CLEAR_CART_ENABLED,
                ScopeInterface::SCOPE_STORE
            );
        }
        return $isEnabled;
    }
}
