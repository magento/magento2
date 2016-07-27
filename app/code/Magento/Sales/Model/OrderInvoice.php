<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

use Magento\Sales\Api\OrderInvoiceInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\InvoiceDocumentFactory;
use Magento\Sales\Model\Order\Invoice\NotifierInterface;
use Magento\Sales\Model\Order\InvoiceValidatorInterface;
use Magento\Sales\Model\Order\PaymentAdapterInterface;
use Magento\Sales\Model\Order\OrderStateResolverInterface;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Model\Order\InvoiceRepository;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class InvoiceService
 */
class OrderInvoice implements OrderInvoiceInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var InvoiceDocumentFactory
     */
    private $invoiceDocumentFactory;

    /**
     * @var InvoiceValidatorInterface
     */
    private $invoiceValidator;

    /**
     * @var PaymentAdapterInterface
     */
    private $paymentAdapter;

    /**
     * @var OrderStateResolverInterface
     */
    private $orderStateResolver;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var InvoiceRepository
     */
    private $invoiceRepository;

    /**
     * @var NotifierInterface
     */
    private $notifierInterface;

    /**
     * InvoiceOffline constructor.
     * @param ResourceConnection $resourceConnection
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceDocumentFactory $invoiceDocumentFactory
     * @param InvoiceValidatorInterface $invoiceValidator
     * @param PaymentAdapterInterface $paymentAdapter
     * @param OrderStateResolverInterface $orderStateResolver
     * @param Config $config
     * @param InvoiceRepository $invoiceRepository
     * @param NotifierInterface $notifierInterface
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        OrderRepositoryInterface $orderRepository,
        InvoiceDocumentFactory $invoiceDocumentFactory,
        InvoiceValidatorInterface $invoiceValidator,
        PaymentAdapterInterface $paymentAdapter,
        OrderStateResolverInterface $orderStateResolver,
        Config $config,
        InvoiceRepository $invoiceRepository,
        NotifierInterface $notifierInterface
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->orderRepository = $orderRepository;
        $this->invoiceDocumentFactory = $invoiceDocumentFactory;
        $this->invoiceValidator = $invoiceValidator;
        $this->paymentAdapter = $paymentAdapter;
        $this->orderStateResolver = $orderStateResolver;
        $this->config = $config;
        $this->invoiceRepository = $invoiceRepository;
        $this->notifierInterface = $notifierInterface;
    }

    public function execute(
        $orderId,
        $capture = false,
        array $items = [],
        $notify = false,
        $appendComment = false,
        \Magento\Sales\Api\Data\InvoiceCommentCreationInterface $comment = null,
        \Magento\Sales\Api\Data\InvoiceCreationArgumentsInterface $arguments = null
    ) {
        $connection = $this->resourceConnection->getConnection('sales');
        $order = $this->orderRepository->get($orderId);
        $invoice = $this->invoiceDocumentFactory->create($order, $items, $comment, $arguments);
        $errorMessages = $this->invoiceValidator->validate($invoice, $order);
        if (!empty($errorMessages)) {
            throw new LocalizedException(__("Sales Document Validation Error", $errorMessages));
        }
        $connection->beginTransaction();
        try {
            $order = $this->paymentAdapter->pay($order, $invoice, $capture);
            $order->setState(
                $this->orderStateResolver->getStateForOrder($order, [OrderStateResolverInterface::IN_PROGRESS])
            );
            $order->setStatus($this->config->getStateDefaultStatus($order->getState()));
            $invoice->setState(InvoiceInterface::STATE_PAID);
            $this->invoiceRepository->save($invoice);
            $this->orderRepository->save($order);
            $connection->commit();
        } catch (\Exception $e) {
            // log original exception
            $connection->rollBack();
            throw new LocalizedException(__("Sales Operation Failed", $errorMessages));
            // throw new SalesOperationFailedException
        }
        if ($notify) {
            $this->notifierInterface->notify($order, $invoice, $comment);
        }

    }
}

