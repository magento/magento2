<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Block;

class Form extends \Magento\Payment\Block\Form\Cc
{
    /**
     * @var \Magento\Braintree\Model\Vault
     */
    protected $vault;

    /**
     * @var \Magento\Braintree\Model\Config\Cc
     */
    protected $config;

    /**
     * @var \Magento\Checkout\Model\Type\Onepage
     */
    protected $onepage;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Braintree\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $sessionQuote;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param \Magento\Braintree\Model\Vault $vault
     * @param \Magento\Braintree\Model\Config\Cc $config
     * @param \Magento\Checkout\Model\Type\Onepage $onepage
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Braintree\Helper\Data $dataHelper
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Braintree\Model\Vault $vault,
        \Magento\Braintree\Model\Config\Cc $config,
        \Magento\Checkout\Model\Type\Onepage $onepage,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Braintree\Helper\Data $dataHelper,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $data);
        $this->vault = $vault;
        $this->config = $config;
        $this->onepage = $onepage;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->dataHelper = $dataHelper;
        $this->sessionQuote = $sessionQuote;
    }

    /**
     * Internal constructor. Set template
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('form.phtml');
    }

    /**
     * Set quote and payment
     * 
     * @return $this
     */
    public function setMethodInfo()
    {
        $payment = $this->onepage
            ->getQuote()
            ->getPayment();
        $this->setMethod($payment->getMethodInstance());

        return $this;
    }

    /**
     * Returns applicable stored cards
     * 
     * @return array
     */
    public function getStoredCards()
    {
        $storedCards = $this->vault->currentCustomerStoredCards();
        $country = $this->checkoutSession->getQuote()->getBillingAddress()->getCountryId();
        $cardTypes = $this->config->getApplicableCardTypes($country);
        $applicableCards = [];
        foreach ($storedCards as $card) {
            if (in_array($this->dataHelper->getCcTypeCodeByName($card->cardType), $cardTypes)) {
                $applicableCards[] = $card;
            }
        }
        return $applicableCards;
    }

    /**
     * Retrieve availables credit card types
     *
     * @return array
     */
    public function getCcAvailableTypes()
    {
        if ($this->_appState->getAreaCode() === \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
            $country = $this->sessionQuote->getQuote()->getBillingAddress()->getCountryId();
        } else {
            $country = $this->checkoutSession->getQuote()->getBillingAddress()->getCountryId();
        }
        $applicableTypes = $this->config->getApplicableCardTypes($country);
        $types = $this->_paymentConfig->getCcTypes();
        foreach (array_keys($types) as $code) {
            if (!in_array($code, $applicableTypes)) {
                unset($types[$code]);
            }
        }
        return $types;
    }

    /**
     * If card can be saved for further use
     *
     * @return boolean
     */
    public function canSaveCard()
    {
        if ($this->config->useVault() &&
            ($this->customerSession->isLoggedIn() ||
            $this->onepage->getCheckoutMethod() == \Magento\Checkout\Model\Type\Onepage::METHOD_REGISTER)) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * @return bool
     */
    public function isCcDetectionEnabled()
    {
        return (bool)$this->config->getConfigData('enable_cc_detection');
    }

    /**
     * @return bool
     */
    public function useVault()
    {
        return (bool)$this->config->useVault();
    }

    /**
     * @return bool
     */
    public function useCvv()
    {
        return (bool)$this->config->useCvv();
    }

    /**
     * @return bool
     */
    public function is3dSecureEnabled()
    {
        return (bool)$this->config->is3dSecureEnabled();
    }

    /**
     * @return string
     */
    public function getBraintreeDataJs()
    {
        return $this->config->getBraintreeDataJs();
    }


    /**
     * If fraud detection is enabled
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function isFraudDetectionEnabled()
    {
        return $this->config->isFraudDetectionEnabled();
    }

    /**
     * Get configuration data
     *
     * @param string $path
     * @return mixed
     */
    public function getConfigData($path)
    {
        return $this->config->getConfigData($path);
    }

    /**
     * @return string
     */
    public function getClientToken()
    {
        return $this->config->getClientToken();
    }

    /**
     * Retrieve today month
     *
     * @return string
     */
    public function getTodayMonth()
    {
        return $this->dataHelper->getTodayMonth();
    }

    /**
     * Retrieve today year
     *
     * @return string
     */
    public function getTodayYear()
    {
        return $this->dataHelper->getTodayYear();
    }
}
