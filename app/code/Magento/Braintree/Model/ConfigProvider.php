<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model;

use Magento\Payment\Model\CcGenericConfigProvider;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\CcConfig;
use \Magento\Braintree\Model\PaymentMethod;

class ConfigProvider extends CcGenericConfigProvider
{

    /**
     * @var string[]
     */
    protected $methodCodes = [
        PaymentMethod::METHOD_CODE,
    ];

    /**
     * @var \Magento\Braintree\Model\Vault
     */
    protected $vault;

    /**
     * @var \Magento\Braintree\Model\Config\Cc
     */
    protected $config;

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
     * Url Builder
     *
     * @var \Magento\Framework\Url
     */
    protected $urlBuilder;

    /**
     * @param CcConfig $ccConfig
     * @param PaymentHelper $paymentHelper
     * @param \Magento\Braintree\Model\Vault $vault
     * @param \Magento\Braintree\Model\Config\Cc $config
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Url $urlBuilder
     * @param \Magento\Braintree\Helper\Data $dataHelper
     */
    public function __construct(
        CcConfig $ccConfig,
        PaymentHelper $paymentHelper,
        \Magento\Braintree\Model\Vault $vault,
        \Magento\Braintree\Model\Config\Cc $config,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Url $urlBuilder,
        \Magento\Braintree\Helper\Data $dataHelper
    ) {
        parent::__construct($ccConfig, $paymentHelper);
        $this->vault = $vault;
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->urlBuilder = $urlBuilder;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Returns applicable stored cards
     *
     * @return array
     */
    public function getStoredCards()
    {
        return $this->vault->currentCustomerStoredCards();
    }

    /**
     * Retrieve available credit card types
     *
     * @return array
     */
    protected function getCcAvailableCcTypes()
    {
        return $this->dataHelper->getCcAvailableCardTypes();
    }

    /**
     * If card can be saved for further use
     *
     * @return boolean
     */
    public function canSaveCard()
    {
        if ($this->config->useVault() && $this->customerSession->isLoggedIn()) {
            return true;
        }
        return false;
    }

    /**
     * If 3dsecure is enabled
     *
     * @return boolean
     */
    public function show3dSecure()
    {
        if ($this->config->is3dSecureEnabled()) {
            return true;
        }
        return false;
    }

    /**
     * Get generate nonce URL
     *
     * @return string
     */
    public function getAjaxGenerateNonceUrl()
    {
        return $this->urlBuilder->getUrl('braintree/creditcard/generate', ['_secure' => true]);
    }

    /**
     * @return array|void
     */
    public function getConfig()
    {
        if (!$this->config->isActive()) {
            return [];
        }
        $config = parent::getConfig();

        $clientToken = $this->config->getClientToken();
        $useVault = $this->config->useVault();
        $selectedCardToken = null;
        $storedCardOptions = [];
        if ($useVault) {
            $storedCards = $this->getStoredCards();
            if (count($storedCards) == 0) {
                $useVault = false;
            } else {
                foreach ($storedCards as $creditCard) {
                    $storedCardOptions[] = [
                        'token' => $creditCard->token,
                        'maskedNumber' => $creditCard->maskedNumber . ' - ' . $creditCard->cardType,
                        'selected' => $creditCard->default,
                        'type' => $this->dataHelper->getCcTypeCodeByName($creditCard->cardType),
                    ];
                    if ($creditCard->default) {
                        $selectedCardToken = $creditCard->token;
                    }
                }
            }
        }
        $config = array_merge_recursive($config, [
            'payment' => [
                'braintree' => [
                    'clientToken' => $clientToken,
                    'useVault' => $useVault,
                    'canSaveCard' => $this->canSaveCard(),
                    'show3dSecure' => $this->show3dSecure(),
                    'storedCards' => $storedCardOptions,
                    'selectedCardToken' => $selectedCardToken,
                    'creditCardExpMonth' => (int)$this->dataHelper->getTodayMonth(),
                    'creditCardExpYear' => (int)$this->dataHelper->getTodayYear(),
                    'countrySpecificCardTypes' => $this->config->getCountrySpecificCardTypeConfig(),
                    'isFraudDetectionEnabled' => $this->config->isFraudDetectionEnabled(),
                    'isCcDetectionEnabled' => $this->config->isCcDetectionEnabled(),
                    'availableCardTypes' => $this->getCcAvailableCcTypes(),
                    'braintreeDataJs'=> $this->config->getBraintreeDataJs(),
                    'ajaxGenerateNonceUrl' => $this->getAjaxGenerateNonceUrl()
                ],
            ],
        ]);

        return $config;
    }
}
