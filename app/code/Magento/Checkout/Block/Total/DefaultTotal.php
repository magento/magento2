<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Total;

/**
 * Default Total Row Renderer
 */
class DefaultTotal extends \Magento\Checkout\Block\Cart\Totals
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Checkout::total/default.phtml';

    /**
     * @var \Magento\Store\Model\Store
     */
    protected $_store;

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_store = $this->_storeManager->getStore();
    }

    /**
     * Get style assigned to total object
     *
     * @return string
     */
    public function getStyle()
    {
        return $this->getTotal()->getStyle();
    }

    /**
     * @param float $total
     * @return $this
     */
    public function setTotal($total)
    {
        $this->setData('total', $total);
        if ($total->getAddress()) {
            $this->_store = $total->getAddress()->getQuote()->getStore();
        }
        return $this;
    }

    /**
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_store;
    }
}
