<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model;

use Magento\Braintree\Model\Adapter\BraintreeCreditCard;
use Magento\Braintree\Model\Adapter\BraintreePaymentMethod;
use \Braintree_Exception;
use Magento\Braintree\Model\Adapter\BraintreeCustomer;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Vault
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Vault
{
    const CACHE_KEY_CREDIT_CARDS = 'braintree_cc';

    /**
     * @var \Magento\Braintree\Helper\Data
     */
    protected $braintreeHelper;

    /**
     * @var \Magento\Braintree\Helper\Error
     */
    protected $errorHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $countryFactory;

    /**
     * @var \Magento\Framework\App\Cache\Type\Collection
     */
    protected $cache;

    /**
     * @var \Magento\Braintree\Model\Config\Cc
     */
    protected $config;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var BraintreeCustomer
     */
    protected $braintreeCustomer;

    /**
     * @var BraintreeCreditCard
     */
    protected $braintreeCreditCard;

    /**
     * @var BraintreePaymentMethod
     */
    protected $braintreePaymentMethod;

    /**
     * @param \Magento\Braintree\Helper\Data $braintreeHelper
     * @param \Magento\Braintree\Model\Config\Cc $config
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Braintree\Helper\Error $errorHelper
     * @param \Magento\Framework\App\Cache\Type\Collection $cache
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param BraintreeCustomer $braintreeCustomer
     * @param BraintreeCreditCard $braintreeCreditCard
     * @param BraintreePaymentMethod $braintreePaymentMethod
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Braintree\Helper\Data $braintreeHelper,
        \Magento\Braintree\Model\Config\Cc $config,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Braintree\Helper\Error $errorHelper,
        \Magento\Framework\App\Cache\Type\Collection $cache,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        BraintreeCustomer $braintreeCustomer,
        BraintreeCreditCard $braintreeCreditCard,
        BraintreePaymentMethod $braintreePaymentMethod,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryFactory
    ) {
        $this->config = $config;
        $this->braintreeHelper = $braintreeHelper;
        $this->logger = $logger;
        $this->errorHelper = $errorHelper;
        $this->cache = $cache;
        $this->customerSession = $customerSession;
        $this->customerFactory = $customerFactory;
        $this->braintreeCustomer = $braintreeCustomer;
        $this->braintreeCreditCard = $braintreeCreditCard;
        $this->braintreePaymentMethod = $braintreePaymentMethod;
        $this->countryFactory = $countryFactory;
    }

    /**
     * Array of customer credit cards
     *
     * @return array
     */
    public function currentCustomerStoredCards()
    {
        if ($this->config->useVault() && $this->customerSession->isLoggedIn()) {
            $customer = $this->customerFactory->create()->load($this->customerSession->getCustomerId());
            $customerId = $this->braintreeHelper->generateCustomerId(
                $this->customerSession->getCustomerId(),
                $customer->getEmail()
            );
            try {
                $ret = $this->braintreeCustomer->find($customerId)->creditCards;
                $this->debug($customerId);
                $this->debug($ret);
                return $ret;
            } catch (\Braintree_Exception $e) {
                return [];
            }
        }
        return [];
    }

    /**
     * Returns stored card by token
     *
     * @param string $token
     * @return \Braintree_CreditCard|null
     */
    public function storedCard($token)
    {
        try {
            $ret = $this->braintreeCreditCard->find($token);
            $this->debug($token);
            $this->debug($ret);
            return $ret;
        } catch (\Braintree_Exception $e) {
            $this->logger->critical($e);
        }
        return null;
    }

    /**
     * @param string $last4
     * @return bool
     */
    public function canSaveCard($last4)
    {
        if (!isset($last4) || !preg_match("/[0-9]{4}/", $last4)) {
            return false;
        }
        if (!$this->config->allowDuplicateCards()) {
            $storedCards = $this->currentCustomerStoredCards();
            if (is_array($storedCards)) {
                foreach ($storedCards as $card) {
                    if ($card->last4 == $last4) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Deletes customer
     *
     * @param int $customerID
     * @return $this
     */
    public function deleteCustomer($customerID)
    {
        try {
            $this->braintreeCustomer->delete($customerID);
        } catch (\Braintree_Exception $e) {
            $this->logger->critical($e);
        }
        return $this;
    }

    /**
     * Delete card by token
     *
     * @param string $token
     * @return \Braintree_CreditCard|bool
     */
    public function deleteCard($token)
    {
        try {
            $ret = $this->braintreeCreditCard->delete($token);
            $this->debug($token);
            $this->debug($ret);
            return $ret;
        } catch (\Braintree_Exception $e) {
            $this->logger->critical($e);
            return false;
        }
    }

    /**
     * If customer exists in Braintree
     *
     * @param int $customerId
     * @return bool
     */
    public function exists($customerId)
    {
        try {
            $this->braintreeCustomer->find($customerId);
        } catch (\Braintree_Exception $e) {
            $this->logger->critical($e);
            return false;
        }
        return true;
    }

    /**
     * Gets response from braintree api using the nonce
     *
     * @param string|null $nonce
     * @param array|null $options
     * @param array|null $billingAddress
     * @return $this
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function processNonce($nonce = null, $options = null, $billingAddress = null)
    {
        $customerId = $this->customerSession->getCustomerId();
        if (!$customerId) {
            throw new LocalizedException(__('Invalid Customer ID provided'));
        }
        $billingCountry = null;
        if (is_array($billingAddress)) {
            $collection = $this->countryFactory->create()->addCountryCodeFilter($billingAddress['countryCodeAlpha2']);
            if ($collection->getSize()) {
                $billingCountry = $collection->getFirstItem()->getId();
            }

            if (!$this->config->canUseForCountry($billingCountry)) {
                throw new LocalizedException(__('Selected payment type is not allowed for billing country.'));
            }
        }

        $ccType = null;
        if ($options) {
            $ccType = $options['ccType'];
        }
        if ($ccType) {
            $error = $this->config->canUseCcTypeForCountry($billingCountry, $ccType);
            if ($error) {
                throw new LocalizedException($error);
            }
        }

        $customer = $this->customerFactory->create()->load($customerId);
        $customerId = $this->braintreeHelper->generateCustomerId($customerId, $customer->getEmail());

        if (!$this->exists($customerId)) {
            $customerRequest = [
                'id'            => $customerId,
                'firstName'     => $billingAddress['firstName'],
                'lastName'      => $billingAddress['lastName'],
                'email'         => $this->customerSession->getCustomerDataObject()->getEmail(),
            ];
            if (strlen($billingAddress['company'])) {
                $customerRequest['company'] = $billingAddress['company'];
            }
            $result = $this->braintreeCustomer->create($customerRequest);
            if (!$result->success) {
                throw new LocalizedException($this->errorHelper->parseBraintreeError($result));
            }
        }
        //check if customerId is created on braintree
        $requestArray = [
            'customerId' => $customerId,
            'paymentMethodNonce' => $nonce,
            'options' => [
                'makeDefault' => ( $options['default'] == 'true') ? true : false,
                'failOnDuplicatePaymentMethod' => $this->config->allowDuplicateCards() == '1' ? false : true,
                'verifyCard' => $this->config->useCvv() == '1' ? true : false,
            ],
        ];
        if ($this->config->isFraudDetectionEnabled() &&
            strlen($options['device_data'])>0) {
            $requestArray['deviceData'] = $options['device_data'];
        }
        if ($options['update'] == 'true') {
            $token = $options['token'];
            unset($requestArray['customerId']);
            unset($requestArray['options']['failOnDuplicatePaymentMethod']);
            $requestArray['billingAddress'] = $billingAddress;
            $result = $this->braintreePaymentMethod->update($token, $requestArray);
            $this->debug($requestArray);
            $this->debug($result);
        } else {
            $result = $this->braintreePaymentMethod->create($requestArray);
            $this->debug($requestArray);
            $this->debug($result);
        }

        if (!$result->success) {
            throw new LocalizedException($this->errorHelper->parseBraintreeError($result));
        }
        return $this;
    }

    /**
     * Log debug data to file
     *
     * @param mixed $debugData
     * @return $this
     */
    protected function debug($debugData)
    {
        if ($this->config->isDebugEnabled() && !empty($debugData)) {
            $this->logger->debug(var_export($debugData, true));
        }
        return $this;
    }

    /**
     * @param string $token
     * @return bool|string
     */
    public function getSavedCardType($token)
    {
        $ccType = false;
        $useCache = $this->config->useVault();
        $cachedValues = $useCache ? $this->cache->load(self::CACHE_KEY_CREDIT_CARDS) : false;
        if ($cachedValues) {
            try {
                $cachedValues = unserialize($cachedValues);
            } catch (\Exception $e) {
                $cachedValues = [];
            }
            if (array_key_exists($token, $cachedValues)) {
                return $cachedValues[$token];
            }
        }

        try {
            $creditCard = $this->braintreeCreditCard->find($token);
            $this->debug($token);
            $this->debug($creditCard);
            $ccType = $this->braintreeHelper->getCcTypeCodeByName($creditCard->cardType);
            if (!empty($cachedValues)) {
                $cachedValues = array_merge($cachedValues, [$token => $ccType]);
            } else {
                $cachedValues = [$token => $ccType];
            }
            if ($useCache) {
                $this->cache->save(serialize($cachedValues), self::CACHE_KEY_CREDIT_CARDS);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return $ccType;
    }

    /**
     * @param string $token
     * @throws LocalizedException
     * @return bool|string
     */
    public function generatePaymentMethodToken($token)
    {
        $result = $this->braintreePaymentMethod->createNonce($token);
        if (!$result->success) {
            throw new LocalizedException($this->errorHelper->parseBraintreeError($result));
        }
        return $result->paymentMethodNonce->nonce;
    }
}
