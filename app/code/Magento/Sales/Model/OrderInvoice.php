<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model;

use Magento\Sales\Api\Data;
use Magento\Sales\Api\OrderInvoiceInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\InvoiceDocumentFactory;
use Magento\Sales\Model\Order\InvoiceValidatorInterface;
use Magento\Sales\Model\Order\OrderStateResolverInterface;
use Magento\Sales\Model\Order\PaymentAdapterInterface;
use Magento\Sales\Model\Order\InvoiceNotifierInterface;
use Magento\Sales\Model\Order\Config;
use Magento\Framework\App\ResourceConnection;

class OrderInvoice implements OrderInvoiceInterface
{
    const STATE_PAID = 2;

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
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var OrderStateResolverInterface
     */
    private $orderStateResolver;

    /**
     * @var InvoiceNotifierInterface
     */
    private $invoiceNotifier;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * InvoiceCapture constructor.
     *
     * @param InvoiceDocumentFactory $invoiceDocumentFactory
     * @param InvoiceValidatorInterface $invoiceValidator
     * @param PaymentAdapterInterface $paymentAdapter
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param OrderStateResolverInterface $orderStateResolver
     * @param InvoiceNotifierInterface $invoiceNotifier
     * @param Config $config
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        InvoiceDocumentFactory $invoiceDocumentFactory,
        InvoiceValidatorInterface $invoiceValidator,
        PaymentAdapterInterface $paymentAdapter,
        OrderRepositoryInterface $orderRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        OrderStateResolverInterface $orderStateResolver,
        InvoiceNotifierInterface $invoiceNotifier,
        Config $config,
        ResourceConnection $resourceConnection
    ) {
        $this->invoiceDocumentFactory = $invoiceDocumentFactory;
        $this->invoiceValidator = $invoiceValidator;
        $this->paymentAdapter = $paymentAdapter;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->orderStateResolver = $orderStateResolver;
        $this->invoiceNotifier = $invoiceNotifier;
        $this->config = $config;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param int $orderId
     * @param bool|false $capture
     * @param \Magento\Sales\Api\Data\InvoiceItemCreationInterface[] $items
     * @param bool|false $notify
     * @param Data\InvoiceCommentCreationInterface|null $comment
     * @param Data\InvoiceCreationArgumentsInterface|null $arguments
     * @return int
     */
    public function execute(
        $orderId,
        $capture = false,
        array $items = [],
        $notify = false,
        \Magento\Sales\Api\Data\InvoiceCommentCreationInterface $comment = null,
        \Magento\Sales\Api\Data\InvoiceCreationArgumentsInterface $arguments = null
    ) {
        $connection = $this->resourceConnection->getConnectionByName('sales');
        $order = $this->orderRepository->get($orderId);
        $invoice = $this->invoiceDocumentFactory->create($order, $items, $comment, $arguments);
        $errorMessages = $this->invoiceValidator->validate($invoice, $order);
        if (!empty($errorMessages)) {
//            throw new SalesDocumentValidationException($messages);
        }
        $connection->beginTransaction();
        try {
            $order = $this->paymentAdapter->pay($order, $invoice, $capture);
            $order->setState(
                $this->orderStateResolver->getStateForOrder($order, [OrderStateResolverInterface::IN_PROGRESS])
            );
            $order->setStatus($this->config->getStateDefaultStatus($order->getState()));
            $invoice->setState(self::STATE_PAID);
            $this->invoiceRepository->save($invoice);
            $this->orderRepository->save($order);
            $connection->commit();
        } catch (\Exception $e) {
            // log original exception
            $connection->rollBack();
            // throw new SalesOperationFailedException
        }
        if ($notify) {
            $this->invoiceNotifier->notify($order, $invoice, $comment);
        }
    }
}
