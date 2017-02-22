<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model;

use Magento\Braintree\Model\Adapter\BraintreeCreditCard;
use Magento\Braintree\Model\Adapter\BraintreeTransaction;
use \Braintree_Exception;
use \Braintree_Transaction;
use \Braintree_Result_Successful;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory as TransactionCollectionFactory;
use Magento\Sales\Model\Order\Payment\Transaction as PaymentTransaction;
use Magento\Payment\Model\InfoInterface;

/**
 * Class PaymentMethod
 * @package Magento\Braintree\Model
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class PaymentMethod extends \Magento\Payment\Model\Method\Cc
{
    const CAPTURE_ON_INVOICE        = 'invoice';
    const CAPTURE_ON_SHIPMENT       = 'shipment';
    const CHANNEL_NAME              = 'Magento';
    const METHOD_CODE               = 'braintree';
    const REGISTER_NAME             = 'braintree_save_card';
    const CONFIG_MASKED_FIELDS      = 'masked_fields';

    /**
     * @var string
     */
    protected $_formBlockType = 'Magento\Braintree\Block\Form';

    /**
     * @var string
     */
    protected $_infoBlockType = 'Magento\Braintree\Block\Info';

    /**
     * @var string
     */
    protected $_code                    = self::METHOD_CODE;

    /**
     * @var bool
     */
    protected $_isGateway               = true;

    /**
     * @var bool
     */
    protected $_canAuthorize            = true;

    /**
     * @var bool
     */
    protected $_canCapture              = true;

    /**
     * @var bool
     */
    protected $_canCapturePartial       = true;

    /**
     * @var bool
     */
    protected $_canRefund               = true;

    /**
     * @var bool
     */
    protected $_canVoid                 = true;

    /**
     * @var bool
     */
    protected $_canUseInternal          = true;

    /**
     * @var bool
     */
    protected $_canUseCheckout          = true;

    /**
     * @var bool
     */
    protected $_canSaveCc               = false;

    /**
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * @var string
     */
    protected $merchantAccountId       = '';

    /**
     * @var bool
     */
    protected $allowDuplicates         = true;

    /**
     * @var array|null
     */
    protected $requestMaskedFields     = null;

    /**
     * @var \Magento\Braintree\Model\Config\Cc
     */
    protected $config;

    /**
     * @var Vault
     */
    protected $vault;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Braintree\Helper\Data
     */
    protected $braintreeHelper;

    /**
     * @var \Magento\Braintree\Helper\Error
     */
    protected $errorHelper;

    /**
     * @var TransactionCollectionFactory
     */
    protected $salesTransactionCollectionFactory;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetaData;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $regionFactory;

    /**
     * @var BraintreeTransaction
     */
    protected $braintreeTransaction;

    /**
     * @var BraintreeCreditCard
     */
    protected $braintreeCreditCard;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Braintree\Model\Config\Cc $config
     * @param Vault $vault
     * @param BraintreeTransaction $braintreeTransaction
     * @param BraintreeCreditCard $braintreeCreditCard
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Braintree\Helper\Data $braintreeHelper
     * @param \Magento\Braintree\Helper\Error $errorHelper
     * @param TransactionCollectionFactory $salesTransactionCollectionFactory
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetaData
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Braintree\Model\Config\Cc $config,
        Vault $vault,
        BraintreeTransaction $braintreeTransaction,
        BraintreeCreditCard $braintreeCreditCard,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Braintree\Helper\Data $braintreeHelper,
        \Magento\Braintree\Helper\Error $errorHelper,
        TransactionCollectionFactory $salesTransactionCollectionFactory,
        \Magento\Framework\App\ProductMetadataInterface $productMetaData,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $moduleList,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
        $this->config = $config;
        $this->vault = $vault;
        $this->braintreeTransaction = $braintreeTransaction;
        $this->braintreeCreditCard = $braintreeCreditCard;
        $this->request = $request;
        $this->braintreeHelper = $braintreeHelper;
        $this->errorHelper = $errorHelper;
        $this->salesTransactionCollectionFactory = $salesTransactionCollectionFactory;
        $this->productMetaData = $productMetaData;
        $this->regionFactory = $regionFactory;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Assign corresponding data
     *
     * @param \Magento\Framework\DataObject|mixed $data
     * @return $this
     * @throws LocalizedException
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        $additionalData = $data->getAdditionalData();

        if (!is_array($data->getAdditionalData())) {
            return $this;
        }
        $additionalData = new DataObject($additionalData);

        $infoInstance = $this->getInfoInstance();
        if ($this->getConfigData('fraudprotection') > 0) {
            $infoInstance->setAdditionalInformation('device_data', $additionalData->getData('device_data'));
        }

        $infoInstance->setAdditionalInformation('cc_last4', $additionalData->getData('cc_last4'));
        $infoInstance->setAdditionalInformation('cc_token', $additionalData->getData('cc_token'));
        $infoInstance->setAdditionalInformation('store_in_vault', $additionalData->getData('store_in_vault'));
        $infoInstance->setAdditionalInformation(
            'payment_method_nonce',
            $additionalData->getData('payment_method_nonce')
        );

        $infoInstance->setCcLast4($additionalData->getData('cc_last4'));
        $infoInstance->setCcType($additionalData->getData('cc_type'));
        $infoInstance->setCcExpMonth($additionalData->getData('cc_exp_month'));
        $infoInstance->setCcExpYear($additionalData->getData('cc_exp_year'));
        return $this;
    }

    /**
     * Validate data
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate()
    {
        $info = $this->getInfoInstance();
        if ($info instanceof \Magento\Sales\Model\Order\Payment) {
            $billingCountry = $info->getOrder()->getBillingAddress()->getCountryId();
        } else {
            $billingCountry = $info->getQuote()->getBillingAddress()->getCountryId();
        }

        if (!$this->config->canUseForCountry($billingCountry)) {
            throw new LocalizedException(__('Selected payment type is not allowed for billing country.'));
        }

        $ccType = $info->getCcType();
        if (!$ccType) {
            $token = $this->getInfoInstance()->getAdditionalInformation('cc_token');
            if ($token) {
                $ccType = $this->vault->getSavedCardType($token);
            }
        }

        if ($ccType) {
            $error = $this->config->canUseCcTypeForCountry($billingCountry, $ccType);
            if ($error) {
                throw new LocalizedException($error);
            }
        }

        return $this;
    }

    /**
     * Authorizes specified amount
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws LocalizedException
     */
    public function authorize(InfoInterface $payment, $amount)
    {
        return $this->braintreeAuthorize($payment, $amount, false);
    }

    /**
     * Whether this payment method accept token
     *
     * @return bool
     */
    public function isTokenAllowed()
    {
        return true;
    }

    /**
     * Whether payment method nonce represent credit card payment
     *
     * @return bool
     */
    public function isPaymentMethodNonceForCc()
    {
        return true;
    }

    /**
     * Avoid saving duplicate card
     *
     * @param string $last4
     * @return bool
     */
    protected function shouldSaveCard($last4)
    {
        // to avoid card save several times during multishipping
        if ($this->_registry->registry(self::REGISTER_NAME) !== null) {
            return false;
        }

        if ($this->vault->canSaveCard($last4)) {
            $this->_registry->register(self::REGISTER_NAME, true);
            return true;
        }
        return false;
    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    protected function verify3dSecure()
    {
        return $this->config->is3dSecureEnabled() &&
            $this->_appState->getAreaCode() !== \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE;
    }

    /**
     * @param InfoInterface $payment
     * @param string|null $token
     * @return array
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function populateAuthorizeRequest(InfoInterface $payment, $token)
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $order = $payment->getOrder();
        $orderId = $order->getIncrementId();
        $billing = $order->getBillingAddress();
        $shipping = $order->getShippingAddress();
        $transactionParams = [
            'channel'   => $this->getChannel(),
            'orderId'   => $orderId,
            'customer'  => [
                'firstName' => $billing->getFirstname(),
                'lastName'  => $billing->getLastname(),
                'company'   => $billing->getCompany(),
                'phone'     => $billing->getTelephone(),
                'fax'       => $billing->getFax(),
                'email'     => $order->getCustomerEmail(),
            ]
        ];
        $customerId = $this->braintreeHelper
            ->generateCustomerId($order->getCustomerId(), $order->getCustomerEmail());

        $merchantAccountId = $this->config->getMerchantAccountId();
        if ($merchantAccountId) {
            $transactionParams['merchantAccountId'] = $merchantAccountId;
        }

        if (!$this->isTokenAllowed()) {
            $token = null;
        } elseif (!$token) {
            $token = $this->getInfoInstance()->getAdditionalInformation('cc_token');
        }

        if ($token) {
            $transactionParams['paymentMethodToken'] = $token;
            $transactionParams['customerId'] = $customerId;
            $transactionParams['billing']  = $this->toBraintreeAddress($billing);
            $transactionParams['shipping'] = $this->toBraintreeAddress($shipping);
        } elseif ($this->getInfoInstance()->getAdditionalInformation('payment_method_nonce')) {
            $transactionParams['paymentMethodNonce'] =
                $this->getInfoInstance()->getAdditionalInformation('payment_method_nonce');
            if ($this->isPaymentMethodNonceForCc()) {
                if ($order->getCustomerId() && $this->config->useVault()) {
                    if ($this->getInfoInstance()->getAdditionalInformation('store_in_vault')) {
                        $last4 = $this->getInfoInstance()->getAdditionalInformation('cc_last4');
                        if ($this->shouldSaveCard($last4)) {
                            $transactionParams['options']['storeInVaultOnSuccess'] = true;
                        }
                    } else {
                        $transactionParams['options']['storeInVault'] = false;
                    }
                    if ($this->vault->exists($customerId)) {
                        $transactionParams['customerId'] = $customerId;
                        //TODO: How can we update customer information?
                        unset($transactionParams['customer']);
                    } else {
                        $transactionParams['customer']['id'] = $customerId;
                    }
                }

                $transactionParams['creditCard'] = [
                    'cardholderName'    => $billing->getFirstname() . ' ' . $billing->getLastname(),
                ];
            }
            $transactionParams['billing']  = $this->toBraintreeAddress($billing);
            $transactionParams['shipping'] = $this->toBraintreeAddress($shipping);
            $transactionParams['options']['addBillingAddressToPaymentMethod']  = true;
        } else {
            throw new LocalizedException(__('Incomplete payment information.'));
        }

        if ($this->verify3dSecure()) {
            $transactionParams['options']['three_d_secure'] = [
                'required' => true,
            ];

            if ($token && $this->getInfoInstance()->getAdditionalInformation('payment_method_nonce')) {
                $transactionParams['paymentMethodNonce'] =
                    $this->getInfoInstance()->getAdditionalInformation('payment_method_nonce');
                unset($transactionParams['paymentMethodToken']);
            }
        }

        if ($this->config->isFraudProtectionEnabled() &&
            strlen($this->getInfoInstance()->getAdditionalInformation('device_data')) > 0) {
            $transactionParams['deviceData'] = $this->getInfoInstance()->getAdditionalInformation('device_data');
        }
        return $transactionParams;
    }

    /**
     * Authorizes specified amount
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @param bool $capture
     * @param string $token
     * @return $this
     * @throws LocalizedException
     */
    protected function braintreeAuthorize(InfoInterface $payment, $amount, $capture, $token = null)
    {
        try {
            $transactionParams = $this->populateAuthorizeRequest($payment, $token);
            if ($capture) {
                $transactionParams['options']['submitForSettlement'] = true;
            }
            $transactionParams['amount'] = $amount;

            $this->_debug($transactionParams);
            try {
                $result = $this->braintreeTransaction->sale($transactionParams);
                $this->_debug($this->_convertObjToArray($result));
            } catch (\Exception $e) {
                $this->_logger->critical($e);
                throw new LocalizedException(__('Please try again later'));
            }
            if ($result->success) {
                $this->setStore($payment->getOrder()->getStoreId());
                $this->processSuccessResult($payment, $result, $amount);
            } else {
                throw new LocalizedException($this->errorHelper->parseBraintreeError($result));
            }
        } catch (LocalizedException $e) {
            $this->_registry->unregister(self::REGISTER_NAME);
            throw $e;
        }
        return $this;
    }

    /**
     * Returns extra transaction information, to be logged as part of the order payment
     *
     * @param \Braintree_Transaction $transaction
     * @return array
     */
    protected function getExtraTransactionInformation(\Braintree_Transaction $transaction)
    {
        $data = [];
        $loggedFields =[
            'avsErrorResponseCode',
            'avsPostalCodeResponseCode',
            'avsStreetAddressResponseCode',
            'cvvResponseCode',
            'gatewayRejectionReason',
            'processorAuthorizationCode',
            'processorResponseCode',
            'processorResponseText',
        ];
        foreach ($loggedFields as $loggedField) {
            if (!empty($transaction->{$loggedField})) {
                $data[$loggedField] = $transaction->{$loggedField};
            }
        }
        return $data;
    }

    /**
     * @param InfoInterface $payment
     * @param string $amount
     * @return $this
     * @throws LocalizedException
     */
    protected function partialCapture($payment, $amount)
    {
        $collection = $this->salesTransactionCollectionFactory->create()
            ->addPaymentIdFilter($payment->getId())
            ->addTxnTypeFilter(PaymentTransaction::TYPE_AUTH)
            ->setOrder('created_at', \Magento\Framework\Data\Collection::SORT_ORDER_DESC)
            ->setOrder('transaction_id', \Magento\Framework\Data\Collection::SORT_ORDER_DESC)
            ->setPageSize(1)
            ->setCurPage(1);
        $authTransaction = $collection->getFirstItem();
        if (!$authTransaction->getId()) {
            throw new LocalizedException(__('Can not find original authorization transaction for partial capture'));
        }
        if (($token = $authTransaction->getAdditionalInformation('token'))) {
            //order was placed using saved card or card was saved during checkout token
            $found = true;
            try {
                $this->braintreeCreditCard->find($token);
            } catch (\Exception $e) {
                $found = false;
            }
            if ($found) {
                $this->config->initEnvironment($payment->getOrder()->getStoreId());
                $this->braintreeAuthorize($payment, $amount, true, $token);
            } else {
                // case if payment token is no more applicable. attempt to clone transaction
                $result = $this->cloneTransaction($amount, $authTransaction->getTxnId());
                if ($result && $result->success) {
                    $this->processSuccessResult($payment, $result, $amount);
                } else {
                    throw new LocalizedException($this->errorHelper->parseBraintreeError($result));
                }
            }
        } else {
            // order was placed without saved card and card wasn't saved during checkout
            $result = $this->cloneTransaction($amount, $authTransaction->getTxnId());
            if ($result->success) {
                $this->processSuccessResult($payment, $result, $amount);
            } else {
                throw new LocalizedException($this->errorHelper->parseBraintreeError($result));
            }
        }
        return $this;
    }

    /**
     * Captures specified amount
     *
     * @param InfoInterface $payment
     * @param string $amount
     * @return $this
     * @throws LocalizedException
     */
    public function capture(InfoInterface $payment, $amount)
    {
        try {
            /** @var \Magento\Sales\Model\Order\Payment $payment */
            if ($payment->getCcTransId()) {
                $collection = $this->salesTransactionCollectionFactory->create()
                    ->addFieldToFilter('payment_id', $payment->getId())
                    ->addFieldToFilter('txn_type', PaymentTransaction::TYPE_CAPTURE);
                if ($collection->getSize() > 0) {
                    $this->partialCapture($payment, $amount);
                } else {
                    $result = $this->braintreeTransaction->submitForSettlement($payment->getCcTransId(), $amount);
                    $this->_debug([$payment->getCcTransId().' - '.$amount]);
                    $this->_debug($this->_convertObjToArray($result));
                    if ($result->success) {
                        $payment->setIsTransactionClosed(false)
                            ->setShouldCloseParentTransaction(false);
                        if ($payment->isCaptureFinal($amount)) {
                            $payment->setShouldCloseParentTransaction(true);
                        }
                    } else {
                        throw new LocalizedException($this->errorHelper->parseBraintreeError($result));
                    }
                }
            } else {
                $this->braintreeAuthorize($payment, $amount, true);
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            throw new LocalizedException(__('There was an error capturing the transaction: %1.', $e->getMessage()));
        }
        return $this;
    }

    /**
     * Refunds specified amount
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws LocalizedException
     */
    public function refund(InfoInterface $payment, $amount)
    {
        $transactionId = $this->braintreeHelper->clearTransactionId($payment->getRefundTransactionId());
        try {
            $transaction = $this->braintreeTransaction->find($transactionId);
            $this->_debug([$payment->getCcTransId()]);
            $this->_debug($this->_convertObjToArray($transaction));
            if ($transaction->status === \Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT) {
                if ($transaction->amount != $amount) {
                    $message = __('This refund is for a partial amount but the Transaction has not settled.')
                        ->getText();
                    $message .= ' ';
                    $message .= __('Please wait 24 hours before trying to issue a partial refund.')
                        ->getText();
                    throw new LocalizedException(
                        __($message)
                    );
                }
            }

            // transaction should be voided if it not settled
            $canVoid = ($transaction->status === \Braintree_Transaction::AUTHORIZED
                || $transaction->status === \Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT);
            $result = $canVoid
                ? $this->braintreeTransaction->void($transactionId)
                : $this->braintreeTransaction->refund($transactionId, $amount);
            $this->_debug($this->_convertObjToArray($result));
            if ($result->success) {
                $payment->setIsTransactionClosed(true);
            } else {
                throw new LocalizedException($this->errorHelper->parseBraintreeError($result));
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            throw new LocalizedException(__('There was an error refunding the transaction: %1.', $message));
        }
        return $this;
    }

    /**
     * @param InfoInterface $payment
     * @return array
     */
    protected function getTransactionsToVoid(InfoInterface $payment)
    {
        $transactionIds = [];
        $invoice = $this->_registry->registry('current_invoice');
        if ($invoice && $invoice->getId() && $invoice->getTransactionId()) {
            $transactionIds[] = $this->braintreeHelper->clearTransactionId($invoice->getTransactionId());
        } else {
            $collection = $this->salesTransactionCollectionFactory->create()
                ->addFieldToSelect('txn_id')
                ->addOrderIdFilter($payment->getOrder()->getId())
                ->addTxnTypeFilter(
                    [
                        PaymentTransaction::TYPE_AUTH,
                        PaymentTransaction::TYPE_CAPTURE,
                    ]
                );
            $fetchedIds = $collection->getColumnValues('txn_id');
            foreach ($fetchedIds as $transactionId) {
                $txnId = $this->braintreeHelper->clearTransactionId($transactionId);
                if (!in_array($txnId, $transactionIds)) {
                    $transactionIds[] = $txnId;
                }
            }
        }
        return $transactionIds;
    }

    /**
     * Voids transaction
     *
     * @param InfoInterface $payment
     * @throws LocalizedException
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function void(InfoInterface $payment)
    {
        $transactionIds = $this->getTransactionsToVoid($payment);
        $message = false;
        foreach ($transactionIds as $transactionId) {
            $transaction = $this->braintreeTransaction->find($transactionId);
            if ($transaction->status !== \Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT &&
                $transaction->status !== \Braintree_Transaction::AUTHORIZED) {
                throw new LocalizedException(
                    __('Some transactions are already settled or voided and cannot be voided.')
                );
            }
            if ($transaction->status === \Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT) {
                $message = __('Voided capture.') ;
            }
        }
        $errors = '';
        foreach ($transactionIds as $transactionId) {
            $this->_debug(['void-' . $transactionId]);
            $result = $this->braintreeTransaction->void($transactionId);
            $this->_debug($this->_convertObjToArray($result));
            if (!$result->success) {
                $errors .= ' ' . $this->errorHelper->parseBraintreeError($result)->getText();
            } elseif ($message) {
                $payment->setMessage($message);
            }
        }
        if ($errors) {
            throw new LocalizedException(__('There was an error voiding the transaction: %1.', $errors));
        } else {
            $match = true;
            foreach ($transactionIds as $transactionId) {
                $collection = $this->salesTransactionCollectionFactory->create()
                    ->addFieldToFilter('parent_txn_id', ['eq' => $transactionId])
                    ->addFieldToFilter('txn_type', PaymentTransaction::TYPE_VOID);
                if ($collection->getSize() < 1) {
                    $match = false;
                }
            }
            if ($match) {
                $payment->setIsTransactionClosed(true);
            }
        }
        return $this;
    }

    /**
     * Returns two digit region code if possible
     *
     * @param string $region
     * @param int|string $regionId
     * @return string
     */
    protected function convertRegionToCode($region, $regionId)
    {
        if (is_string($region) && strlen($region) == 2) {
            return $region;
        } else {
            $regionObj = $this->regionFactory->create()->load($regionId);
            if ($regionObj->getId()) {
                return $regionObj->getCode();
            }
        }
        return $region;
    }

    /**
     * Convert magento address to array for braintree
     *
     * @param \Magento\Sales\Api\Data\OrderAddressInterface $address
     * @return array
     */
    public function toBraintreeAddress($address)
    {
        if ($address) {
            $countryId = $address->getCountryId();
            $region = $address->getRegion();
            if ($countryId == 'US') {
                $region = $this->convertRegionToCode($region, $address->getRegionId());
            }
            $street = $address->getStreet();
            $streetAddress = $street[0];
            return [
                'firstName'         => $address->getFirstname(),
                'lastName'          => $address->getLastname(),
                'company'           => $address->getCompany(),
                'streetAddress'     => $streetAddress,
                'extendedAddress'   => isset($street[1]) ? $street[1] : null,
                'locality'          => $address->getCity(),
                'region'            => $region,
                'postalCode'        => $address->getPostcode(),
                'countryCodeAlpha2' => $countryId, // alpha2 is the default in magento
            ];
        } else {
            return [];
        }
    }

    /**
     * Voids transaction on cancel action
     *
     * @param InfoInterface $payment
     * @return $this
     * @throws LocalizedException
     */
    public function cancel(InfoInterface $payment)
    {
        try {
            $this->void($payment);
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            throw new LocalizedException(__('There was an error voiding the transaction: %1.', $e->getMessage()));
        }
        return $this;
    }

    /**
     * Check whether payment method is applicable to quote
     * Purposed to allow use in controllers some logic that was implemented in blocks only before
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if (parent::isAvailable($quote)) {
            if ($quote != null) {
                $availableCcTypes = $this->config->getApplicableCardTypes($quote->getBillingAddress()->getCountryId());
                if (!$availableCcTypes) {
                    return false;
                }
            }
        } else {
            return false;
        }
        return true;
    }

    /**
     * Format param "channel" for transaction
     *
     * @return string
     */
    protected function getChannel()
    {
        $edition = $this->productMetaData->getEdition();
        $version = $this->productMetaData->getVersion();
        return self::CHANNEL_NAME . ' ' . $edition . ' ' . $version;
    }

    /**
     * Clones existing transaction
     *
     * @param float $amount
     * @param string $transactionId
     * @return mixed
     * @throws \Exception
     */
    protected function cloneTransaction($amount, $transactionId)
    {
        $this->_debug(['clone-' . $transactionId . ' amount=' . $amount]);
        $result = $this->braintreeTransaction->cloneTransaction(
            $transactionId,
            [
                'amount'    => $amount,
                'options'   => [
                    'submitForSettlement' => true,
                ]
            ]
        );
        $this->_debug($this->_convertObjToArray($result));
        return $result;
    }

    /**
     * Processes successful authorize/clone result
     *
     * @param \Magento\Framework\DataObject $payment
     * @param \Braintree_Result_Successful $result
     * @param string $amount
     * @return \Magento\Framework\DataObject
     */
    protected function processSuccessResult(
        \Magento\Framework\DataObject $payment,
        \Braintree_Result_Successful $result,
        $amount
    ) {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment->setStatus(self::STATUS_APPROVED)
            ->setCcTransId($result->transaction->id)
            ->setLastTransId($result->transaction->id)
            ->setTransactionId($result->transaction->id)
            ->setIsTransactionClosed(false)
            ->setCcLast4($result->transaction->creditCardDetails->last4)
            ->setAdditionalInformation($this->getExtraTransactionInformation($result->transaction))
            ->setAmount($amount)
            ->setShouldCloseParentTransaction(false);
        if ($payment->isCaptureFinal($amount)) {
            $payment->setShouldCloseParentTransaction(true);
        }
        if (isset($result->transaction->creditCard['token']) && $result->transaction->creditCard['token']) {
            $payment->setTransactionAdditionalInfo('token', $result->transaction->creditCard['token']);
        }
        return $payment;
    }

    /**
     * @return bool
     */
    public function canVoid()
    {
        if ((($order = $this->_registry->registry('current_order'))
            && $order->getId() && $order->hasInvoices()) || $this->_registry->registry('current_invoice')) {
            return false;
        }
        return $this->_canVoid;
    }

    /**
     * Return replace keys for debug data
     *
     * @return array
     */
    public function getDebugReplacePrivateDataKeys()
    {
        if (!$this->requestMaskedFields) {
            $this->requestMaskedFields = explode(',', $this->config->getConfigData(self::CONFIG_MASKED_FIELDS));
        }
        return (array) $this->requestMaskedFields;
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|string|null|\Magento\Store\Model\Store $storeId
     *
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        if ('order_place_redirect_url' === $field) {
            return $this->getOrderPlaceRedirectUrl();
        }
        return $this->config->getConfigData($field, $storeId);
    }

    /**
     * Convert response from Braintree to array
     * @param \Braintree_Result_Successful|\Braintree_Result_Error|\Braintree_Transaction $data
     * @return array
     */
    protected function _convertObjToArray($data)
    {
        return json_decode(json_encode($data), true);
    }
}
