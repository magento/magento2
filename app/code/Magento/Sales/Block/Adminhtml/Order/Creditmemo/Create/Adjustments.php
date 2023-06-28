<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create;

use Magento\Framework\Currency\Data\Currency as CurrencyData;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Order;

/**
 * Credit memo adjustments block
 *
 * @api
 * @since 100.0.2
 */
class Adjustments extends \Magento\Backend\Block\Template
{
    /**
     * Source object
     *
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $_taxConfig;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Tax\Model\Config $taxConfig,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->_taxConfig = $taxConfig;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $data);
    }

    /**
     * Initialize creditmemo adjustment totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_source = $parent->getSource();
        $total = new \Magento\Framework\DataObject(['code' => 'adjustments', 'block_name' => $this->getNameInLayout()]);
        $parent->removeTotal('shipping');
        $parent->removeTotal('adjustment_positive');
        $parent->removeTotal('adjustment_negative');
        $parent->addTotal($total);
        return $this;
    }

    /**
     * Format value based on order currency
     *
     * @param null|float $value
     *
     * @return string
     * @since 102.1.0
     */
    public function formatValue($value)
    {
        /** @var Order $order */
        $order = $this->getSource()->getOrder();

        return $order->getOrderCurrency()->formatPrecision(
            $value,
            2,
            ['display' => CurrencyData::NO_SYMBOL],
            false,
            false
        );
    }

    /**
     * Get source object
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * Get credit memo shipping amount depend on configuration settings
     *
     * @return float
     */
    public function getShippingAmount()
    {
        $source = $this->getSource();
        if ($this->_taxConfig->displaySalesShippingInclTax($source->getOrder()->getStoreId())) {
            $shipping = $source->getBaseShippingInclTax();
        } else {
            $shipping = $source->getBaseShippingAmount();
        }
        return $this->priceCurrency->round($shipping) * 1;
    }

    /**
     * Get label for shipping total based on configuration settings
     *
     * @return string
     */
    public function getShippingLabel()
    {
        $source = $this->getSource();
        if ($this->_taxConfig->displaySalesShippingInclTax($source->getOrder()->getStoreId())) {
            $label = __('Refund Shipping (Incl. Tax)');
        } elseif ($this->_taxConfig->displaySalesShippingBoth($source->getOrder()->getStoreId())) {
            $label = __('Refund Shipping (Excl. Tax)');
        } else {
            $label = __('Refund Shipping');
        }
        return $label;
    }
}
