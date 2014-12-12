<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Paypal\Model;

use Magento\Sales\Model\Order\Payment\Transaction;

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
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Logger\AdapterFactory $logAdapterFactory
     * @param ProFactory $proFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param CartFactory $cartFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Model\ExceptionFactory $exception
     * @param InfoFactory $paypalInfoFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Logger\AdapterFactory $logAdapterFactory,
        ProFactory $proFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        CartFactory $cartFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Model\ExceptionFactory $exception,
        InfoFactory $paypalInfoFactory,
        array $data = []
    ) {
        parent::__construct(
            $eventManager,
            $paymentData,
            $scopeConfig,
            $logAdapterFactory,
            $proFactory,
            $storeManager,
            $urlBuilder,
            $cartFactory,
            $checkoutSession,
            $exception,
            $data
        );
        $this->_paypalInfoFactory = $paypalInfoFactory;
    }

    /**
     * EC PE won't be available if the EC is available
     *
     * @param \Magento\Sales\Model\Quote|null $quote
     * @return bool
     */
    public function isAvailable($quote = null)
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
     * @see \Magento\Sales\Model\Quote\Payment::getCheckoutRedirectUrl()
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
        $captureTransaction = $payment->lookupTransaction('', Transaction::TYPE_CAPTURE);
        return $captureTransaction && $captureTransaction->getAdditionalInformation(
            Payflow\Pro::TRANSPORT_PAYFLOW_TXN_ID
        ) && $this->_canRefund;
    }
}
