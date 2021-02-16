<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Model\Type;

use Magento\Checkout\Helper\Data;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory as CustomerDataFactory;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\FormFactory;
use Magento\Customer\Model\Metadata\Form;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Math\Random;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Checkout onepage
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Onepage implements OnepageInterface
{

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var Quote
     */
    protected $_quote = null;

    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * Customer url
     *
     * @var Url
     */
    protected $_customerUrl;

    /**
     * Core event manager proxy
     *
     * @var ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var AddressFactory
     */
    protected $_customrAddrFactory;

    /**
     * @var FormFactory
     */
    protected $_customerFormFactory;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var Copy
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
     * @var CustomerDataFactory
     */
    protected $customerDataFactory;

    /**
     * @var Random
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
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var CartManagementInterface
     */
    protected $quoteManagement;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var TotalsCollector
     */
    protected $totalsCollector;

    /**
     * @param ManagerInterface $eventManager
     * @param Data $helper
     * @param Url $customerUrl
     * @param LoggerInterface $logger
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     * @param AddressFactory $customrAddrFactory
     * @param FormFactory $customerFormFactory
     * @param CustomerFactory $customerFactory
     * @param OrderFactory $orderFactory
     * @param Copy $objectCopyService
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Customer\Model\Metadata\FormFactory $formFactory
     * @param CustomerDataFactory $customerDataFactory
     * @param Random $mathRandom
     * @param EncryptorInterface $encryptor
     * @param AddressRepositoryInterface $addressRepository
     * @param AccountManagementInterface $accountManagement
     * @param OrderSender $orderSender
     * @param CustomerRepositoryInterface $customerRepository
     * @param CartRepositoryInterface $quoteRepository
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param CartManagementInterface $quoteManagement
     * @param DataObjectHelper $dataObjectHelper
     * @param TotalsCollector $totalsCollector
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ManagerInterface $eventManager,
        Data $helper,
        Url $customerUrl,
        LoggerInterface $logger,
        \Magento\Checkout\Model\Session $checkoutSession,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        AddressFactory $customrAddrFactory,
        FormFactory $customerFormFactory,
        CustomerFactory $customerFactory,
        OrderFactory $orderFactory,
        Copy $objectCopyService,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\Metadata\FormFactory $formFactory,
        CustomerDataFactory $customerDataFactory,
        Random $mathRandom,
        EncryptorInterface $encryptor,
        AddressRepositoryInterface $addressRepository,
        AccountManagementInterface $accountManagement,
        OrderSender $orderSender,
        CustomerRepositoryInterface $customerRepository,
        CartRepositoryInterface $quoteRepository,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        CartManagementInterface $quoteManagement,
        DataObjectHelper $dataObjectHelper,
        TotalsCollector $totalsCollector
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
        $this->_orderFactory = $orderFactory;
        $this->_objectCopyService = $objectCopyService;
        $this->messageManager = $messageManager;
        $this->_formFactory = $formFactory;
        $this->customerDataFactory = $customerDataFactory;
        $this->mathRandom = $mathRandom;
        $this->_encryptor = $encryptor;
        $this->addressRepository = $addressRepository;
        $this->accountManagement = $accountManagement;
        $this->orderSender = $orderSender;
        $this->customerRepository = $customerRepository;
        $this->quoteRepository = $quoteRepository;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->quoteManagement = $quoteManagement;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->totalsCollector = $totalsCollector;
    }

    /**
     * Get frontend checkout session object
     *
     * @return \Magento\Checkout\Model\Session
     * @codeCoverageIgnore
     */
    public function getCheckout()
    {
        return $this->_checkoutSession;
    }

    /**
     * Quote object getter
     *
     * @return Quote
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
     * @param Quote $quote
     * @return $this
     * @codeCoverageIgnore
     */
    public function setQuote(Quote $quote)
    {
        $this->_quote = $quote;
        return $this;
    }

    /**
     * Get customer session object
     *
     * @return Session
     * @codeCoverageIgnore
     */
    public function getCustomerSession()
    {
        return $this->_customerSession;
    }

    /**
     * Initialize quote state to be valid for one page checkout
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
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
     * Check whether checkout method is "register"
     *
     * @return bool
     */
    protected function isCheckoutMethodRegister()
    {
        return $this->getQuote()->getCheckoutMethod() == self::METHOD_REGISTER;
    }

    /**
     * Save checkout shipping address
     *
     * @param   array $data
     * @param   int $customerAddressId
     * @return  array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function saveShipping($data, $customerAddressId)
    {
        if (empty($data)) {
            return ['error' => -1, 'message' => __('Invalid data')];
        }
        $address = $this->getQuote()->getShippingAddress();

        $addressForm = $this->_formFactory->create(
            \customer_address::class,
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
                    if (isset($compactedData[$attributeCode])) {
                        $address->setDataUsingMethod($attributeCode, $compactedData[$attributeCode]);
                    }
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

        $this->totalsCollector->collectAddressTotals($this->getQuote(), $address);
        $address->save();

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
            AbstractMethod::CHECK_USE_CHECKOUT,
            AbstractMethod::CHECK_USE_FOR_COUNTRY,
            AbstractMethod::CHECK_USE_FOR_CURRENCY,
            AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
            AbstractMethod::CHECK_ZERO_TOTAL,
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
     * @throws LocalizedException
     */
    protected function validate()
    {
        $quote = $this->getQuote();

        if ($quote->isMultipleShippingAddresses()) {
            throw new LocalizedException(
                __('There are more than one shipping addresses.')
            );
        }

        if ($quote->getCheckoutMethod() == self::METHOD_GUEST && !$this->_helper->isAllowedGuestCheckout($quote)) {
            throw new LocalizedException(__('Sorry, guest checkout is not available.'));
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
        $quote->setCustomerId(0)
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
        $this->dataObjectHelper->populateWithArray(
            $customer,
            $dataArray,
            CustomerInterface::class
        );
        $quote->setCustomer($customer)->setCustomerId(1);

        $customerBillingData->setIsDefaultBilling(true);

        if ($shipping) {
            if (!$shipping->getSameAsBilling()) {
                $customerShippingData = $shipping->exportCustomerAddress();
                $customerShippingData->setIsDefaultShipping(true);
                $shipping->setCustomerAddressData($customerShippingData);
                // Add shipping address to quote since customer Data Object does not hold address information
                $quote->addCustomerAddress($customerShippingData);
            } else {
                $shipping->setCustomerAddressData($customerBillingData);
                $customerBillingData->setIsDefaultShipping(true);
            }
        } else {
            $customerBillingData->setIsDefaultShipping(true);
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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
                $shippingAddress->setIsDefaultShipping(true);
                $hasDefaultShipping = true;
            }
            $quote->addCustomerAddress($shippingAddress);
            $shipping->setCustomerAddressData($shippingAddress);
        }

        if (!$billing->getCustomerId() || $billing->getSaveInAddressBook()) {
            $billingAddress = $billing->exportCustomerAddress();
            if (!$hasDefaultBilling) {
                //Make provided address as default shipping address
                if (!$hasDefaultShipping) {
                    //Make provided address as default shipping address
                    $billingAddress->setIsDefaultShipping(true);
                }
                $billingAddress->setIsDefaultBilling(true);
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
        if ($confirmationStatus === AccountManagement::ACCOUNT_CONFIRMATION_REQUIRED) {
            $url = $this->_customerUrl->getEmailConfirmationUrl($customer->getEmail());
            $this->messageManager->addSuccessMessage(
                // @codingStandardsIgnoreStart
                __(
                    'You must confirm your account. Please check your email for the confirmation link or <a href="%1">click here</a> for a new link.',
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
        $order = $this->quoteManagement->submit($this->getQuote());
        if ($isNewCustomer) {
            try {
                $this->_involveNewCustomer();
            } catch (\Exception $e) {
                $this->_logger->critical($e);
            }
        }
        $this->_checkoutSession
            ->setLastQuoteId($this->getQuote()->getId())
            ->setLastSuccessQuoteId($this->getQuote()->getId())
            ->clearHelperData();

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
            $this->_checkoutSession
                ->setLastOrderId($order->getId())
                ->setRedirectUrl($redirectUrl)
                ->setLastRealOrderId($order->getIncrementId())
                ->setLastOrderStatus($order->getStatus());
        }

        $this->_eventManager->dispatch(
            'checkout_submit_all_after',
            [
                'order' => $order,
                'quote' => $this->getQuote()
            ]
        );
        return $this;
    }

    /**
     * Check if customer email exists
     *
     * @param string $email
     * @param int $websiteId
     * @return false|Customer
     * @codeCoverageIgnore
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
