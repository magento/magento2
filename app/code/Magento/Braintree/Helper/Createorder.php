<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Helper;

use Magento\Braintree\Model\Adapter\BraintreeCustomer;
use \Braintree_Exception;
use \Magento\Framework\App\Helper\Context;
use \Magento\Braintree\Model\PaymentMethod;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Createorder extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONFIG_PATH_VAULT         = 'payment/braintree/use_vault';
    const CONFIG_PATH_MERCHANT_ID   = 'payment/braintree/merchant_id';

    /**
     * @var \Magento\Braintree\Helper\Data
     */
    protected $paymentHelper;

    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $sessionQuote;

    /**
     * @var BraintreeCustomer
     */
    protected $braintreeCustomerAdapter;

    /**
     * @param Context $context
     * @param \Magento\Braintree\Helper\Data $paymentHelper
     * @param BraintreeCustomer $braintreeCustomerAdapter
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     */
    public function __construct(
        Context $context,
        \Magento\Braintree\Helper\Data $paymentHelper,
        BraintreeCustomer $braintreeCustomerAdapter,
        \Magento\Backend\Model\Session\Quote $sessionQuote
    ) {
        parent::__construct($context);
        $this->paymentHelper = $paymentHelper;
        $this->braintreeCustomerAdapter = $braintreeCustomerAdapter;
        $this->sessionQuote = $sessionQuote;
    }

    /**
     * Returns customer credit cards if applicable
     * 
     * @return \Braintree_Customer|boolean
     */
    public function getLoggedInCustomerCards()
    {
        $applicableCards = [];
        $useVault = (bool)(int)$this->scopeConfig->getValue(
            self::CONFIG_PATH_VAULT,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $this->sessionQuote->getStoreId()
        );
        if ($useVault) {
            $storedCards = false;
            if ($this->sessionQuote->getCustomerId()) {
                $customerId = $this->paymentHelper->generateCustomerId(
                    $this->sessionQuote->getCustomerId(),
                    $this->sessionQuote->getQuote()->getCustomerEmail()
                );
                try {
                    $storedCards = $this->braintreeCustomerAdapter->find($customerId)->creditCards;
                } catch (\Braintree_Exception $e) {
                    $this->_logger->critical($e);
                }
            }
            if ($storedCards) {
                $country = $this->sessionQuote->getQuote()->getBillingAddress()->getCountryId();
                $cardTypes = $this->paymentHelper->getCcAvailableCardTypes($country);
                $applicableCards = [];
                foreach ($storedCards as $card) {
                    if (isset($cardTypes[$this->paymentHelper->getCcTypeCodeByName($card->cardType)])) {
                        $applicableCards[] = $card;
                    }
                }
                
            }
        }
        return $applicableCards;
    }
    
    /**
     * Returns merchant id
     *
     * @return string
     */
    public function getMerchantId()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_MERCHANT_ID, $this->sessionQuote->getStoreId());
    }
}
