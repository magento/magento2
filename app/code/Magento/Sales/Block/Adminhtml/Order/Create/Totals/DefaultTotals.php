<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Totals;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Default Total Row Renderer
 *
 * @author Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class DefaultTotals extends \Magento\Sales\Block\Adminhtml\Order\Create\Totals
{
    /**
     * Template
     *
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'Magento_Sales::order/create/totals/default.phtml';

    /**
     * @var PriceCurrencyInterface
     * @since 2.0.0
     */
    protected $priceCurrency;

    /**
     * Retrieve quote session object
     *
     * @return \Magento\Backend\Model\Session\Quote
     * @since 2.0.0
     */
    protected function _getSession()
    {
        return $this->_sessionQuote;
    }

    /**
     * Retrieve store model object
     *
     * @return \Magento\Store\Model\Store
     * @since 2.0.0
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
     * @since 2.0.0
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
