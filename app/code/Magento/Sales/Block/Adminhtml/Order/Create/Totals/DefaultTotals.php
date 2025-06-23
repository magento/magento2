<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Totals;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Default Total Row Renderer
 */
class DefaultTotals extends \Magento\Sales\Block\Adminhtml\Order\Create\Totals
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Sales::order/create/totals/default.phtml';

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * Retrieve quote session object
     *
     * @return \Magento\Backend\Model\Session\Quote
     */
    protected function _getSession()
    {
        return $this->_sessionQuote;
    }

    /**
     * Retrieve store model object
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_getSession()->getStore();
    }

    /**
     * Format price
     *
     * @param float $value
     * @return string
     */
    public function formatPrice($value)
    {
        return $this->priceCurrency->format(
            $value,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getStore()
        );
    }
}
