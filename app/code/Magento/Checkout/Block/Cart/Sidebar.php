<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Checkout
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Checkout\Block\Cart;

/**
 * Wishlist sidebar block
 *
 * @category    Magento
 * @package     Magento_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Sidebar extends \Magento\Checkout\Block\Cart\AbstractCart
{
    const XML_PATH_CHECKOUT_SIDEBAR_COUNT   = 'checkout/sidebar/count';

    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxData;

    /**
     * @var \Magento\Catalog\Model\Resource\Url
     */
    protected $_catalogUrl;

    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $_taxConfig;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_checkoutCart;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $_checkoutHelper;

    /**
     * @var \Magento\Checkout\Helper\Url
     */
    protected $_urlHelper;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Catalog\Model\Resource\Url $catalogUrl
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Checkout\Model\Cart $checkoutCart
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Checkout\Helper\Url $urlHelper
     * @param array $data
     * 
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Catalog\Model\Resource\Url $catalogUrl,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Checkout\Helper\Url $urlHelper,
        array $data = array()
    ) {
        $this->_urlHelper = $urlHelper;
        $this->_checkoutHelper = $checkoutHelper;
        $this->_taxData = $taxData;
        $this->_catalogUrl = $catalogUrl;
        $this->_taxConfig = $taxConfig;
        $this->_checkoutCart = $checkoutCart;
        parent::__construct($context, $catalogData, $customerSession, $checkoutSession, $data);
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
            $count = $this->_storeConfig->getConfig(self::XML_PATH_CHECKOUT_SIDEBAR_COUNT);
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

        $items = array();
        if (!$this->getSummaryCount()) {
            return $items;
        }

        $i = 0;
        $allItems = array_reverse($this->getItems());
        foreach ($allItems as $item) {
            /* @var $item \Magento\Sales\Model\Quote\Item */
            if (!$item->getProduct()->isVisibleInSiteVisibility()) {
                $productId = $item->getProduct()->getId();
                $products  = $this->_catalogUrl
                    ->getRewriteByProductStore(array($productId => $item->getStoreId()));
                if (!isset($products[$productId])) {
                    continue;
                }
                $urlDataObject = new \Magento\Object($products[$productId]);
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
     * It will include tax, if required by config settings.
     *
     * @param   bool $skipTax flag for getting price with tax or not. Ignored in case when we display just subtotal incl.tax
     * @return  float
     */
    public function getSubtotal($skipTax = true)
    {
        $subtotal = 0;
        $totals = $this->getTotals();
        if (isset($totals['subtotal'])) {
            if ($this->_taxConfig->displayCartSubtotalBoth()) {
                if ($skipTax) {
                    $subtotal = $totals['subtotal']->getValueExclTax();
                } else {
                    $subtotal = $totals['subtotal']->getValueInclTax();
                }
            } elseif($this->_taxConfig->displayCartSubtotalInclTax()) {
                $subtotal = $totals['subtotal']->getValueInclTax();
            } else {
                $subtotal = $totals['subtotal']->getValue();
                if (!$skipTax && isset($totals['tax'])) {
                    $subtotal+= $totals['tax']->getValue();
                }
            }
        }
        return $subtotal;
    }

    /**
     * Get subtotal, including tax.
     * Will return > 0 only if appropriate config settings are enabled.
     *
     * @return float
     */
    public function getSubtotalInclTax()
    {
        if (!$this->_taxConfig->displayCartSubtotalBoth()) {
            return 0;
        }
        return $this->getSubtotal(false);
    }

    /**
     * Add tax to amount
     *
     * @param float $price
     * @param bool $exclShippingTax
     * @return float
     */
    private function _addTax($price, $exclShippingTax=true) {
        $totals = $this->getTotals();
        if (isset($totals['tax'])) {
            if ($exclShippingTax) {
                $price += $totals['tax']->getValue()-$this->_getShippingTaxAmount();
            } else {
                $price += $totals['tax']->getValue();
            }
        }
        return $price;
    }

    /**
     * Get shipping tax amount
     *
     * @return float
     */
    protected function _getShippingTaxAmount()
    {
        $quote = $this->getCustomQuote() ? $this->getCustomQuote() : $this->getQuote();
        return $quote->getShippingAddress()->getShippingTaxAmount();
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
     * Get incl/excl tax label
     *
     * @param bool $flag
     * @return string
     */
    public function getIncExcTax($flag)
    {
        $text = $this->_taxData->getIncExcText($flag);
        return $text ? ' ('.$text.')' : '';
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
        return $this->_urlHelper->getCheckoutUrl();
    }

    /**
     * Define if Mini Shopping Cart Pop-Up Menu enabled
     *
     * @return bool
     */
    public function getIsNeedToDisplaySideBar()
    {
        return (bool) $this->_storeManager->getStore()->getConfig('checkout/sidebar/display');
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
        $result = array();
        foreach ($this->getLayout()->getChildBlocks($this->_getRendererList()->getNameInLayout()) as $alias => $block) {
            /** @var $block \Magento\View\Element\Template */
            $result[] = implode('|', array(
                // skip $this->getNameInLayout() and '.'
                $alias,
                get_class($block),
                $block->getTemplate()
            ));
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
        $rendererList = $this->addChild('renderer.list', 'Magento\View\Element\RendererList');

        $renders = explode('|', $renders);
        while (!empty($renders)) {
            $template = array_pop($renders);
            $block = array_pop($renders);
            $alias = array_pop($renders);
            if (!$template || !$block || !$alias) {
                continue;
            }

            if (!$rendererList->getChildBlock($alias)) {
                $rendererList->addChild($alias, $block, array('template' => $template));
            }
        }
        return $this;
    }
}
