<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Adminhtml;

use Magento\Checkout\Model\Session;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedExceptionFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\Logger;
use Magento\Paypal\Model\CartFactory;
use Magento\Paypal\Model\Express as PaypalExpress;
use Magento\Paypal\Model\ProFactory;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\State\AuthorizeCommand;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Paypal\Model\Config;
use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Provides ability to make an authorization calls to Paypal API from admin.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Express extends PaypalExpress
{
    /**
     * @var AuthorizeCommand
     */
    private $authCommand;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Data $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param ProFactory $proFactory
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     * @param CartFactory $cartFactory
     * @param Session $checkoutSession
     * @param LocalizedExceptionFactory $exception
     * @param TransactionRepositoryInterface $transactionRepository
     * @param BuilderInterface $transactionBuilder
     * @param AuthorizeCommand $authCommand*
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        ProFactory $proFactory,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        CartFactory $cartFactory,
        Session $checkoutSession,
        LocalizedExceptionFactory $exception,
        TransactionRepositoryInterface $transactionRepository,
        BuilderInterface $transactionBuilder,
        AuthorizeCommand $authCommand,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        $data = []
    ) {
        $this->authCommand = $authCommand;

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
    }

    /**
     * Creates an authorization of requested amount.
     *
     * @param OrderInterface $order
     * @return $this
     * @throws LocalizedException
     */
    public function authorizeOrder(OrderInterface $order)
    {
        $baseTotalDue = $order->getBaseTotalDue();

        /** @var $payment Payment */
        $payment = $order->getPayment();
        if (!$payment || !$this->isOrderAuthorizationAllowed($payment)) {
            throw new LocalizedException(__('Authorization is not allowed.'));
        }

        $orderTransaction = $this->getOrderTransaction($payment);

        $api = $this->_callDoAuthorize($baseTotalDue, $payment, $orderTransaction->getTxnId());
        $this->_pro->importPaymentInfo($api, $payment);

        $payment->resetTransactionAdditionalInfo()
            ->setIsTransactionClosed(false)
            ->setTransactionId($api->getTransactionId())
            ->setParentTransactionId($orderTransaction->getTxnId());

        $transaction = $payment->addTransaction(Transaction::TYPE_AUTH, null, true);
        $message = $this->authCommand->execute($payment, $baseTotalDue, $payment->getOrder());
        $message = $payment->prependMessage($message);

        $payment->addTransactionCommentsToOrder($transaction, $message);
        $payment->setAmountAuthorized($order->getTotalDue());
        $payment->setBaseAmountAuthorized($baseTotalDue);

        return $this;
    }

    /**
     * Checks if payment has authorization transactions.
     *
     * @param Payment $payment
     * @return bool
     */
    private function hasAuthorization(Payment $payment): bool
    {
        return (bool) ($payment->getAmountAuthorized() ?? 0);
    }

    /**
     * Checks if payment authorization allowed
     *
     * @param Payment $payment
     * @return bool
     * @throws LocalizedException
     */
    public function isOrderAuthorizationAllowed(Payment $payment): bool
    {
        if ($payment->getMethod() === Config::METHOD_EXPRESS &&
            $payment->getMethodInstance()->getConfigPaymentAction() === AbstractMethod::ACTION_ORDER) {
            return !$this->hasAuthorization($payment);
        }

        return false;
    }
}
