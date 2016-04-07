<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Method;

use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Model\Store;

/**
 * Paypal Billing Agreement method
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Agreement extends \Magento\Paypal\Model\Payment\Method\Billing\AbstractAgreement implements
    \Magento\Paypal\Model\Billing\Agreement\MethodInterface
{
    /**
     * Method code
     *
     * @var string
     */
    protected $_code = \Magento\Paypal\Model\Config::METHOD_BILLING_AGREEMENT;

    /**
     * Method instance setting
     *
     * @var bool
     */
    protected $_canAuthorize = true;

    /**
     * Method instance setting
     *
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * Method instance setting
     *
     * @var bool
     */
    protected $_canCapturePartial = true;

    /**
     * Method instance setting
     *
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * Method instance setting
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * Method instance setting
     *
     * @var bool
     */
    protected $_canVoid = true;

    /**
     * Method instance setting
     *
     * @var bool
     */
    protected $_canUseCheckout = true;

    /**
     * Method instance setting
     *
     * @var bool
     */
    protected $_canUseInternal = true;

    /**
     * Method instance setting
     *
     * @var bool
     */
    protected $_canFetchTransactionInfo = true;

    /**
     * Method instance setting
     *
     * @var bool
     */
    protected $_canReviewPayment = true;

    /**
     * Website Payments Pro instance
     *
     * @var \Magento\Paypal\Model\Pro
     */
    protected $_pro;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Paypal\Model\CartFactory
     */
    protected $_cartFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Paypal\Model\Billing\AgreementFactory $agreementFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Paypal\Model\ProFactory $proFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Paypal\Model\CartFactory $cartFactory
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
        \Magento\Paypal\Model\Billing\AgreementFactory $agreementFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Paypal\Model\ProFactory $proFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Paypal\Model\CartFactory $cartFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_urlBuilder = $urlBuilder;
        $this->_cartFactory = $cartFactory;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $agreementFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $proInstance = array_shift($data);
        if ($proInstance && $proInstance instanceof \Magento\Paypal\Model\Pro) {
            $this->_pro = $proInstance;
        } else {
            $this->_pro = $proFactory->create();
        }
        $this->_pro->setMethod($this->_code);
    }

    /**
     * Store setter
     * Also updates store ID in config object
     *
     * @param Store|int $store
     * @return $this
     */
    public function setStore($store)
    {
        $this->setData('store', $store);
        if (null === $store) {
            $store = $this->_storeManager->getStore()->getId();
        }
        $this->_pro->getConfig()->setStoreId(is_object($store) ? $store->getId() : $store);
        return $this;
    }

    /**
     * Init billing agreement
     *
     * @param \Magento\Paypal\Model\Billing\AbstractAgreement $agreement
     * @return $this
     */
    public function initBillingAgreementToken(\Magento\Paypal\Model\Billing\AbstractAgreement $agreement)
    {
        $api = $this->_pro->getApi()->setReturnUrl(
            $agreement->getReturnUrl()
        )->setCancelUrl(
            $agreement->getCancelUrl()
        )->setBillingType(
            $this->_pro->getApi()->getBillingAgreementType()
        );

        $api->callSetCustomerBillingAgreement();
        $agreement->setRedirectUrl($this->_pro->getConfig()->getStartBillingAgreementUrl($api->getToken()));
        return $this;
    }

    /**
     * Retrieve billing agreement customer details by token
     *
     * @param \Magento\Paypal\Model\Billing\AbstractAgreement $agreement
     * @return array
     */
    public function getBillingAgreementTokenInfo(\Magento\Paypal\Model\Billing\AbstractAgreement $agreement)
    {
        $api = $this->_pro->getApi()->setToken($agreement->getToken());
        $api->callGetBillingAgreementCustomerDetails();
        $responseData = [
            'token' => $api->getData('token'),
            'email' => $api->getData('email'),
            'payer_id' => $api->getData('payer_id'),
            'payer_status' => $api->getData('payer_status'),
        ];
        $agreement->addData($responseData);
        return $responseData;
    }

    /**
     * Create billing agreement by token specified in request
     *
     * @param \Magento\Paypal\Model\Billing\AbstractAgreement $agreement
     * @return $this
     */
    public function placeBillingAgreement(\Magento\Paypal\Model\Billing\AbstractAgreement $agreement)
    {
        $api = $this->_pro->getApi()->setToken($agreement->getToken());
        $api->callCreateBillingAgreement();
        $agreement->setBillingAgreementId($api->getData('billing_agreement_id'));
        return $this;
    }

    /**
     * Update billing agreement status
     *
     * @param \Magento\Paypal\Model\Billing\AbstractAgreement $agreement
     * @return $this
     * @throws \Exception|\Magento\Framework\Exception\LocalizedException
     */
    public function updateBillingAgreementStatus(\Magento\Paypal\Model\Billing\AbstractAgreement $agreement)
    {
        $targetStatus = $agreement->getStatus();
        $api = $this->_pro->getApi()->setReferenceId(
            $agreement->getReferenceId()
        )->setBillingAgreementStatus(
            $targetStatus
        );
        try {
            $api->callUpdateBillingAgreement();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            // when BA was already canceled, just pretend that the operation succeeded
            if (!(\Magento\Paypal\Model\Billing\Agreement::STATUS_CANCELED == $targetStatus &&
                $api->getIsBillingAgreementAlreadyCancelled())
            ) {
                throw $e;
            }
        }
        return $this;
    }

    /**
     * Authorize payment
     *
     * @param \Magento\Framework\DataObject|InfoInterface $payment
     * @param float $amount
     * @return $this
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this->_placeOrder($payment, $amount);
    }

    /**
     * Void payment
     *
     * @param \Magento\Framework\DataObject|InfoInterface|Payment $payment
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        $this->_pro->void($payment);
        return $this;
    }

    /**
     * Capture payment
     *
     * @param \Magento\Framework\DataObject|InfoInterface|Payment $payment
     * @param float $amount
     * @return $this
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (false === $this->_pro->capture($payment, $amount)) {
            $this->_placeOrder($payment, $amount);
        }
        return $this;
    }

    /**
     * Refund capture
     *
     * @param \Magento\Framework\DataObject|InfoInterface|Payment $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $this->_pro->refund($payment, $amount);
        return $this;
    }

    /**
     * Cancel payment
     *
     * @param \Magento\Framework\DataObject|InfoInterface|Payment $payment
     * @return $this
     */
    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        $this->_pro->cancel($payment);
        return $this;
    }

    /**
     * Whether payment can be reviewed
     * @return bool
     * @internal param InfoInterface|Payment $payment
     */
    public function canReviewPayment()
    {
        return parent::canReviewPayment() && $this->_pro->canReviewPayment($this->getInfoInstance());
    }

    /**
     * Attempt to accept a pending payment
     *
     * @param InfoInterface|Payment $payment
     * @return bool
     */
    public function acceptPayment(InfoInterface $payment)
    {
        parent::acceptPayment($payment);
        return $this->_pro->reviewPayment($payment, \Magento\Paypal\Model\Pro::PAYMENT_REVIEW_ACCEPT);
    }

    /**
     * Attempt to deny a pending payment
     *
     * @param InfoInterface|Payment $payment
     * @return bool
     */
    public function denyPayment(InfoInterface $payment)
    {
        parent::denyPayment($payment);
        return $this->_pro->reviewPayment($payment, \Magento\Paypal\Model\Pro::PAYMENT_REVIEW_DENY);
    }

    /**
     * Fetch transaction details info
     *
     * @param InfoInterface $payment
     * @param string $transactionId
     * @return array
     */
    public function fetchTransactionInfo(InfoInterface $payment, $transactionId)
    {
        return $this->_pro->fetchTransactionInfo($payment, $transactionId);
    }

    /**
     * Place an order with authorization or capture action
     *
     * @param Payment $payment
     * @param float $amount
     * @return $this
     */
    protected function _placeOrder(Payment $payment, $amount)
    {
        $order = $payment->getOrder();
        /** @var \Magento\Paypal\Model\Billing\Agreement $billingAgreement */
        $billingAgreement = $this->_agreementFactory->create()->load(
            $payment->getAdditionalInformation(
                \Magento\Paypal\Model\Payment\Method\Billing\AbstractAgreement::TRANSPORT_BILLING_AGREEMENT_ID
            )
        );

        $cart = $this->_cartFactory->create(['salesModel' => $order]);

        $proConfig = $this->_pro->getConfig();
        $api = $this->_pro->getApi()->setReferenceId(
            $billingAgreement->getReferenceId()
        )->setPaymentAction(
            $proConfig->getValue('paymentAction')
        )->setAmount(
            $amount
        )->setCurrencyCode(
            $payment->getOrder()->getBaseCurrencyCode()
        )->setNotifyUrl(
            $this->_urlBuilder->getUrl('paypal/ipn/')
        )->setPaypalCart(
            $cart
        )->setIsLineItemsEnabled(
            $proConfig->getValue('lineItemsEnabled')
        )->setInvNum(
            $order->getIncrementId()
        );

        // call api and import transaction and other payment information
        $api->callDoReferenceTransaction();
        $this->_pro->importPaymentInfo($api, $payment);
        $api->callGetTransactionDetails();
        $this->_pro->importPaymentInfo($api, $payment);

        $payment->setTransactionId($api->getTransactionId())->setIsTransactionClosed(0);

        if ($api->getBillingAgreementId()) {
            $order->addRelatedObject($billingAgreement);
            $billingAgreement->setIsObjectChanged(true);
            $billingAgreement->addOrderRelation($order);
        }

        return $this;
    }

    /**
     * @param object $quote
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _isAvailable($quote)
    {
        return $this->_pro->getConfig()->isMethodAvailable($this->_code);
    }

    /**
     * Payment action getter compatible with payment model
     *
     * @see \Magento\Sales\Model\Payment::place()
     * @return string
     */
    public function getConfigPaymentAction()
    {
        return $this->_pro->getConfig()->getPaymentAction();
    }
}
