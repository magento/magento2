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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Paypal\Model\Method;

use Magento\Store\Model\Store;
use Magento\Payment\Model\Info;
use Magento\Sales\Model\Order\Payment;

/**
 * Paypal Billing Agreement method
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
     * @var \Magento\Framework\StoreManagerInterface
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
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Logger\AdapterFactory $logAdapterFactory
     * @param \Magento\Paypal\Model\Billing\AgreementFactory $agreementFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Paypal\Model\ProFactory $proFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Paypal\Model\CartFactory $cartFactory
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Logger\AdapterFactory $logAdapterFactory,
        \Magento\Paypal\Model\Billing\AgreementFactory $agreementFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Paypal\Model\ProFactory $proFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Paypal\Model\CartFactory $cartFactory,
        array $data = array()
    ) {
        $this->_storeManager = $storeManager;
        $this->_urlBuilder = $urlBuilder;
        $this->_cartFactory = $cartFactory;
        parent::__construct(
            $eventManager,
            $paymentData,
            $scopeConfig,
            $logAdapterFactory,
            $agreementFactory,
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
        $responseData = array(
            'token' => $api->getData('token'),
            'email' => $api->getData('email'),
            'payer_id' => $api->getData('payer_id'),
            'payer_status' => $api->getData('payer_status')
        );
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
     * @throws \Exception|\Magento\Framework\Model\Exception
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
        } catch (\Magento\Framework\Model\Exception $e) {
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
     * @param \Magento\Framework\Object $payment
     * @param float $amount
     * @return $this
     */
    public function authorize(\Magento\Framework\Object $payment, $amount)
    {
        return $this->_placeOrder($payment, $amount);
    }

    /**
     * Void payment
     *
     * @param \Magento\Framework\Object|Payment $payment
     * @return $this
     */
    public function void(\Magento\Framework\Object $payment)
    {
        $this->_pro->void($payment);
        return $this;
    }

    /**
     * Capture payment
     *
     * @param \Magento\Framework\Object|Payment $payment
     * @param float $amount
     * @return $this
     */
    public function capture(\Magento\Framework\Object $payment, $amount)
    {
        if (false === $this->_pro->capture($payment, $amount)) {
            $this->_placeOrder($payment, $amount);
        }
        return $this;
    }

    /**
     * Refund capture
     *
     * @param \Magento\Framework\Object|Payment $payment
     * @param float $amount
     * @return $this
     */
    public function refund(\Magento\Framework\Object $payment, $amount)
    {
        $this->_pro->refund($payment, $amount);
        return $this;
    }

    /**
     * Cancel payment
     *
     * @param \Magento\Framework\Object|Payment $payment
     * @return $this
     */
    public function cancel(\Magento\Framework\Object $payment)
    {
        $this->_pro->cancel($payment);
        return $this;
    }

    /**
     * Whether payment can be reviewed
     *
     * @param Info|Payment $payment
     * @return bool
     */
    public function canReviewPayment(Info $payment)
    {
        return parent::canReviewPayment($payment) && $this->_pro->canReviewPayment($payment);
    }

    /**
     * Attempt to accept a pending payment
     *
     * @param Info|Payment $payment
     * @return bool
     */
    public function acceptPayment(Info $payment)
    {
        parent::acceptPayment($payment);
        return $this->_pro->reviewPayment($payment, \Magento\Paypal\Model\Pro::PAYMENT_REVIEW_ACCEPT);
    }

    /**
     * Attempt to deny a pending payment
     *
     * @param Info|Payment $payment
     * @return bool
     */
    public function denyPayment(Info $payment)
    {
        parent::denyPayment($payment);
        return $this->_pro->reviewPayment($payment, \Magento\Paypal\Model\Pro::PAYMENT_REVIEW_DENY);
    }

    /**
     * Fetch transaction details info
     *
     * @param Info $payment
     * @param string $transactionId
     * @return array
     */
    public function fetchTransactionInfo(Info $payment, $transactionId)
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

        $cart = $this->_cartFactory->create(array('salesModel' => $order));

        $proConfig = $this->_pro->getConfig();
        $api = $this->_pro->getApi()->setReferenceId(
            $billingAgreement->getReferenceId()
        )->setPaymentAction(
            $proConfig->getConfigValue('paymentAction')
        )->setAmount(
            $amount
        )->setCurrencyCode(
            $payment->getOrder()->getBaseCurrencyCode()
        )->setNotifyUrl(
            $this->_urlBuilder->getUrl('paypal/ipn/')
        )->setPaypalCart(
            $cart
        )->setIsLineItemsEnabled(
            $proConfig->getConfigValue('lineItemsEnabled')
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
