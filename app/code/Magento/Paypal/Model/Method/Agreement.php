<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Paypal
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Paypal Billing Agreement method
 */
namespace Magento\Paypal\Model\Method;

class Agreement extends \Magento\Sales\Model\Payment\Method\Billing\AbstractAgreement
    implements \Magento\Payment\Model\Billing\Agreement\MethodInterface
{
    /**
     * Method code
     *
     * @var string
     */
    protected $_code = \Magento\Paypal\Model\Config::METHOD_BILLING_AGREEMENT;

    /**
     * Method instance settings
     */
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid                 = true;
    protected $_canUseCheckout          = false;
    protected $_canUseInternal          = false;
    protected $_canFetchTransactionInfo = true;
    protected $_canReviewPayment        = true;

    /**
     * Website Payments Pro instance
     *
     * @var \Magento\Paypal\Model\Pro
     */
    protected $_pro;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Paypal\Model\CartFactory
     */
    protected $_cartFactory;

    /**
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Core\Model\Store\ConfigInterface $coreStoreConfig
     * @param \Magento\Core\Model\Log\AdapterFactory $logAdapterFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Paypal\Model\Method\ProTypeFactory $proTypeFactory
     * @param \Magento\Sales\Model\Billing\AgreementFactory $agreementFactory
     * @param \Magento\UrlInterface $urlBuilder
     * @param \Magento\Paypal\Model\CartFactory $cartFactory
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Core\Model\Store\ConfigInterface $coreStoreConfig,
        \Magento\Core\Model\Log\AdapterFactory $logAdapterFactory,
        \Magento\Sales\Model\Billing\AgreementFactory $agreementFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Paypal\Model\Method\ProTypeFactory $proTypeFactory,
        \Magento\UrlInterface $urlBuilder,
        \Magento\Paypal\Model\CartFactory $cartFactory,
        array $data = array()
    ) {
        $this->_storeManager = $storeManager;
        $this->_urlBuilder = $urlBuilder;
        $this->_cartFactory = $cartFactory;
        parent::__construct(
            $eventManager,
            $paymentData,
            $coreStoreConfig,
            $logAdapterFactory,
            $agreementFactory,
            $data
        );
        $proInstance = array_shift($data);
        if ($proInstance && ($proInstance instanceof \Magento\Paypal\Model\Pro)) {
            $this->_pro = $proInstance;
        } else {
            $this->_pro = $proTypeFactory->create('Magento\Paypal\Model\Pro');
        }
        $this->_pro->setMethod($this->_code);
    }

    /**
     * Store setter
     * Also updates store ID in config object
     *
     * @param \Magento\Core\Model\Store|int $store
     * @return $this
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
     * @param \Magento\Payment\Model\Billing\AbstractAgreement $agreement
     * @return \Magento\Paypal\Model\Method\Agreement
     */
    public function initBillingAgreementToken(\Magento\Payment\Model\Billing\AbstractAgreement $agreement)
    {
        $api = $this->_pro->getApi()
            ->setReturnUrl($agreement->getReturnUrl())
            ->setCancelUrl($agreement->getCancelUrl())
            ->setBillingType($this->_pro->getApi()->getBillingAgreementType());

        $api->callSetCustomerBillingAgreement();
        $agreement->setRedirectUrl(
            $this->_pro->getConfig()->getStartBillingAgreementUrl($api->getToken())
        );
        return $this;
    }

    /**
     * Retrieve billing agreement customer details by token
     *
     * @param \Magento\Payment\Model\Billing\AbstractAgreement $agreement
     * @return array
     */
    public function getBillingAgreementTokenInfo(\Magento\Payment\Model\Billing\AbstractAgreement $agreement)
    {
        $api = $this->_pro->getApi()
            ->setToken($agreement->getToken());
        $api->callGetBillingAgreementCustomerDetails();
        $responseData = array(
            'token'         => $api->getData('token'),
            'email'         => $api->getData('email'),
            'payer_id'      => $api->getData('payer_id'),
            'payer_status'  => $api->getData('payer_status')
        );
        $agreement->addData($responseData);
        return $responseData;
    }

    /**
     * Create billing agreement by token specified in request
     *
     * @param \Magento\Payment\Model\Billing\AbstractAgreement $agreement
     * @return \Magento\Paypal\Model\Method\Agreement
     */
    public function placeBillingAgreement(\Magento\Payment\Model\Billing\AbstractAgreement $agreement)
    {
        $api = $this->_pro->getApi()
            ->setToken($agreement->getToken());
        $api->callCreateBillingAgreement();
        $agreement->setBillingAgreementId($api->getData('billing_agreement_id'));
        return $this;
    }

    /**
     * Update billing agreement status
     *
     * @param \Magento\Payment\Model\Billing\AbstractAgreement $agreement
     * @return \Magento\Paypal\Model\Method\Agreement
     * @throws \Exception|\Magento\Core\Exception
     */
    public function updateBillingAgreementStatus(\Magento\Payment\Model\Billing\AbstractAgreement $agreement)
    {
        $targetStatus = $agreement->getStatus();
        $api = $this->_pro->getApi()
            ->setReferenceId($agreement->getReferenceId())
            ->setBillingAgreementStatus($targetStatus);
        try {
            $api->callUpdateBillingAgreement();
        } catch (\Magento\Core\Exception $e) {
            // when BA was already canceled, just pretend that the operation succeeded
            if (!(\Magento\Sales\Model\Billing\Agreement::STATUS_CANCELED == $targetStatus
                && $api->getIsBillingAgreementAlreadyCancelled())) {
                throw $e;
            }
        }
        return $this;
    }

    /**
     * Authorize payment
     *
     * @param \Magento\Object $payment
     * @param float $amount
     * @return \Magento\Paypal\Model\Method\Agreement
     */
    public function authorize(\Magento\Object $payment, $amount)
    {
        return $this->_placeOrder($payment, $amount);
    }

    /**
     * Void payment
     *
     * @param \Magento\Object|\Magento\Sales\Model\Order\Payment $payment
     * @return \Magento\Paypal\Model\Method\Agreement
     */
    public function void(\Magento\Object $payment)
    {
        $this->_pro->void($payment);
        return $this;
    }

    /**
     * Capture payment
     *
     * @param \Magento\Object|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @return \Magento\Paypal\Model\Method\Agreement
     */
    public function capture(\Magento\Object $payment, $amount)
    {
        if (false === $this->_pro->capture($payment, $amount)) {
            $this->_placeOrder($payment, $amount);
        }
        return $this;
    }

    /**
     * Refund capture
     *
     * @param \Magento\Object|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @return \Magento\Paypal\Model\Method\Agreement
     */
    public function refund(\Magento\Object $payment, $amount)
    {
        $this->_pro->refund($payment, $amount);
        return $this;
    }

    /**
     * Cancel payment
     *
     * @param \Magento\Object|\Magento\Sales\Model\Order\Payment $payment
     * @return \Magento\Paypal\Model\Method\Agreement
     */
    public function cancel(\Magento\Object $payment)
    {
        $this->_pro->cancel($payment);
        return $this;
    }

    /**
     * Whether payment can be reviewed
     *
     * @param \Magento\Payment\Model\Info|\Magento\Sales\Model\Order\Payment $payment
     * @return bool
     */
    public function canReviewPayment(\Magento\Payment\Model\Info $payment)
    {
        return parent::canReviewPayment($payment) && $this->_pro->canReviewPayment($payment);
    }

    /**
     * Attempt to accept a pending payment
     *
     * @param \Magento\Payment\Model\Info|\Magento\Sales\Model\Order\Payment $payment
     * @return bool
     */
    public function acceptPayment(\Magento\Payment\Model\Info $payment)
    {
        parent::acceptPayment($payment);
        return $this->_pro->reviewPayment($payment, \Magento\Paypal\Model\Pro::PAYMENT_REVIEW_ACCEPT);
    }

    /**
     * Attempt to deny a pending payment
     *
     * @param \Magento\Payment\Model\Info|\Magento\Sales\Model\Order\Payment $payment
     * @return bool
     */
    public function denyPayment(\Magento\Payment\Model\Info $payment)
    {
        parent::denyPayment($payment);
        return $this->_pro->reviewPayment($payment, \Magento\Paypal\Model\Pro::PAYMENT_REVIEW_DENY);
    }

    /**
     * Fetch transaction details info
     *
     * @param \Magento\Payment\Model\Info $payment
     * @param string $transactionId
     * @return array
     */
    public function fetchTransactionInfo(\Magento\Payment\Model\Info $payment, $transactionId)
    {
        return $this->_pro->fetchTransactionInfo($payment, $transactionId);
    }

    /**
     * Place an order with authorization or capture action
     *
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @return \Magento\Paypal\Model\Method\Agreement
     */
    protected function _placeOrder(\Magento\Sales\Model\Order\Payment $payment, $amount)
    {
        $order = $payment->getOrder();
        /** @var \Magento\Sales\Model\Billing\Agreement $billingAgreement */
        $billingAgreement = $this->_agreementFactory->create()->load(
            $payment->getAdditionalInformation(
                \Magento\Sales\Model\Payment\Method\Billing\AbstractAgreement::TRANSPORT_BILLING_AGREEMENT_ID
            )
        );

        $parameters = array('params' => array($order));
        $api = $this->_pro->getApi()
            ->setReferenceId($billingAgreement->getReferenceId())
            ->setPaymentAction($this->_pro->getConfig()->paymentAction)
            ->setAmount($amount)
            ->setNotifyUrl($this->_urlBuilder->getUrl('paypal/ipn/'))
            ->setPaypalCart($this->_cartFactory->create($parameters))
            ->setIsLineItemsEnabled($this->_pro->getConfig()->lineItemsEnabled)
            ->setInvNum($order->getIncrementId());

        // call api and import transaction and other payment information
        $api->callDoReferenceTransaction();
        $this->_pro->importPaymentInfo($api, $payment);
        $api->callGetTransactionDetails();
        $this->_pro->importPaymentInfo($api, $payment);

        $payment->setTransactionId($api->getTransactionId())
            ->setIsTransactionClosed(0);

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
