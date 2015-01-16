<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * One page checkout processing model
 */
namespace Magento\Checkout\Model\Type;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressMetadataInterface as AddressMetadata;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressDataBuilder as AddressBuilder;
use Magento\Customer\Api\Data\CustomerDataBuilder as CustomerBuilder;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Model\Metadata\Form;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

class Onepage
{
    /**
     * Checkout types: Checkout as Guest, Register, Logged In Customer
     */
    const METHOD_GUEST    = 'guest';
    const METHOD_REGISTER = 'register';
    const METHOD_CUSTOMER = 'customer';
    const USE_FOR_SHIPPING = 1;
    const NOT_USE_FOR_SHIPPING = 0;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\Quote
     */
    protected $_quote = null;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * Customer url
     *
     * @var \Magento\Customer\Model\Url
     */
    protected $_customerUrl;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $_customrAddrFactory;

    /**
     * @var \Magento\Customer\Model\FormFactory
     */
    protected $_customerFormFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Sales\Model\Service\QuoteFactory
     */
    protected $_serviceQuoteFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Framework\Object\Copy
     */
    protected $_objectCopyService;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    protected $_formFactory;

    /**
     * @var CustomerBuilder
     */
    protected $_customerBuilder;

    /**
     * @var AddressBuilder
     */
    protected $_addressBuilder;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * @var \Magento\Sales\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Checkout\Helper\Data $helper
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Customer\Model\AddressFactory $customrAddrFactory
     * @param \Magento\Customer\Model\FormFactory $customerFormFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Sales\Model\Service\QuoteFactory $serviceQuoteFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Object\Copy $objectCopyService
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Customer\Model\Metadata\FormFactory $formFactory
     * @param CustomerBuilder $customerBuilder
     * @param AddressBuilder $addressBuilder
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param AddressRepositoryInterface $addressRepository
     * @param AccountManagementInterface $accountManagement
     * @param OrderSender $orderSender
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Sales\Model\QuoteRepository $quoteRepository
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Checkout\Helper\Data $helper,
        \Magento\Customer\Model\Url $customerUrl,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Customer\Model\AddressFactory $customrAddrFactory,
        \Magento\Customer\Model\FormFactory $customerFormFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Sales\Model\Service\QuoteFactory $serviceQuoteFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Object\Copy $objectCopyService,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\Metadata\FormFactory $formFactory,
        CustomerBuilder $customerBuilder,
        AddressBuilder $addressBuilder,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        AddressRepositoryInterface $addressRepository,
        AccountManagementInterface $accountManagement,
        OrderSender $orderSender,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\QuoteRepository $quoteRepository,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->_eventManager = $eventManager;
        $this->_customerUrl = $customerUrl;
        $this->_helper = $helper;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_logger = $logger;
        $this->_storeManager = $storeManager;
        $this->_request = $request;
        $this->_customrAddrFactory = $customrAddrFactory;
        $this->_customerFormFactory = $customerFormFactory;
        $this->_customerFactory = $customerFactory;
        $this->_serviceQuoteFactory = $serviceQuoteFactory;
        $this->_orderFactory = $orderFactory;
        $this->_objectCopyService = $objectCopyService;
        $this->messageManager = $messageManager;
        $this->_formFactory = $formFactory;
        $this->_customerBuilder = $customerBuilder;
        $this->_addressBuilder = $addressBuilder;
        $this->mathRandom = $mathRandom;
        $this->_encryptor = $encryptor;
        $this->addressRepository = $addressRepository;
        $this->accountManagement = $accountManagement;
        $this->orderSender = $orderSender;
        $this->customerRepository = $customerRepository;
        $this->quoteRepository = $quoteRepository;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * Get frontend checkout session object
     *
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckout()
    {
        return $this->_checkoutSession;
    }

    /**
     * Quote object getter
     *
     * @return \Magento\Sales\Model\Quote
     */
    public function getQuote()
    {
        if ($this->_quote === null) {
            return $this->_checkoutSession->getQuote();
        }
        return $this->_quote;
    }

    /**
     * Declare checkout quote instance
     *
     * @param \Magento\Sales\Model\Quote $quote
     * @return $this
     */
    public function setQuote(\Magento\Sales\Model\Quote $quote)
    {
        $this->_quote = $quote;
        return $this;
    }

    /**
     * Get customer session object
     *
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession()
    {
        return $this->_customerSession;
    }

    /**
     * Initialize quote state to be valid for one page checkout
     *
     * @return $this
     */
    public function initCheckout()
    {
        $checkout = $this->getCheckout();
        $customerSession = $this->getCustomerSession();
        if (is_array($checkout->getStepData())) {
            foreach ($checkout->getStepData() as $step => $data) {
                if (!($step === 'login' || $customerSession->isLoggedIn() && $step === 'billing')) {
                    $checkout->setStepData($step, 'allow', false);
                }
            }
        }

        $quote = $this->getQuote();
        if ($quote->isMultipleShippingAddresses()) {
            $quote->removeAllAddresses();
            $this->quoteRepository->save($quote);
        }

        /*
         * want to load the correct customer information by assigning to address
         * instead of just loading from sales/quote_address
         */
        $customer = $customerSession->getCustomerDataObject();
        if ($customer) {
            $quote->assignCustomer($customer);
        }
        return $this;
    }

    /**
     * Get quote checkout method
     *
     * @return string
     */
    public function getCheckoutMethod()
    {
        if ($this->getCustomerSession()->isLoggedIn()) {
            return self::METHOD_CUSTOMER;
        }
        if (!$this->getQuote()->getCheckoutMethod()) {
            if ($this->_helper->isAllowedGuestCheckout($this->getQuote())) {
                $this->getQuote()->setCheckoutMethod(self::METHOD_GUEST);
            } else {
                $this->getQuote()->setCheckoutMethod(self::METHOD_REGISTER);
            }
        }
        return $this->getQuote()->getCheckoutMethod();
    }

    /**
     * Specify checkout method
     *
     * @param   string $method
     * @return  array
     */
    public function saveCheckoutMethod($method)
    {
        if (empty($method)) {
            return ['error' => -1, 'message' => __('Invalid data')];
        }

        $this->quoteRepository->save($this->getQuote()->setCheckoutMethod($method));
        $this->getCheckout()->setStepData('billing', 'allow', true);
        return [];
    }

    /**
     * Save billing address information to quote
     * This method is called by One Page Checkout JS (AJAX) while saving the billing information.
     *
     * @param   array $data
     * @param   int $customerAddressId
     * @return  array
     */
    public function saveBilling($data, $customerAddressId)
    {
        if (empty($data)) {
            return ['error' => -1, 'message' => __('Invalid data')];
        }

        $address = $this->getQuote()->getBillingAddress();
        $addressForm = $this->_formFactory->create(
            AddressMetadata::ENTITY_TYPE_ADDRESS,
            'customer_address_edit',
            [],
            $this->_request->isAjax(),
            Form::IGNORE_INVISIBLE,
            []
        );

        if ($customerAddressId) {
            try {
                $customerAddress = $this->addressRepository->getById($customerAddressId);
                if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
                    return ['error' => 1, 'message' => __('The customer address is not valid.')];
                }
                $address->importCustomerAddressData($customerAddress)->setSaveInAddressBook(0);
            } catch (\Exception $e) {
                return ['error' => 1, 'message' => __('Address does not exist.')];
            }
        } else {
            // emulate request object
            $addressData = $addressForm->extractData($addressForm->prepareRequest($data));
            $addressErrors = $addressForm->validateData($addressData);
            if ($addressErrors !== true) {
                return ['error' => 1, 'message' => array_values($addressErrors)];
            }
            $address->addData($addressForm->compactData($addressData));
            //unset billing address attributes which were not shown in form
            foreach ($addressForm->getAttributes() as $attribute) {
                if (!isset($data[$attribute->getAttributeCode()])) {
                    $address->setData($attribute->getAttributeCode(), null);
                }
            }
            $address->setCustomerAddressId(null);
            // Additional form data, not fetched by extractData (as it fetches only attributes)
            $address->setSaveInAddressBook(empty($data['save_in_address_book']) ? 0 : 1);
            $this->getQuote()->setBillingAddress($address);
        }

        // validate billing address
        if (($validateRes = $address->validate()) !== true) {
            return ['error' => 1, 'message' => $validateRes];
        }

        if (true !== ($result = $this->_validateCustomerData($data))) {
            return $result;
        } else {
            /** Even though _validateCustomerData should not modify data, it does */
            $address = $this->getQuote()->getBillingAddress();
        }

        if (!$this->getQuote()->getCustomerId() && $this->isCheckoutMethodRegister()) {
            if ($this->_customerEmailExists($address->getEmail(), $this->_storeManager->getWebsite()->getId())) {
                return [
                    'error' => 1,
                    // @codingStandardsIgnoreStart
                    'message' => __(
                        'There is already a registered customer using this email address. Please log in using this email address or enter a different email address to register your account.'
                    )
                    // @codingStandardsIgnoreEnd
                ];
            }
        }

        if (!$this->getQuote()->isVirtual()) {
            /**
             * Billing address using options
             */
            $usingCase = isset($data['use_for_shipping']) ? (int)$data['use_for_shipping'] : self::NOT_USE_FOR_SHIPPING;

            switch ($usingCase) {
                case self::NOT_USE_FOR_SHIPPING:
                    $shipping = $this->getQuote()->getShippingAddress();
                    $shipping->setSameAsBilling(0);
                    $shipping->save();
                    break;
                case self::USE_FOR_SHIPPING:
                    $billing = clone $address;
                    $billing->unsAddressId()->unsAddressType();
                    $shipping = $this->getQuote()->getShippingAddress();
                    $shippingMethod = $shipping->getShippingMethod();

                    // Billing address properties that must be always copied to shipping address
                    $requiredBillingAttributes = ['customer_address_id'];

                    // don't reset original shipping data, if it was not changed by customer
                    foreach ($shipping->getData() as $shippingKey => $shippingValue) {
                        if (!is_null(
                            $shippingValue
                        ) && !is_null(
                            $billing->getData($shippingKey)
                        ) && !isset(
                            $data[$shippingKey]
                        ) && !in_array(
                            $shippingKey,
                            $requiredBillingAttributes
                        )
                        ) {
                            $billing->unsetData($shippingKey);
                        }
                    }
                    $shipping->addData(
                        $billing->getData()
                    )->setSameAsBilling(
                        1
                    )->setSaveInAddressBook(
                        0
                    )->setShippingMethod(
                        $shippingMethod
                    )->setCollectShippingRates(
                        true
                    )->collectTotals();
                    if (!$this->isCheckoutMethodRegister()) {
                        $shipping->save();
                    }
                    $this->getCheckout()->setStepData('shipping', 'complete', true);
                    break;
            }
        }

        if ($this->isCheckoutMethodRegister()) {
            $this->quoteRepository->save($this->getQuote());
        } else {
            $address->save();
        }

        $this->getCheckout()->setStepData(
            'billing',
            'allow',
            true
        )->setStepData(
            'billing',
            'complete',
            true
        )->setStepData(
            'shipping',
            'allow',
            true
        );

        return [];
    }

    /**
     * Check whether checkout method is "register"
     *
     * @return bool
     */
    protected function isCheckoutMethodRegister()
    {
        return $this->getQuote()->getCheckoutMethod() == self::METHOD_REGISTER;
    }

    /**
     * Validate customer data and set some its data for further usage in quote
     *
     * Will return either true or array with error messages
     *
     * @param array $data
     * @return bool|array
     */
    protected function _validateCustomerData(array $data)
    {
        $quote = $this->getQuote();
        $isCustomerNew = !$quote->getCustomerId();
        $customer = $quote->getCustomer();
        $customerData = $this->extensibleDataObjectConverter->toFlatArray($customer, [], '\Magento\Customer\Api\Data\CustomerInterface');

        /** @var Form $customerForm */
        $customerForm = $this->_formFactory->create(
            \Magento\Customer\Api\CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            'checkout_register',
            $customerData,
            $this->_request->isAjax(),
            Form::IGNORE_INVISIBLE,
            []
        );

        if ($isCustomerNew) {
            $customerRequest = $customerForm->prepareRequest($data);
            $customerData = $customerForm->extractData($customerRequest);
        }

        $customerErrors = $customerForm->validateData($customerData);
        if ($customerErrors !== true) {
            return ['error' => -1, 'message' => implode(', ', $customerErrors)];
        }

        if (!$isCustomerNew) {
            return true;
        }

        $this->_customerBuilder->populateWithArray($customerData);
        $customer = $this->_customerBuilder->create();

        if ($quote->getCheckoutMethod() == self::METHOD_REGISTER) {
            // We always have $customerRequest here, otherwise we would have been kicked off the function several
            // lines above
            $password = $customerRequest->getParam('customer_password');
            if ($password != $customerRequest->getParam('confirm_password')) {
                return [
                    'error'   => -1,
                    'message' => __('Password and password confirmation are not equal.')
                ];
            }
            $quote->setPasswordHash($this->accountManagement->getPasswordHash($password));
        } else {
            // set NOT LOGGED IN group id explicitly,
            // otherwise copyFieldsetToTarget('customer_account', 'to_quote') will fill it with default group id value
            $this->_customerBuilder->populate($customer);
            $this->_customerBuilder->setGroupId(GroupInterface::NOT_LOGGED_IN_ID);
            $customer = $this->_customerBuilder->create();
        }

        //validate customer
        $result = $this->accountManagement->validate($customer);
        if (!$result->isValid()) {
            return [
                'error' => -1,
                'message' => implode(', ', $result->getMessages())
            ];
        }

        // copy customer/guest email to address
        $quote->getBillingAddress()->setEmail($customer->getEmail());

        // copy customer data to quote
        $this->_objectCopyService->copyFieldsetToTarget(
            'customer_account',
            'to_quote',
            $this->extensibleDataObjectConverter->toFlatArray($customer, [], '\Magento\Customer\Api\Data\CustomerInterface'),
            $quote
        );

        return true;
    }

    /**
     * Save checkout shipping address
     *
     * @param   array $data
     * @param   int $customerAddressId
     * @return  array
     */
    public function saveShipping($data, $customerAddressId)
    {
        if (empty($data)) {
            return ['error' => -1, 'message' => __('Invalid data')];
        }
        $address = $this->getQuote()->getShippingAddress();

        $addressForm = $this->_formFactory->create(
            'customer_address',
            'customer_address_edit',
            [],
            $this->_request->isAjax(),
            Form::IGNORE_INVISIBLE,
            []
        );

        if (!empty($customerAddressId)) {
            $addressData = null;
            try {
                $addressData = $this->addressRepository->getById($customerAddressId);
            } catch (NoSuchEntityException $e) {
                // do nothing if customer is not found by id
            }

            if ($addressData->getCustomerId() != $this->getQuote()->getCustomerId()) {
                return ['error' => 1, 'message' => __('The customer address is not valid.')];
            }

            $address->importCustomerAddressData($addressData)->setSaveInAddressBook(0);
            $addressErrors = $addressForm->validateData($address->getData());
            if ($addressErrors !== true) {
                return ['error' => 1, 'message' => $addressErrors];
            }
        } else {
            // emulate request object
            $addressData = $addressForm->extractData($addressForm->prepareRequest($data));
            $addressErrors = $addressForm->validateData($addressData);
            if ($addressErrors !== true) {
                return ['error' => 1, 'message' => $addressErrors];
            }
            $compactedData = $addressForm->compactData($addressData);
            // unset shipping address attributes which were not shown in form
            foreach ($addressForm->getAttributes() as $attribute) {
                $attributeCode = $attribute->getAttributeCode();
                if (!isset($data[$attributeCode])) {
                    $address->setData($attributeCode, null);
                } else {
                    $address->setDataUsingMethod($attributeCode, $compactedData[$attributeCode]);
                }
            }

            $address->setCustomerAddressId(null);
            // Additional form data, not fetched by extractData (as it fetches only attributes)
            $address->setSaveInAddressBook(empty($data['save_in_address_book']) ? 0 : 1);
            $address->setSameAsBilling(empty($data['same_as_billing']) ? 0 : 1);
        }

        $address->setCollectShippingRates(true);

        if (($validateRes = $address->validate()) !== true) {
            return ['error' => 1, 'message' => $validateRes];
        }

        $address->collectTotals()->save();

        $this->getCheckout()->setStepData('shipping', 'complete', true)->setStepData('shipping_method', 'allow', true);

        return [];
    }

    /**
     * Specify quote shipping method
     *
     * @param   string $shippingMethod
     * @return  array
     */
    public function saveShippingMethod($shippingMethod)
    {
        if (empty($shippingMethod)) {
            return ['error' => -1, 'message' => __('Invalid shipping method')];
        }
        $shippingAddress = $this->getQuote()->getShippingAddress();
        $rate = $shippingAddress->getShippingRateByCode($shippingMethod);
        if (!$rate) {
            return ['error' => -1, 'message' => __('Invalid shipping method')];
        } else {
            $shippingDescription = $rate->getCarrierTitle() . ' - ' . $rate->getMethodTitle();
            $shippingAddress->setShippingDescription(trim($shippingDescription, ' -'));
        }
        $shippingAddress->setShippingMethod($shippingMethod)->save();

        $this->getCheckout()->setStepData('shipping_method', 'complete', true)->setStepData('payment', 'allow', true);

        return [];
    }

    /**
     * Specify quote payment method
     *
     * @param   array $data
     * @return  array
     */
    public function savePayment($data)
    {
        if (empty($data)) {
            return ['error' => -1, 'message' => __('Invalid data')];
        }
        $quote = $this->getQuote();

        // shipping totals may be affected by payment method
        if (!$quote->isVirtual() && $quote->getShippingAddress()) {
            $quote->getShippingAddress()->setCollectShippingRates(true);
        }

        $data['checks'] = [
            \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_CHECKOUT,
            \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_COUNTRY,
            \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_CURRENCY,
            \Magento\Payment\Model\Method\AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
            \Magento\Payment\Model\Method\AbstractMethod::CHECK_ZERO_TOTAL,
        ];

        $payment = $quote->getPayment();
        $payment->importData($data);

        $this->quoteRepository->save($quote);

        $this->getCheckout()->setStepData('payment', 'complete', true)->setStepData('review', 'allow', true);

        return [];
    }

    /**
     * Validate quote state to be integrated with one page checkout process
     *
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    protected function validate()
    {
        $quote = $this->getQuote();

        if ($quote->isMultipleShippingAddresses()) {
            throw new \Magento\Framework\Model\Exception(__('There are more than one shipping address.'));
        }

        if ($quote->getCheckoutMethod() == self::METHOD_GUEST && !$this->_helper->isAllowedGuestCheckout($quote)) {
            throw new \Magento\Framework\Model\Exception(__('Sorry, guest checkout is not enabled.'));
        }
    }

    /**
     * Prepare quote for guest checkout order submit
     *
     * @return $this
     */
    protected function _prepareGuestQuote()
    {
        $quote = $this->getQuote();
        $quote->setCustomerId(null)
            ->setCustomerEmail($quote->getBillingAddress()->getEmail())
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(GroupInterface::NOT_LOGGED_IN_ID);
        return $this;
    }

    /**
     * Prepare quote for customer registration and customer order submit
     *
     * @return void
     */
    protected function _prepareNewCustomerQuote()
    {
        $quote = $this->getQuote();
        $billing = $quote->getBillingAddress();
        $shipping = $quote->isVirtual() ? null : $quote->getShippingAddress();

        $customer = $quote->getCustomer();
        $customerBillingData = $billing->exportCustomerAddress();
        $dataArray = $this->_objectCopyService->getDataFromFieldset('checkout_onepage_quote', 'to_customer', $quote);
        $customer = $this->_customerBuilder->mergeDataObjectWithArray($customer, $dataArray);
        $quote->setCustomer($customer->create())->setCustomerId(true);

        $customerBillingData = $this->_addressBuilder->populate(
            $customerBillingData
        )->setDefaultBilling(
            true
        )->create();

        if ($shipping) {
            if (!$shipping->getSameAsBilling()) {
                $customerShippingData = $shipping->exportCustomerAddress();
                $customerShippingData = $this->_addressBuilder->populate($customerShippingData)
                    ->setDefaultShipping(true)
                    ->create();
                $shipping->setCustomerAddressData($customerShippingData);
                // Add shipping address to quote since customer Data Object does not hold address information
                $quote->addCustomerAddress($customerShippingData);
            } else {
                $shipping->setCustomerAddressData($customerBillingData);
                $customerBillingData = $this->_addressBuilder->populate($customerBillingData)
                    ->setDefaultShipping(true)
                    ->create();
            }
        } else {
            $customerBillingData = $this->_addressBuilder->populate($customerBillingData)
                ->setDefaultShipping(true)
                ->create();
        }
        $billing->setCustomerAddressData($customerBillingData);
        // TODO : Eventually need to remove this legacy hack
        // Add billing address to quote since customer Data Object does not hold address information
        $quote->addCustomerAddress($customerBillingData);
    }

    /**
     * Prepare quote for customer order submit
     *
     * @return void
     */
    protected function _prepareCustomerQuote()
    {
        $quote = $this->getQuote();
        $billing = $quote->getBillingAddress();
        $shipping = $quote->isVirtual() ? null : $quote->getShippingAddress();

        $customer = $this->customerRepository->getById($this->getCustomerSession()->getCustomerId());
        $hasDefaultBilling = (bool)$customer->getDefaultBilling();
        $hasDefaultShipping = (bool)$customer->getDefaultShipping();

        if ($shipping && !$shipping->getSameAsBilling() &&
            (!$shipping->getCustomerId() || $shipping->getSaveInAddressBook())
        ) {
            $shippingAddress = $shipping->exportCustomerAddress();
            if (!$hasDefaultShipping) {
                //Make provided address as default shipping address
                $shippingAddress = $this->_addressBuilder
                    ->populate($shippingAddress)
                    ->setDefaultShipping(true)
                    ->create();
                $hasDefaultShipping = true;
            }
            $quote->addCustomerAddress($shippingAddress);
            $shipping->setCustomerAddressData($shippingAddress);
        }

        if (!$billing->getCustomerId() || $billing->getSaveInAddressBook()) {
            $billingAddress = $billing->exportCustomerAddress();
            if (!$hasDefaultBilling) {
                //Make provided address as default shipping address
                $this->_addressBuilder->populate($billingAddress);
                if (!$hasDefaultShipping) {
                    //Make provided address as default shipping address
                    $this->_addressBuilder->setDefaultShipping(true);
                }
                $this->_addressBuilder->setDefaultBilling(true);
                $billingAddress = $this->_addressBuilder->create();
            }
            $quote->addCustomerAddress($billingAddress);
            $billing->setCustomerAddressData($billingAddress);
        }
    }

    /**
     * Involve new customer to system
     *
     * @return $this
     */
    protected function _involveNewCustomer()
    {
        $customer = $this->getQuote()->getCustomer();
        $confirmationStatus = $this->accountManagement->getConfirmationStatus($customer->getId());
        if ($confirmationStatus === \Magento\Customer\Model\AccountManagement::ACCOUNT_CONFIRMATION_REQUIRED) {
            $url = $this->_customerUrl->getEmailConfirmationUrl($customer->getEmail());
            $this->messageManager->addSuccess(
                // @codingStandardsIgnoreStart
                __(
                    'Account confirmation is required. Please, check your e-mail for confirmation link. To resend confirmation email please <a href="%1">click here</a>.',
                    $url
                )
                // @codingStandardsIgnoreEnd
            );
        } else {
            $this->getCustomerSession()->loginById($customer->getId());
        }
        return $this;
    }

    /**
     * Create order based on checkout type. Create customer if necessary.
     *
     * @return $this
     */
    public function saveOrder()
    {
        $this->validate();
        $isNewCustomer = false;
        switch ($this->getCheckoutMethod()) {
            case self::METHOD_GUEST:
                $this->_prepareGuestQuote();
                break;
            case self::METHOD_REGISTER:
                $this->_prepareNewCustomerQuote();
                $isNewCustomer = true;
                break;
            default:
                $this->_prepareCustomerQuote();
                break;
        }

        /** @var \Magento\Sales\Model\Service\Quote $quoteService */
        $quoteService = $this->_serviceQuoteFactory->create(['quote' => $this->getQuote()]);
        $quoteService->submitAllWithDataObject();

        if ($isNewCustomer) {
            try {
                $this->_involveNewCustomer();
            } catch (\Exception $e) {
                $this->_logger->critical($e);
            }
        }

        $this->_checkoutSession->setLastQuoteId(
            $this->getQuote()->getId()
        )->setLastSuccessQuoteId(
            $this->getQuote()->getId()
        )->clearHelperData();

        $order = $quoteService->getOrder();
        if ($order) {
            $this->_eventManager->dispatch(
                'checkout_type_onepage_save_order_after',
                ['order' => $order, 'quote' => $this->getQuote()]
            );

            /**
             * a flag to set that there will be redirect to third party after confirmation
             */
            $redirectUrl = $this->getQuote()->getPayment()->getOrderPlaceRedirectUrl();
            /**
             * we only want to send to customer about new order when there is no redirect to third party
             */
            if (!$redirectUrl && $order->getCanSendNewEmailFlag()) {
                try {
                    $this->orderSender->send($order);
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                }
            }

            // add order information to the session
            $this->_checkoutSession->setLastOrderId(
                $order->getId()
            )->setRedirectUrl(
                $redirectUrl
            )->setLastRealOrderId(
                $order->getIncrementId()
            );
        }

        $this->_eventManager->dispatch(
            'checkout_submit_all_after',
            ['order' => $order, 'quote' => $this->getQuote()]
        );

        return $this;
    }

    /**
     * Check if customer email exists
     *
     * @param string $email
     * @param int $websiteId
     * @return false|\Magento\Customer\Model\Customer
     */
    protected function _customerEmailExists($email, $websiteId = null)
    {
        return !$this->accountManagement->isEmailAvailable($email, $websiteId);
    }

    /**
     * Get last order increment id by order id
     *
     * @return string
     */
    public function getLastOrderId()
    {
        $lastId = $this->getCheckout()->getLastOrderId();
        $orderId = false;
        if ($lastId) {
            $order = $this->_orderFactory->create();
            $order->load($lastId);
            $orderId = $order->getIncrementId();
        }
        return $orderId;
    }
}
