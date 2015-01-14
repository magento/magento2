<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart;

use Magento\Framework\View\Block\IdentityInterface;

/**
 * Wishlist sidebar block
 */
class Sidebar extends AbstractCart implements IdentityInterface
{
    /**
     * Xml pah to chackout sidebar count value
     */
    const XML_PATH_CHECKOUT_SIDEBAR_COUNT = 'checkout/sidebar/count';

    /**
     * @var \Magento\Catalog\Model\Resource\Url
     */
    protected $_catalogUrl;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_checkoutCart;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $_checkoutHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Model\Resource\Url $catalogUrl
     * @param \Magento\Checkout\Model\Cart $checkoutCart
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\Resource\Url $catalogUrl,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        array $data = []
    ) {
        $this->_checkoutHelper = $checkoutHelper;
        $this->_catalogUrl = $catalogUrl;
        $this->_checkoutCart = $checkoutCart;
        parent::__construct($context, $customerSession, $checkoutSession, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Retrieve count of display recently added items
     *
     * @return int
     */
    public function getItemCount()
    {
        $count = $this->getData('item_count');
        if (is_null($count)) {
            $count = $this->_scopeConfig->getValue(
                self::XML_PATH_CHECKOUT_SIDEBAR_COUNT,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $this->setData('item_count', $count);
        }
        return $count;
    }

    /**
     * Get array of last added items
     *
     * @param int|null $count
     * @return array
     */
    public function getRecentItems($count = null)
    {
        if ($count === null) {
            $count = $this->getItemCount();
        }

        $items = [];
        if (!$this->getSummaryCount()) {
            return $items;
        }

        $i = 0;
        $allItems = array_reverse($this->getItems());
        foreach ($allItems as $item) {
            /* @var $item \Magento\Sales\Model\Quote\Item */
            if (!$item->getProduct()->isVisibleInSiteVisibility()) {
                $productId = $item->getProduct()->getId();
                $products = $this->_catalogUrl->getRewriteByProductStore([$productId => $item->getStoreId()]);
                if (!isset($products[$productId])) {
                    continue;
                }
                $urlDataObject = new \Magento\Framework\Object($products[$productId]);
                $item->getProduct()->setUrlDataObject($urlDataObject);
            }

            $items[] = $item;
            if (++$i == $count) {
                break;
            }
        }

        return $items;
    }

    /**
     * Get shopping cart subtotal.
     *
      * @return  float
     */
    public function getSubtotal()
    {
        $subtotal = 0;
        $totals = $this->getTotals();
        if (isset($totals['subtotal'])) {
            $subtotal = $totals['subtotal']->getValue();
        }
        return $subtotal;
    }

    /**
     * Get shopping cart items qty based on configuration (summary qty or items qty)
     *
     * @return int|float
     */
    public function getSummaryCount()
    {
        if ($this->getData('summary_qty')) {
            return $this->getData('summary_qty');
        }
        return $this->_checkoutCart->getSummaryQty();
    }

    /**
     * Check if one page checkout is available
     *
     * @return bool
     */
    public function isPossibleOnepageCheckout()
    {
        return $this->_checkoutHelper->canOnepageCheckout() && !$this->getQuote()->getHasError();
    }

    /**
     * Get one page checkout page url
     *
     * @return bool
     */
    public function getCheckoutUrl()
    {
        return $this->getUrl('checkout/onepage');
    }

    /**
     * Define if Mini Shopping Cart Pop-Up Menu enabled
     *
     * @return bool
     */
    public function getIsNeedToDisplaySideBar()
    {
        return (bool)$this->_scopeConfig->getValue(
            'checkout/sidebar/display',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return customer quote items
     *
     * @return array
     */
    public function getItems()
    {
        if ($this->getCustomQuote()) {
            return $this->getCustomQuote()->getAllVisibleItems();
        }

        return parent::getItems();
    }

    /**
     * Return totals from custom quote if needed
     *
     * @return array
     */
    public function getTotalsCache()
    {
        if (empty($this->_totals)) {
            $quote = $this->getCustomQuote() ? $this->getCustomQuote() : $this->getQuote();
            $this->_totals = $quote->getTotals();
        }
        return $this->_totals;
    }

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $cacheKeyInfo = parent::getCacheKeyInfo();
        $cacheKeyInfo['item_renders'] = $this->_serializeRenders();
        return $cacheKeyInfo;
    }

    /**
     * Serialize renders
     *
     * @return string
     */
    protected function _serializeRenders()
    {
        $result = [];
        foreach ($this->getLayout()->getChildBlocks(
            $this->_getRendererList()->getNameInLayout()
        ) as $alias => $block) {
            /** @var $block \Magento\Framework\View\Element\Template */
            $result[] = implode('|', [$alias, get_class($block), $block->getTemplate()]);
        }
        return implode('|', $result);
    }

    /**
     * De-serialize renders from string
     *
     * @param string $renders
     * @return $this
     */
    public function deserializeRenders($renders)
    {
        if (!is_string($renders)) {
            return $this;
        }
        $rendererList = $this->addChild('renderer.list', 'Magento\Framework\View\Element\RendererList');

        $renders = explode('|', $renders);
        while (!empty($renders)) {
            $template = array_pop($renders);
            $block = array_pop($renders);
            $alias = array_pop($renders);
            if (!$template || !$block || !$alias) {
                continue;
            }

            if (!$rendererList->getChildBlock($alias)) {
                $rendererList->addChild($alias, $block, ['template' => $template]);
            }
        }
        return $this;
    }

    /**
     * Retrieve block cache tags
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];
        /** @var $item \Magento\Sales\Model\Quote\Item */
        foreach ($this->getItems() as $item) {
            $identities = array_merge($identities, $item->getProduct()->getIdentities());
        }
        return $identities;
    }

    public function getTotalsHtml()
    {
        return $this->getLayout()->getBlock('checkout.cart.minicart.totals')->toHtml();
    }
}
