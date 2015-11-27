<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Block;

use Magento\Backend\Model\Session\Quote;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Form\Cc;
use Magento\Payment\Model\Config;
use Magento\BraintreeTwo\Gateway\Config\Config as GatewayConfig;

/**
 * Class Form
 * @package Magento\BraintreeTwo\Block
 */
class Form extends Cc
{

    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $sessionQuote;

    /**
     * @var \Magento\BraintreeTwo\Gateway\Config\Config
     */
    protected $gatewayConfig;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\BraintreeTwo\Gateway\Config\Config $gatewayConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $paymentConfig,
        Quote $sessionQuote,
        GatewayConfig $gatewayConfig,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $data);
        $this->sessionQuote = $sessionQuote;
        $this->gatewayConfig = $gatewayConfig;
    }

    /**
     * Get list of available card types of order billing address country
     * @return array
     */
    public function getCcAvailableTypes()
    {
        $types = $this->_paymentConfig->getCcTypes();

        // get only available for Braintree card types
        $configCardTypes = array_fill_keys($this->gatewayConfig->getCcAvailableCardTypes(), '');
        $filteredTypes = array_intersect_key($types, $configCardTypes);

        $countryId = $this->sessionQuote->getQuote()->getBillingAddress()->getCountryId();
        $availableTypes = $this->gatewayConfig->getCountryAvailableCardTypes($countryId);
        // filter card types only if specific card types are set for country
        if (!empty($availableTypes)) {
            $availableTypes = array_fill_keys($availableTypes, '');
            $filteredTypes = array_intersect_key($filteredTypes, $availableTypes);
        }
        return $filteredTypes;
    }

    /**
     * Check if cvv validation is available
     * @return boolean
     */
    public function useCvv()
    {
        return $this->gatewayConfig->useCvv();
    }
}
