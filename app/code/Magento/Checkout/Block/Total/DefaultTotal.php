<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Total;

/**
 * Default Total Row Renderer
 * @since 2.0.0
 */
class DefaultTotal extends \Magento\Checkout\Block\Cart\Totals
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'Magento_Checkout::total/default.phtml';

    /**
     * @var \Magento\Store\Model\Store
     * @since 2.0.0
     */
    protected $_store;

    /**
     * @return void
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getStyle()
    {
        return $this->getTotal()->getStyle();
    }

    /**
     * @param float $total
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getStore()
    {
        return $this->_store;
    }
}
