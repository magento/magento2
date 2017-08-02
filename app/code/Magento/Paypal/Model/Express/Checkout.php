<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Express;

use Magento\Customer\Api\Data\CustomerInterface as CustomerDataObject;
use Magento\Customer\Model\AccountManagement;
use Magento\Framework\DataObject;
use Magento\Paypal\Model\Cart as PaypalCart;
use Magento\Paypal\Model\Config as PaypalConfig;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

/**
 * Wrapper that performs Paypal Express and Checkout communication
 * Use current Paypal Express method instance
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Checkout
{
    /**
     * Cache ID prefix for "pal" lookup
     * @var string
     */
    const PAL_CACHE_ID = 'paypal_express_checkout_pal';

    /**
     * Keys for passthrough variables in sales/quote_payment and sales/order_payment
     * Uses additional_information as storage
     */
    const PAYMENT_INFO_TRANSPORT_TOKEN    = 'paypal_express_checkout_token';
    const PAYMENT_INFO_TRANSPORT_SHIPPING_OVERRIDDEN = 'paypal_express_checkout_shipping_overridden';
    const PAYMENT_INFO_TRANSPORT_SHIPPING_METHOD = 'paypal_express_checkout_shipping_method';
    const PAYMENT_INFO_TRANSPORT_PAYER_ID = 'paypal_express_checkout_payer_id';
    const PAYMENT_INFO_TRANSPORT_REDIRECT = 'paypal_express_checkout_redirect_required';
    const PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT = 'paypal_ec_create_ba';

    /**
     * Flag which says that was used PayPal Express Checkout button for checkout
     * Uses additional_information as storage
     * @var string
     */
    const PAYMENT_INFO_BUTTON = 'button';

    /**
     * @var \Magento\Quote\Model\Quote
     * @since 2.0.0
     */
    protected $_quote;

    /**
     * Config instance
     *
     * @var PaypalConfig
     * @since 2.0.0
     */
    protected $_config;

    /**
     * API instance
     *
     * @var \Magento\Paypal\Model\Api\Nvp
     * @since 2.0.0
     */
    protected $_api;

    /**
     * Api Model Type
     *
     * @var string
     * @since 2.0.0
     */
    protected $_apiType = \Magento\Paypal\Model\Api\Nvp::class;

    /**
     * Payment method type
     *
     * @var string
     * @since 2.0.0
     */
    protected $_methodType = PaypalConfig::METHOD_WPP_EXPRESS;

    /**
     * State helper variable
     *
     * @var string
     * @since 2.0.0
     */
    protected $_redirectUrl = '';

    /**
     * State helper variable
     *
     * @var string
     * @since 2.0.0
     */
    protected $_pendingPaymentMessage = '';

    /**
     * State helper variable
     *
     * @var string
     * @since 2.0.0
     */
    protected $_checkoutRedirectUrl = '';

    /**
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $_customerSession;

    /**
     * Redirect urls supposed to be set to support giropay
     *
     * @var array
     * @since 2.0.0
     */
    protected $_giropayUrls = [];

    /**
     * Create Billing Agreement flag
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_isBARequested = false;

    /**
     * Flag for Bill Me Later mode
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_isBml = false;

    /**
     * Customer ID
     *
     * @var int
     * @since 2.0.0
     */
    protected $_customerId;

    /**
     * Billing agreement that might be created during order placing
     *
     * @var \Magento\Paypal\Model\Billing\Agreement
     * @since 2.0.0
     */
    protected $_billingAgreement;

    /**
     * Order
     *
     * @var \Magento\Sales\Model\Order
     * @since 2.0.0
     */
    protected $_order;

    /**
     * @var \Magento\Framework\App\Cache\Type\Config
     * @since 2.0.0
     */
    protected $_configCacheType;

    /**
     * Checkout data
     *
     * @var \Magento\Checkout\Helper\Data
     * @since 2.0.0
     */
    protected $_checkoutData;

    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     * @since 2.0.0
     */
    protected $_taxData;

    /**
     * Customer data
     *
     * @var \Magento\Customer\Model\Url
     * @since 2.0.0
     */
    protected $_customerUrl;

    /**
     * @var \Psr\Log\LoggerInterface
     * @since 2.0.0
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     * @since 2.0.0
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Paypal\Model\Info
     * @since 2.0.0
     */
    protected $_paypalInfo;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\UrlInterface
     * @since 2.0.0
     */
    protected $_coreUrl;

    /**
     * @var \Magento\Paypal\Model\CartFactory
     * @since 2.0.0
     */
    protected $_cartFactory;

    /**
     * @var \Magento\Checkout\Model\Type\OnepageFactory
     * @since 2.0.0
     */
    protected $_checkoutOnepageFactory;

    /**
     * @var \Magento\Paypal\Model\Billing\AgreementFactory
     * @since 2.0.0
     */
    protected $_agreementFactory;

    /**
     * @var \Magento\Paypal\Model\Api\Type\Factory
     * @since 2.0.0
     */
    protected $_apiTypeFactory;

    /**
     * @var \Magento\Framework\DataObject\Copy
     * @since 2.0.0
     */
    protected $_objectCopyService;

    /**
     * @var \Magento\Checkout\Model\Session
     * @since 2.0.0
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     * @since 2.0.0
     */
    protected $_customerRepository;

    /**
     * @var \Magento\Customer\Model\AccountManagement
     * @since 2.0.0
     */
    protected $_accountManagement;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     * @since 2.0.0
     */
    protected $_encryptor;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     * @since 2.0.0
     */
    protected $_messageManager;

    /**
     * @var OrderSender
     * @since 2.0.0
     */
    protected $orderSender;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     * @since 2.0.0
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     * @since 2.0.0
     */
    protected $quoteManagement;

    /**
     * @var \Magento\Quote\Model\Quote\TotalsCollector
     * @since 2.0.0
     */
    protected $totalsCollector;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Checkout\Helper\Data $checkoutData
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Paypal\Model\Info $paypalInfo
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $coreUrl
     * @param \Magento\Paypal\Model\CartFactory $cartFactory
     * @param \Magento\Checkout\Model\Type\OnepageFactory $onepageFactory
     * @param \Magento\Quote\Api\CartManagementInterface $quoteManagement
     * @param \Magento\Paypal\Model\Billing\AgreementFactory $agreementFactory
     * @param \Magento\Paypal\Model\Api\Type\Factory $apiTypeFactory
     * @param DataObject\Copy $objectCopyService
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param AccountManagement $accountManagement
     * @param OrderSender $orderSender
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector
     * @param array $params
     * @throws \Exception
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Paypal\Model\Info $paypalInfo,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $coreUrl,
        \Magento\Paypal\Model\CartFactory $cartFactory,
        \Magento\Checkout\Model\Type\OnepageFactory $onepageFactory,
        \Magento\Quote\Api\CartManagementInterface $quoteManagement,
        \Magento\Paypal\Model\Billing\AgreementFactory $agreementFactory,
        \Magento\Paypal\Model\Api\Type\Factory $apiTypeFactory,
        \Magento\Framework\DataObject\Copy $objectCopyService,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        AccountManagement $accountManagement,
        OrderSender $orderSender,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        $params = []
    ) {
        $this->quoteManagement = $quoteManagement;
        $this->_customerUrl = $customerUrl;
        $this->_taxData = $taxData;
        $this->_checkoutData = $checkoutData;
        $this->_configCacheType = $configCacheType;
        $this->_logger = $logger;
        $this->_localeResolver = $localeResolver;
        $this->_paypalInfo = $paypalInfo;
        $this->_storeManager = $storeManager;
        $this->_coreUrl = $coreUrl;
        $this->_cartFactory = $cartFactory;
        $this->_checkoutOnepageFactory = $onepageFactory;
        $this->_agreementFactory = $agreementFactory;
        $this->_apiTypeFactory = $apiTypeFactory;
        $this->_objectCopyService = $objectCopyService;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerRepository = $customerRepository;
        $this->_encryptor = $encryptor;
        $this->_messageManager = $messageManager;
        $this->orderSender = $orderSender;
        $this->_accountManagement = $accountManagement;
        $this->quoteRepository = $quoteRepository;
        $this->totalsCollector = $totalsCollector;
        $this->_customerSession = isset($params['session'])
            && $params['session'] instanceof \Magento\Customer\Model\Session ? $params['session'] : $customerSession;

        if (isset($params['config']) && $params['config'] instanceof PaypalConfig) {
            $this->_config = $params['config'];
        } else {
            throw new \Exception('Config instance is required.');
        }

        if (isset($params['quote']) && $params['quote'] instanceof \Magento\Quote\Model\Quote) {
            $this->_quote = $params['quote'];
        } else {
            throw new \Exception('Quote instance is required.');
        }
    }

    /**
     * Checkout with PayPal image URL getter
     * Spares API calls of getting "pal" variable, by putting it into cache per store view
     *
     * @return string
     * @since 2.0.0
     */
    public function getCheckoutShortcutImageUrl()
    {
        // get "pal" thing from cache or lookup it via API
        $pal = null;
        if ($this->_config->areButtonsDynamic()) {
            $cacheId = self::PAL_CACHE_ID . $this->_storeManager->getStore()->getId();
            $pal = $this->_configCacheType->load($cacheId);
            if (self::PAL_CACHE_ID == $pal) {
                $pal = null;
            } elseif (!$pal) {
                $pal = null;
                try {
                    $this->_getApi()->callGetPalDetails();
                    $pal = $this->_getApi()->getPal();
                    $this->_configCacheType->save($pal, $cacheId);
                } catch (\Exception $e) {
                    $this->_configCacheType->save(self::PAL_CACHE_ID, $cacheId);
                    $this->_logger->critical($e);
                }
            }
        }

        return $this->_config->getExpressCheckoutShortcutImageUrl(
            $this->_localeResolver->getLocale(),
            $this->_quote->getBaseGrandTotal(),
            $pal
        );
    }

    /**
     * Setter that enables giropay redirects flow
     *
     * @param string $successUrl - payment success result
     * @param string $cancelUrl  - payment cancellation result
     * @param string $pendingUrl - pending payment result
     * @return $this
     * @since 2.0.0
     */
    public function prepareGiropayUrls($successUrl, $cancelUrl, $pendingUrl)
    {
        $this->_giropayUrls = [$successUrl, $cancelUrl, $pendingUrl];
        return $this;
    }

    /**
     * Set create billing agreement flag
     *
     * @param bool $flag
     * @return $this
     * @since 2.0.0
     */
    public function setIsBillingAgreementRequested($flag)
    {
        $this->_isBARequested = $flag;
        return $this;
    }

    /**
     * Set flag that forces to use BillMeLater
     *
     * @param bool $isBml
     * @return $this
     * @since 2.0.0
     */
    public function setIsBml($isBml)
    {
        $this->_isBml = $isBml;
        return $this;
    }

    /**
     * Setter for customer
     *
     * @param CustomerDataObject $customerData
     * @return $this
     * @since 2.0.0
     */
    public function setCustomerData(CustomerDataObject $customerData)
    {
        $this->_quote->assignCustomer($customerData);
        $this->_customerId = $customerData->getId();
        return $this;
    }

    /**
     * Setter for customer with billing and shipping address changing ability
     *
     * @param CustomerDataObject $customerData
     * @param Address|null $billingAddress
     * @param Address|null $shippingAddress
     * @return $this
     * @since 2.0.0
     */
    public function setCustomerWithAddressChange(
        CustomerDataObject $customerData,
        $billingAddress = null,
        $shippingAddress = null
    ) {
        $this->_quote->assignCustomerWithAddressChange($customerData, $billingAddress, $shippingAddress);
        $this->_customerId = $customerData->getId();
        return $this;
    }

    /**
     * Reserve order ID for specified quote and start checkout on PayPal
     *
     * @param string $returnUrl
     * @param string $cancelUrl
     * @param bool|null $button
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @since 2.0.0
     */
    public function start($returnUrl, $cancelUrl, $button = null)
    {
        $this->_quote->collectTotals();

        if (!$this->_quote->getGrandTotal()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'PayPal can\'t process orders with a zero balance due. '
                    . 'To finish your purchase, please go through the standard checkout process.'
                )
            );
        }

        $this->_quote->reserveOrderId();
        $this->quoteRepository->save($this->_quote);
        // prepare API
        $solutionType = $this->_config->getMerchantCountry() == 'DE'
            ? \Magento\Paypal\Model\Config::EC_SOLUTION_TYPE_MARK
            : $this->_config->getValue('solutionType');
        $this->_getApi()->setAmount($this->_quote->getBaseGrandTotal())
            ->setCurrencyCode($this->_quote->getBaseCurrencyCode())
            ->setInvNum($this->_quote->getReservedOrderId())
            ->setReturnUrl($returnUrl)
            ->setCancelUrl($cancelUrl)
            ->setSolutionType($solutionType)
            ->setPaymentAction($this->_config->getValue('paymentAction'));
        if ($this->_giropayUrls) {
            list($successUrl, $cancelUrl, $pendingUrl) = $this->_giropayUrls;
            $this->_getApi()->addData(
                [
                    'giropay_cancel_url' => $cancelUrl,
                    'giropay_success_url' => $successUrl,
                    'giropay_bank_txn_pending_url' => $pendingUrl,
                ]
            );
        }

        if ($this->_isBml) {
            $this->_getApi()->setFundingSource('BML');
        }

        $this->_setBillingAgreementRequest();

        if ($this->_config->getValue('requireBillingAddress') == PaypalConfig::REQUIRE_BILLING_ADDRESS_ALL) {
            $this->_getApi()->setRequireBillingAddress(1);
        }

        // suppress or export shipping address
        $address = null;
        if ($this->_quote->getIsVirtual()) {
            if ($this->_config->getValue('requireBillingAddress')
                == PaypalConfig::REQUIRE_BILLING_ADDRESS_VIRTUAL
            ) {
                $this->_getApi()->setRequireBillingAddress(1);
            }
            $this->_getApi()->setSuppressShipping(true);
        } else {
            $this->_getApi()->setBillingAddress($this->_quote->getBillingAddress());

            $address = $this->_quote->getShippingAddress();
            $isOverridden = 0;
            if (true === $address->validate()) {
                $isOverridden = 1;
                $this->_getApi()->setAddress($address);
            }
            $this->_quote->getPayment()->setAdditionalInformation(
                self::PAYMENT_INFO_TRANSPORT_SHIPPING_OVERRIDDEN,
                $isOverridden
            );
            $this->_quote->getPayment()->save();
        }

        /** @var $cart \Magento\Payment\Model\Cart */
        $cart = $this->_cartFactory->create(['salesModel' => $this->_quote]);

        $this->_getApi()->setPaypalCart($cart);

        if (!$this->_taxData->getConfig()->priceIncludesTax()) {
            $this->setShippingOptions($cart, $address);
        }

        $this->_config->exportExpressCheckoutStyleSettings($this->_getApi());

        /* Temporary solution. @TODO: do not pass quote into Nvp model */
        $this->_getApi()->setQuote($this->_quote);
        $this->_getApi()->callSetExpressCheckout();

        $token = $this->_getApi()->getToken();

        $this->_setRedirectUrl($button, $token);

        $payment = $this->_quote->getPayment();
        $payment->unsAdditionalInformation(self::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT);
        // Set flag that we came from Express Checkout button
        if (!empty($button)) {
            $payment->setAdditionalInformation(self::PAYMENT_INFO_BUTTON, 1);
        } elseif ($payment->hasAdditionalInformation(self::PAYMENT_INFO_BUTTON)) {
            $payment->unsAdditionalInformation(self::PAYMENT_INFO_BUTTON);
        }
        $payment->save();

        return $token;
    }

    /**
     * Check whether system can skip order review page before placing order
     *
     * @return bool
     * @since 2.0.0
     */
    public function canSkipOrderReviewStep()
    {
        $isOnepageCheckout = !$this->_quote->getPayment()->getAdditionalInformation(self::PAYMENT_INFO_BUTTON);
        return $this->_config->isOrderReviewStepDisabled() && $isOnepageCheckout;
    }

    /**
     * Update quote when returned from PayPal
     * rewrite billing address by paypal
     * save old billing address for new customer
     * export shipping address in case address absence
     *
     * @param string $token
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    public function returnFromPaypal($token)
    {
        $this->_getApi()
            ->setToken($token)
            ->callGetExpressCheckoutDetails();
        $quote = $this->_quote;

        $this->ignoreAddressValidation();

        // import shipping address
        $exportedShippingAddress = $this->_getApi()->getExportedShippingAddress();
        if (!$quote->getIsVirtual()) {
            $shippingAddress = $quote->getShippingAddress();
            if ($shippingAddress) {
                if ($exportedShippingAddress
                    && $quote->getPayment()->getAdditionalInformation(self::PAYMENT_INFO_BUTTON) == 1
                ) {
                    $this->_setExportedAddressData($shippingAddress, $exportedShippingAddress);
                    // PayPal doesn't provide detailed shipping info: prefix, middlename, lastname, suffix
                    $shippingAddress->setPrefix(null);
                    $shippingAddress->setMiddlename(null);
                    $shippingAddress->setLastname(null);
                    $shippingAddress->setSuffix(null);
                    $shippingAddress->setCollectShippingRates(true);
                    $shippingAddress->setSameAsBilling(0);
                }

                // import shipping method
                $code = '';
                if ($this->_getApi()->getShippingRateCode()) {
                    $code = $this->_matchShippingMethodCode($shippingAddress, $this->_getApi()->getShippingRateCode());
                    if ($code) {
                        // possible bug of double collecting rates :-/
                        $shippingAddress->setShippingMethod($code)->setCollectShippingRates(true);
                    }
                }
                $quote->getPayment()->setAdditionalInformation(
                    self::PAYMENT_INFO_TRANSPORT_SHIPPING_METHOD,
                    $code
                );
            }
        }

        // import billing address
        $portBillingFromShipping = $quote->getPayment()->getAdditionalInformation(self::PAYMENT_INFO_BUTTON) == 1
            && $this->_config->getValue(
                'requireBillingAddress'
            ) != \Magento\Paypal\Model\Config::REQUIRE_BILLING_ADDRESS_ALL
            && !$quote->isVirtual();
        if ($portBillingFromShipping) {
            $billingAddress = clone $shippingAddress;
            $billingAddress->unsAddressId()->unsAddressType()->setCustomerAddressId(null);
            $data = $billingAddress->getData();
            $data['save_in_address_book'] = 0;
            $quote->getBillingAddress()->addData($data);
            $quote->getShippingAddress()->setSameAsBilling(1);
        } else {
            $billingAddress = $quote->getBillingAddress();
        }
        $exportedBillingAddress = $this->_getApi()->getExportedBillingAddress();

        $this->_setExportedAddressData($billingAddress, $exportedBillingAddress);
        $billingAddress->setCustomerNote($exportedBillingAddress->getData('note'));
        $quote->setBillingAddress($billingAddress);
        $quote->setCheckoutMethod($this->getCheckoutMethod());

        // import payment info
        $payment = $quote->getPayment();
        $payment->setMethod($this->_methodType);
        $this->_paypalInfo->importToPayment($this->_getApi(), $payment);
        $payment->setAdditionalInformation(self::PAYMENT_INFO_TRANSPORT_PAYER_ID, $this->_getApi()->getPayerId())
            ->setAdditionalInformation(self::PAYMENT_INFO_TRANSPORT_TOKEN, $token);
        $quote->collectTotals();
        $this->quoteRepository->save($quote);
    }

    /**
     * Check whether order review has enough data to initialize
     *
     * @param string|null $token
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function prepareOrderReview($token = null)
    {
        $payment = $this->_quote->getPayment();
        if (!$payment || !$payment->getAdditionalInformation(self::PAYMENT_INFO_TRANSPORT_PAYER_ID)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('A payer is not identified.'));
        }
        $this->_quote->setMayEditShippingAddress(
            1 != $this->_quote->getPayment()->getAdditionalInformation(self::PAYMENT_INFO_TRANSPORT_SHIPPING_OVERRIDDEN)
        );
        $this->_quote->setMayEditShippingMethod(
            '' == $this->_quote->getPayment()->getAdditionalInformation(self::PAYMENT_INFO_TRANSPORT_SHIPPING_METHOD)
        );
        $this->ignoreAddressValidation();
        $this->_quote->collectTotals();
        $this->quoteRepository->save($this->_quote);
    }

    /**
     * Return callback response with shipping options
     *
     * @param array $request
     * @return string
     * @throws \Exception
     * @since 2.0.0
     */
    public function getShippingOptionsCallbackResponse(array $request)
    {
        $debugData = ['request' => $request, 'response' => []];

        try {
            // obtain addresses
            $address = $this->_getApi()->prepareShippingOptionsCallbackAddress($request);
            $quoteAddress = $this->_quote->getShippingAddress();

            // compare addresses, calculate shipping rates and prepare response
            $options = [];
            if ($address && $quoteAddress && !$this->_quote->getIsVirtual()) {
                foreach ($address->getExportedKeys() as $key) {
                    $quoteAddress->setDataUsingMethod($key, $address->getData($key));
                }
                $quoteAddress->setCollectShippingRates(true);
                $this->totalsCollector->collectAddressTotals($this->_quote, $quoteAddress);
                $options = $this->_prepareShippingOptions($quoteAddress, false, true);
            }
            $response = $this->_getApi()->setShippingOptions($options)->formatShippingOptionsCallback();

            // log request and response
            $debugData['response'] = $response;
            $this->_logger->debug(var_export($debugData, true));
            return $response;
        } catch (\Exception $e) {
            $this->_logger->debug(var_export($debugData, true));
            throw $e;
        }
    }

    /**
     * Set shipping method to quote, if needed
     *
     * @param string $methodCode
     * @return void
     * @since 2.0.0
     */
    public function updateShippingMethod($methodCode)
    {
        $shippingAddress = $this->_quote->getShippingAddress();
        if (!$this->_quote->getIsVirtual() && $shippingAddress) {
            if ($methodCode != $shippingAddress->getShippingMethod()) {
                $this->ignoreAddressValidation();
                $shippingAddress->setShippingMethod($methodCode)->setCollectShippingRates(true);
                $cartExtension = $this->_quote->getExtensionAttributes();
                if ($cartExtension && $cartExtension->getShippingAssignments()) {
                    $cartExtension->getShippingAssignments()[0]
                        ->getShipping()
                        ->setMethod($methodCode);
                }
                $this->_quote->collectTotals();
                $this->quoteRepository->save($this->_quote);
            }
        }
    }

    /**
     * Place the order when customer returned from PayPal until this moment all quote data must be valid.
     *
     * @param string $token
     * @param string|null $shippingMethodCode
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    public function place($token, $shippingMethodCode = null)
    {
        if ($shippingMethodCode) {
            $this->updateShippingMethod($shippingMethodCode);
        }

        if ($this->getCheckoutMethod() == \Magento\Checkout\Model\Type\Onepage::METHOD_GUEST) {
            $this->prepareGuestQuote();
        }

        $this->ignoreAddressValidation();
        $this->_quote->collectTotals();
        $order = $this->quoteManagement->submit($this->_quote);

        if (!$order) {
            return;
        }

        // commence redirecting to finish payment, if paypal requires it
        if ($order->getPayment()->getAdditionalInformation(self::PAYMENT_INFO_TRANSPORT_REDIRECT)) {
            $this->_redirectUrl = $this->_config->getExpressCheckoutCompleteUrl($token);
        }

        switch ($order->getState()) {
            // even after placement paypal can disallow to authorize/capture, but will wait until bank transfers money
            case \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT:
                // TODO
                break;
                // regular placement, when everything is ok
            case \Magento\Sales\Model\Order::STATE_PROCESSING:
            case \Magento\Sales\Model\Order::STATE_COMPLETE:
            case \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW:
                $this->orderSender->send($order);
                $this->_checkoutSession->start();
                break;
            default:
                break;
        }
        $this->_order = $order;
    }

    /**
     * Make sure addresses will be saved without validation errors
     *
     * @return void
     * @since 2.0.0
     */
    private function ignoreAddressValidation()
    {
        $this->_quote->getBillingAddress()->setShouldIgnoreValidation(true);
        if (!$this->_quote->getIsVirtual()) {
            $this->_quote->getShippingAddress()->setShouldIgnoreValidation(true);
            if (!$this->_config->getValue('requireBillingAddress')
                && !$this->_quote->getBillingAddress()->getEmail()
            ) {
                $this->_quote->getBillingAddress()->setSameAsBilling(1);
            }
        }
    }

    /**
     * Determine whether redirect somewhere specifically is required
     *
     * @return string
     * @since 2.0.0
     */
    public function getRedirectUrl()
    {
        return $this->_redirectUrl;
    }

    /**
     * Get created billing agreement
     *
     * @return \Magento\Paypal\Model\Billing\Agreement|null
     * @since 2.0.0
     */
    public function getBillingAgreement()
    {
        return $this->_billingAgreement;
    }

    /**
     * Return order
     *
     * @return \Magento\Sales\Model\Order
     * @since 2.0.0
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Get checkout method
     *
     * @return string
     * @since 2.0.0
     */
    public function getCheckoutMethod()
    {
        if ($this->getCustomerSession()->isLoggedIn()) {
            return \Magento\Checkout\Model\Type\Onepage::METHOD_CUSTOMER;
        }
        if (!$this->_quote->getCheckoutMethod()) {
            if ($this->_checkoutData->isAllowedGuestCheckout($this->_quote)) {
                $this->_quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_GUEST);
            } else {
                $this->_quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_REGISTER);
            }
        }
        return $this->_quote->getCheckoutMethod();
    }

    /**
     * Sets address data from exported address
     *
     * @param Address $address
     * @param array $exportedAddress
     * @return void
     * @since 2.0.0
     */
    protected function _setExportedAddressData($address, $exportedAddress)
    {
        // Exported data is more priority if we came from Express Checkout button
        $isButton = (bool)$this->_quote->getPayment()->getAdditionalInformation(self::PAYMENT_INFO_BUTTON);
        if (!$isButton) {
            return;
        }

        foreach ($exportedAddress->getExportedKeys() as $key) {
            $data = $exportedAddress->getData($key);
            if (!empty($data)) {
                $address->setDataUsingMethod($key, $data);
            }
        }
    }

    /**
     * Set create billing agreement flag to api call
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _setBillingAgreementRequest()
    {
        if (!$this->_customerId) {
            return $this;
        }

        $isRequested = $this->_isBARequested || $this->_quote->getPayment()
            ->getAdditionalInformation(self::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT);

        if (!($this->_config->getValue('allow_ba_signup') == PaypalConfig::EC_BA_SIGNUP_AUTO
            || $isRequested && $this->_config->shouldAskToCreateBillingAgreement())
        ) {
            return $this;
        }

        if (!$this->_agreementFactory->create()->needToCreateForCustomer($this->_customerId)) {
            return $this;
        }
        $this->_getApi()->setBillingType($this->_getApi()->getBillingAgreementType());
        return $this;
    }

    /**
     * @return \Magento\Paypal\Model\Api\Nvp
     * @since 2.0.0
     */
    protected function _getApi()
    {
        if (null === $this->_api) {
            $this->_api = $this->_apiTypeFactory->create($this->_apiType)->setConfigObject($this->_config);
        }
        return $this->_api;
    }

    /**
     * Attempt to collect address shipping rates and return them for further usage in instant update API
     * Returns empty array if it was impossible to obtain any shipping rate
     * If there are shipping rates obtained, the method must return one of them as default.
     *
     * @param Address $address
     * @param bool $mayReturnEmpty
     * @param bool $calculateTax
     * @return array|false
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    protected function _prepareShippingOptions(Address $address, $mayReturnEmpty = false, $calculateTax = false)
    {
        $options = [];
        $i = 0;
        $iMin = false;
        $min = false;
        $userSelectedOption = null;

        foreach ($address->getGroupedAllShippingRates() as $group) {
            foreach ($group as $rate) {
                $amount = (double)$rate->getPrice();
                if ($rate->getErrorMessage()) {
                    continue;
                }
                $isDefault = $address->getShippingMethod() === $rate->getCode();
                $amountExclTax = $this->_taxData->getShippingPrice($amount, false, $address);
                $amountInclTax = $this->_taxData->getShippingPrice($amount, true, $address);

                $options[$i] = new \Magento\Framework\DataObject(
                    [
                        'is_default' => $isDefault,
                        'name' => trim("{$rate->getCarrierTitle()} - {$rate->getMethodTitle()}", ' -'),
                        'code' => $rate->getCode(),
                        'amount' => $amountExclTax,
                    ]
                );
                if ($calculateTax) {
                    $options[$i]->setTaxAmount(
                        $amountInclTax - $amountExclTax + $address->getTaxAmount() - $address->getShippingTaxAmount()
                    );
                }
                if ($isDefault) {
                    $userSelectedOption = $options[$i];
                }
                if (false === $min || $amountInclTax < $min) {
                    $min = $amountInclTax;
                    $iMin = $i;
                }
                $i++;
            }
        }

        if ($mayReturnEmpty && $userSelectedOption === null) {
            $options[] = new \Magento\Framework\DataObject(
                [
                    'is_default' => true,
                    'name'       => __('N/A'),
                    'code'       => 'no_rate',
                    'amount'     => 0.00,
                ]
            );
            if ($calculateTax) {
                $options[$i]->setTaxAmount($address->getTaxAmount());
            }
        } elseif ($userSelectedOption === null && isset($options[$iMin])) {
            $options[$iMin]->setIsDefault(true);
        }

        // Magento will transfer only first 10 cheapest shipping options if there are more than 10 available.
        if (count($options) > 10) {
            usort($options, [get_class($this), 'cmpShippingOptions']);
            array_splice($options, 10);
            // User selected option will be always included in options list
            if ($userSelectedOption !== null && !in_array($userSelectedOption, $options)) {
                $options[9] = $userSelectedOption;
            }
        }

        return $options;
    }

    /**
     * Compare two shipping options based on their amounts
     *
     * This function is used as a callback comparison function in shipping options sorting process
     * @see self::_prepareShippingOptions()
     *
     * @param \Magento\Framework\DataObject $option1
     * @param \Magento\Framework\DataObject $option2
     * @return int
     * @since 2.0.0
     */
    protected static function cmpShippingOptions(DataObject $option1, DataObject $option2)
    {
        if ($option1->getAmount() == $option2->getAmount()) {
            return 0;
        }
        return ($option1->getAmount() < $option2->getAmount()) ? -1 : 1;
    }

    /**
     * Try to find whether the code provided by PayPal corresponds to any of possible shipping rates
     * This method was created only because PayPal has issues with returning the selected code.
     * If in future the issue is fixed, we don't need to attempt to match it. It would be enough to set the method code
     * before collecting shipping rates
     *
     * @param Address $address
     * @param string $selectedCode
     * @return string
     * @since 2.0.0
     */
    protected function _matchShippingMethodCode(Address $address, $selectedCode)
    {
        $options = $this->_prepareShippingOptions($address, false);
        foreach ($options as $option) {
            if ($selectedCode === $option['code'] // the proper case as outlined in documentation
                || $selectedCode === $option['name'] // workaround: PayPal may return name instead of the code
                // workaround: PayPal may concatenate code and name, and return it instead of the code:
                || $selectedCode === "{$option['code']} {$option['name']}"
            ) {
                return $option['code'];
            }
        }
        return '';
    }

    /**
     * Create payment redirect url
     * @param bool|null $button
     * @param string $token
     * @return void
     * @since 2.0.0
     */
    protected function _setRedirectUrl($button, $token)
    {
        $this->_redirectUrl = ($button && !$this->_taxData->getConfig()->priceIncludesTax())
            ? $this->_config->getExpressCheckoutStartUrl($token)
            : $this->_config->getPayPalBasicStartUrl($token);
    }

    /**
     * Get customer session object
     *
     * @return \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    public function getCustomerSession()
    {
        return $this->_customerSession;
    }

    /**
     * Set shipping options to api
     * @param \Magento\Paypal\Model\Cart $cart
     * @param \Magento\Quote\Model\Quote\Address|null $address
     * @return void
     * @since 2.0.0
     */
    private function setShippingOptions(PaypalCart $cart, Address $address = null)
    {
        // for included tax always disable line items (related to paypal amount rounding problem)
        $this->_getApi()->setIsLineItemsEnabled($this->_config->getValue(PaypalConfig::TRANSFER_CART_LINE_ITEMS));

        // add shipping options if needed and line items are available
        $cartItems = $cart->getAllItems();
        if ($this->_config->getValue(PaypalConfig::TRANSFER_CART_LINE_ITEMS)
            && $this->_config->getValue(PaypalConfig::TRANSFER_SHIPPING_OPTIONS)
            && !empty($cartItems)
        ) {
            if (!$this->_quote->getIsVirtual()) {
                $options = $this->_prepareShippingOptions($address, true);
                if ($options) {
                    $this->_getApi()->setShippingOptionsCallbackUrl(
                        $this->_coreUrl->getUrl(
                            '*/*/shippingOptionsCallback',
                            ['quote_id' => $this->_quote->getId()]
                        )
                    )->setShippingOptions($options);
                }
            }
        }
    }

    /**
     * Prepare quote for guest checkout order submit
     *
     * @return $this
     * @since 2.0.0
     */
    protected function prepareGuestQuote()
    {
        $quote = $this->_quote;
        $quote->setCustomerId(null)
            ->setCustomerEmail($quote->getBillingAddress()->getEmail())
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID);
        return $this;
    }
}
