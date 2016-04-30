<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart;

use Magento\Quote\Model\Quote;

/**
 * Shopping cart abstract block
 */
class AbstractCart extends \Magento\Framework\View\Element\Template
{
    /**
     * Block alias fallback
     */
    const DEFAULT_TYPE = 'default';

    /**
     * @var Quote|null
     */
    protected $_quote = null;

    /**
     * @var array
     */
    protected $_totals;

    /**
     * @var array
     */
    protected $_itemRenders = [];

    /**
     * TODO: MAGETWO-34827: unused object?
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Retrieve renderer list
     *
     * @return \Magento\Framework\View\Element\RendererList
     */
    protected function _getRendererList()
    {
        return $this->getRendererListName() ? $this->getLayout()->getBlock(
            $this->getRendererListName()
        ) : $this->getChildBlock(
            'renderer.list'
        );
    }

    /**
     * Retrieve item renderer block
     *
     * @param string|null $type
     * @return \Magento\Framework\View\Element\Template
     * @throws \RuntimeException
     */
    public function getItemRenderer($type = null)
    {
        if ($type === null) {
            $type = self::DEFAULT_TYPE;
        }
        $rendererList = $this->_getRendererList();
        if (!$rendererList) {
            throw new \RuntimeException('Renderer list for block "' . $this->getNameInLayout() . '" is not defined');
        }
        $overriddenTemplates = $this->getOverriddenTemplates() ?: [];
        $template = isset($overriddenTemplates[$type]) ? $overriddenTemplates[$type] : $this->getRendererTemplate();
        return $rendererList->getRenderer($type, self::DEFAULT_TYPE, $template);
    }

    /**
     * Get active quote
     *
     * @return Quote
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
     * @codeCoverageIgnore
     */
    public function getItems()
    {
        return $this->getQuote()->getAllVisibleItems();
    }

    /**
     * Get item row html
     *
     * @param   \Magento\Quote\Model\Quote\Item $item
     * @return  string
     */
    public function getItemHtml(\Magento\Quote\Model\Quote\Item $item)
    {
        $renderer = $this->getItemRenderer($item->getProductType())->setItem($item);
        return $renderer->toHtml();
    }

    /**
     * @return array
     * @codeCoverageIgnore
     */
    public function getTotals()
    {
        return $this->getTotalsCache();
    }

    /**
     * @return array
     */
    public function getTotalsCache()
    {
        if (empty($this->_totals)) {
            if ($this->getQuote()->isVirtual()) {
                $this->_totals = $this->getQuote()->getBillingAddress()->getTotals();
            } else {
                $this->_totals = $this->getQuote()->getShippingAddress()->getTotals();
            }
        }
        return $this->_totals;
    }
}
