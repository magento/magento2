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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shopping cart abstract block
 *
 * @category    Magento
 * @package     Magento_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Checkout\Block\Cart;

class AbstractCart extends \Magento\Core\Block\Template
{
    /**
     * Block alias fallback
     */
    const DEFAULT_TYPE = 'default';

    protected $_customer = null;
    protected $_quote    = null;
    protected $_totals;
    protected $_itemRenders = array();

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Block\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Block\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = array()
    ) {
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_catalogData = $catalogData;
        parent::__construct($coreData, $context, $data);
    }

    /**
     * Initialize default item renderer
     */
    protected function _prepareLayout()
    {
        if (!$this->getChildBlock(self::DEFAULT_TYPE)) {
            $this->addChild(
                self::DEFAULT_TYPE,
                'Magento\Checkout\Block\Cart\Item\Renderer',
                array('template' => 'cart/item/default.phtml')
            );
        }
        return parent::_prepareLayout();
    }

    /**
     * Get renderer block instance by product type code
     *
     * @param  string $type
     * @throws \RuntimeException
     * @return \Magento\Core\Block\AbstractBlock
     */
    public function getItemRenderer($type)
    {
        $renderer = $this->getChildBlock($type) ?: $this->getChildBlock(self::DEFAULT_TYPE);
        if (!$renderer instanceof \Magento\View\Element\BlockInterface) {
            throw new \RuntimeException('Renderer for type "' . $type . '" does not exist.');
        }
        $renderer->setRenderedBlock($this);
        return $renderer;
    }


    /**
     * Get logged in customer
     *
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        if (null === $this->_customer) {
            $this->_customer = $this->_customerSession->getCustomer();
        }
        return $this->_customer;
    }

    /**
     * Get active quote
     *
     * @return \Magento\Sales\Model\Quote
     */
    public function getQuote()
    {
        if (null === $this->_quote) {
            $this->_quote = $this->_checkoutSession->getQuote();
        }
        return $this->_quote;
    }

    /**
     * Get all cart items
     *
     * @return array
     */
    public function getItems()
    {
        return $this->getQuote()->getAllVisibleItems();
    }

    /**
     * Get item row html
     *
     * @param   \Magento\Sales\Model\Quote\Item $item
     * @return  string
     */
    public function getItemHtml(\Magento\Sales\Model\Quote\Item $item)
    {
        $renderer = $this->getItemRenderer($item->getProductType())->setItem($item);
        return $renderer->toHtml();
    }

    public function getTotals()
    {
        return $this->getTotalsCache();
    }

    public function getTotalsCache()
    {
        if (empty($this->_totals)) {
            $this->_totals = $this->getQuote()->getTotals();
        }
        return $this->_totals;
    }

    /**
     * Check if can apply msrp to totals
     *
     * @return bool
     */
    public function canApplyMsrp()
    {
        if (!$this->getQuote()->hasCanApplyMsrp() && $this->_catalogData->isMsrpEnabled()) {
            $this->getQuote()->collectTotals();
        }
        return $this->getQuote()->getCanApplyMsrp();
    }
}
