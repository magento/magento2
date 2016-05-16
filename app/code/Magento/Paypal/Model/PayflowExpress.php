<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Model;

use Magento\Sales\Model\Order\Payment\Transaction;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PayflowExpress extends \Magento\Paypal\Model\Express
{
    /**
     * @var string
     */
    protected $_code = Config::METHOD_WPP_PE_EXPRESS;

    /**
     * @var string
     */
    protected $_formBlockType = 'Magento\Paypal\Block\PayflowExpress\Form';

    /**
     * Express Checkout payment method instance
     *
     * @var Express
     */
    protected $_ecInstance = null;

    /**
     * @var InfoFactory
     */
    protected $_paypalInfoFactory;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canFetchTransactionInfo = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canReviewPayment = false;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param ProFactory $proFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param CartFactory $cartFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Exception\LocalizedExceptionFactory $exception
     * @param \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository
     * @param Transaction\BuilderInterface $transactionBuilder
     * @param InfoFactory $paypalInfoFactory
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
        ProFactory $proFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        CartFactory $cartFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Exception\LocalizedExceptionFactory $exception,
        \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
        InfoFactory $paypalInfoFactory,
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
            $proFactory,
            $storeManager,
            $urlBuilder,
            $cartFactory,
            $checkoutSession,
            $exception,
            $transactionRepository,
            $transactionBuilder,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_paypalInfoFactory = $paypalInfoFactory;
    }

    /**
     * EC PE won't be available if the EC is available
     *
     * @param \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if (!parent::isAvailable($quote)) {
            return false;
        }
        if (!$this->_ecInstance) {
            $this->_ecInstance = $this->_paymentData->getMethodInstance(
                Config::METHOD_WPP_EXPRESS
            );
        }
        if ($quote) {
            $this->_ecInstance->setStore($quote->getStoreId());
        }
        return !$this->_ecInstance->isAvailable();
    }

    /**
     * Import payment info to payment
     *
     * @param Api\Nvp $api
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return void
     */
    protected function _importToPayment($api, $payment)
    {
        $payment->setTransactionId(
            $api->getPaypalTransactionId()
        )->setIsTransactionClosed(
            0
        )->setAdditionalInformation(
            Express\Checkout::PAYMENT_INFO_TRANSPORT_REDIRECT,
            $api->getRedirectRequired() || $api->getRedirectRequested()
        )->setIsTransactionPending(
            $api->getIsPaymentPending()
        )->setTransactionAdditionalInfo(
            Payflow\Pro::TRANSPORT_PAYFLOW_TXN_ID,
            $api->getTransactionId()
        );
        $payment->setPreparedMessage(__('Payflow PNREF: #%1.', $api->getTransactionId()));
        $this->_paypalInfoFactory->create()->importToPayment($api, $payment);
    }

    /**
     * Checkout redirect URL getter for onepage checkout (hardcode)
     *
     * @see \Magento\Checkout\Controller\Onepage::savePaymentAction()
     * @see \Magento\Quote\Model\Quote\Payment::getCheckoutRedirectUrl()
     * @return string
     */
    public function getCheckoutRedirectUrl()
    {
        return $this->_urlBuilder->getUrl('paypal/payflowexpress/start');
    }

    /**
     * Check refund availability.
     * The main factor is that the last capture transaction exists and has an Payflow\Pro::TRANSPORT_PAYFLOW_TXN_ID in
     * additional information(needed to perform online refund. Requirement of the Payflow gateway)
     *
     * @return bool
     */
    public function canRefund()
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $this->getInfoInstance();
        // we need the last capture transaction was made
        $captureTransaction = $this->transactionRepository->getByTransactionType(
            Transaction::TYPE_CAPTURE,
            $payment->getId(),
            $payment->getOrder()->getId()
        );
        return $captureTransaction && $captureTransaction->getAdditionalInformation(
            Payflow\Pro::TRANSPORT_PAYFLOW_TXN_ID
        ) && $this->_canRefund;
    }
}
