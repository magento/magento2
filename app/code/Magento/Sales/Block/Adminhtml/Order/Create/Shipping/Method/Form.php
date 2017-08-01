<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Shipping\Method;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Adminhtml sales order create shipping method form block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Form extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * Shipping rates
     *
     * @var array
     * @since 2.0.0
     */
    protected $_rates;

    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     * @since 2.0.0
     */
    protected $_taxData = null;

    /**
     * @var PriceCurrencyInterface
     * @since 2.0.0
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Tax\Helper\Data $taxData
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Tax\Helper\Data $taxData,
        array $data = []
    ) {
        $this->_taxData = $taxData;
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $data);
    }

    /**
     * Constructor
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_shipping_method_form');
    }

    /**
     * Retrieve quote shipping address model
     *
     * @return \Magento\Quote\Model\Quote\Address
     * @since 2.0.0
     */
    public function getAddress()
    {
        return $this->getQuote()->getShippingAddress();
    }

    /**
     * Retrieve array of shipping rates groups
     *
     * @return array
     * @since 2.0.0
     */
    public function getShippingRates()
    {
        if (empty($this->_rates)) {
            $this->_rates = $this->getAddress()->getGroupedAllShippingRates();
        }
        return $this->_rates;
    }

    /**
     * Rertrieve carrier name from store configuration
     *
     * @param string $carrierCode
     * @return string
     * @since 2.0.0
     */
    public function getCarrierName($carrierCode)
    {
        if ($name = $this->_scopeConfig->getValue(
            'carriers/' . $carrierCode . '/title',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        )
        ) {
            return $name;
        }
        return $carrierCode;
    }

    /**
     * Retrieve current selected shipping method
     *
     * @return string
     * @since 2.0.0
     */
    public function getShippingMethod()
    {
        return $this->getAddress()->getShippingMethod();
    }

    /**
     * Check activity of method by code
     *
     * @param string $code
     * @return bool
     * @since 2.0.0
     */
    public function isMethodActive($code)
    {
        return $code === $this->getShippingMethod();
    }

    /**
     * Retrieve rate of active shipping method
     *
     * @return \Magento\Quote\Model\Quote\Address\Rate|false
     * @since 2.0.0
     */
    public function getActiveMethodRate()
    {
        $rates = $this->getShippingRates();
        if (is_array($rates)) {
            foreach ($rates as $group) {
                foreach ($group as $rate) {
                    if ($rate->getCode() == $this->getShippingMethod()) {
                        return $rate;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Get rate request
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getIsRateRequest()
    {
        return $this->getRequest()->getParam('collect_shipping_rates');
    }

    /**
     * Get shipping price
     *
     * @param float $price
     * @param bool $flag
     * @return float
     * @since 2.0.0
     */
    public function getShippingPrice($price, $flag)
    {
        return $this->priceCurrency->convertAndFormat(
            $this->_taxData->getShippingPrice(
                $price,
                $flag,
                $this->getAddress(),
                null,
                $this->getAddress()->getQuote()->getStore()
            ),
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getQuote()->getStore()
        );
    }
}
