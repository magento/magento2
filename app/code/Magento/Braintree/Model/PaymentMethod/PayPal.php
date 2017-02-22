<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\PaymentMethod;

use Magento\Braintree\Model\Adapter\BraintreeTransaction;
use Magento\Braintree\Model\Adapter\BraintreeCreditCard;
use \Braintree_Exception;
use \Braintree_Transaction;
use \Braintree_Result_Successful;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Braintree\Model\PaymentMethod;
use Magento\Payment\Model\InfoInterface;
use Magento\Braintree\Model\Vault;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory as TransactionCollectionFactory;

/**
 * Class PayPal
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PayPal extends \Magento\Braintree\Model\PaymentMethod
{
    const METHOD_CODE = 'braintree_paypal';

    /**
     * @var string
     */
    protected $_code = self::METHOD_CODE;

    /**
     * PayPal can't be used on backend
     *
     * @var bool
     */
    protected $_canUseInternal = false;

    /**
     * @var string
     */
    protected $_infoBlockType = 'Magento\Braintree\Block\Info\PayPal';

    /**
     * @var \Magento\Braintree\Model\Config\PayPal
     */
    protected $payPalConfig;

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
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Braintree\Model\Config\PayPal $payPalConfig
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
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
        \Magento\Braintree\Model\Config\PayPal $payPalConfig,
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
            $config,
            $vault,
            $braintreeTransaction,
            $braintreeCreditCard,
            $request,
            $braintreeHelper,
            $errorHelper,
            $salesTransactionCollectionFactory,
            $productMetaData,
            $regionFactory,
            $orderRepository,
            $resource,
            $resourceCollection,
            $data
        );
        $this->payPalConfig = $payPalConfig;
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
        return $this->payPalConfig->getConfigData($field, $storeId);
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
        $infoInstance->setAdditionalInformation(
            'payment_method_nonce',
            $additionalData->getData('payment_method_nonce')
        );
        return $this;
    }

    /**
     * Validate data
     *
     * @return $this
     * @throws LocalizedException
     */
    public function validate()
    {
        $info = $this->getInfoInstance();
        if ($info instanceof \Magento\Sales\Model\Order\Payment) {
            $billingCountry = $info->getOrder()->getBillingAddress()->getCountryId();
        } else {
            $billingCountry = $info->getQuote()->getBillingAddress()->getCountryId();
        }

        if (!$this->payPalConfig->canUseForCountry($billingCountry)) {
            throw new LocalizedException(__('Selected payment type is not allowed for billing country.'));
        }
        return $this;
    }

    /**
     * Check whether payment method can be used with the quote
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return $this->isActive($quote ? $quote->getStoreId() : null);
    }

    /**
     * Whether this payment method accept token
     *
     * @return bool
     */
    public function isTokenAllowed()
    {
        return false;
    }

    /**
     * Whether payment method nonce represent credit card payment
     *
     * @return bool
     */
    public function isPaymentMethodNonceForCc()
    {
        return false;
    }

    /**
     * @return bool
     */
    protected function verify3dSecure()
    {
        return false;
    }

    /**
     * Processes successful authorize/clone result
     *
     * @param \Magento\Framework\DataObject $payment
     * @param \Braintree_Result_Successful $result
     * @param float $amount
     * @return \Magento\Framework\DataObject
     */
    protected function processSuccessResult(
        \Magento\Framework\DataObject $payment,
        \Braintree_Result_Successful $result,
        $amount
    ) {
        $additionalInformation = $this->getExtraTransactionInformation($result->transaction);
        $payment->setStatus(self::STATUS_APPROVED)
            ->setCcTransId($result->transaction->id)
            ->setLastTransId($result->transaction->id)
            ->setTransactionId($result->transaction->id)
            ->setIsTransactionClosed(false)
            ->setAdditionalInformation($additionalInformation)
            ->setAmount($amount)
            ->setShouldCloseParentTransaction(false);
        return $payment;
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
        $payPalFields = [
            'payerEmail',
            'paymentId',
            'authorizationId',
            'payerId',
            'payerFirstName',
            'payerLastName'
        ];
        $payPalDetails = $transaction->paypalDetails;
        foreach ($payPalFields as $loggedField) {
            if (!empty($payPalDetails->{$loggedField})) {
                $data[$loggedField] = $payPalDetails->{$loggedField};
            }
        }
        return $data;
    }

    /**
     * Captures specified amount, override the method in Braintree CC method as transaction through PayPal does not
     * can't be cloned
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws LocalizedException
     */
    public function capture(InfoInterface $payment, $amount)
    {
        try {
            /** @var \Magento\Sales\Model\Order\Payment $payment */
            if ($payment->getCcTransId()) {
                $result = $this->braintreeTransaction->submitForSettlement($payment->getCcTransId(), $amount);
                $this->_debug([$payment->getCcTransId().' - '.$amount]);
                $this->_debug($this->_convertObjToArray($result));
                if ($result->success) {
                    $payment->setIsTransactionClosed(false)
                        ->setShouldCloseParentTransaction(true);
                } else {
                    throw new LocalizedException($this->errorHelper->parseBraintreeError($result));
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
}
