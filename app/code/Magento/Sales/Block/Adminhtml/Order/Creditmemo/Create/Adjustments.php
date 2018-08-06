<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
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
     * Tax config
     *
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
     * Initialize creditmemo agjustment totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_source = $parent->getSource();
        $total = new \Magento\Framework\DataObject(['code' => 'agjustments', 'block_name' => $this->getNameInLayout()]);
        $parent->removeTotal('shipping');
        $parent->removeTotal('adjustment_positive');
        $parent->removeTotal('adjustment_negative');
        $parent->addTotal($total);
        return $this;
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

    /**
     * Get update totals url
     *
     * @return string
     */
    public function getUpdateTotalsUrl()
    {
        return $this->getUrl(
            'sales/*/updateQty',
            [
                'order_id' => $this->getSource()->getOrderId(),
                'invoice_id' => $this->getRequest()->getParam('invoice_id', null)
            ]
        );
    }
}
