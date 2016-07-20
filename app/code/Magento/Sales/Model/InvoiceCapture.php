<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model;

use Magento\Sales\Api\Data;
use Magento\Sales\Api\InvoiceCaptureInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\InvoiceBuilderInterface;
use Magento\Sales\Model\Order\InvoiceValidatorInterface;
use Magento\Sales\Model\Order\PaymentAdapterInterface;
use Magento\Sales\Model\Order\InvoiceStatisticInterface;
use Magento\Sales\Model\Order\StateCheckerInterface;
use Magento\Sales\Model\Order\InvoiceNotifierInterface;
use Magento\Sales\Model\Order\Config;
use Magento\Framework\App\ResourceConnection;

class InvoiceCapture implements InvoiceCaptureInterface
{
    const STATE_PAID = 2;

    /**
     * @var InvoiceBuilderInterface
     */
    private $invoiceBuilder;

    /**
     * @var InvoiceValidatorInterface
     */
    private $invoiceValidator;

    /**
     * @var PaymentAdapterInterface
     */
    private $paymentAdapter;

    /**
     * @var InvoiceStatisticInterface
     */
    private $invoiceStatistic;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var StateCheckerInterface
     */
    private $stateChecker;

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
     * @param InvoiceBuilderInterface $invoiceBuilder
     * @param InvoiceValidatorInterface $invoiceValidator
     * @param PaymentAdapterInterface $paymentAdapter
     * @param InvoiceStatisticInterface $invoiceStatistic
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param StateCheckerInterface $stateChecker
     * @param InvoiceNotifierInterface $invoiceNotifier
     * @param Config $config
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        InvoiceBuilderInterface $invoiceBuilder,
        InvoiceValidatorInterface $invoiceValidator,
        PaymentAdapterInterface $paymentAdapter,
        InvoiceStatisticInterface $invoiceStatistic,
        OrderRepositoryInterface $orderRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        StateCheckerInterface $stateChecker,
        InvoiceNotifierInterface $invoiceNotifier,
        Config $config,
        ResourceConnection $resourceConnection
    ) {
        $this->invoiceBuilder = $invoiceBuilder;
        $this->invoiceValidator = $invoiceValidator;
        $this->paymentAdapter = $paymentAdapter;
        $this->invoiceStatistic = $invoiceStatistic;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->stateChecker = $stateChecker;
        $this->invoiceNotifier = $invoiceNotifier;
        $this->config = $config;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param int $orderId
     * @param \Magento\Sales\Api\Data\InvoiceItemInterface[] $items
     * @param bool|false $notify
     * @param Data\InvoiceCommentCreationInterface|null $comment
     * @param Data\InvoiceCreationArgumentsInterface|null $arguments
     * @return int
     */
    public function captureOffline(
        $orderId,
        array $items = [],
        $notify = false,
        \Magento\Sales\Api\Data\InvoiceCommentCreationInterface $comment = null,
        \Magento\Sales\Api\Data\InvoiceCreationArgumentsInterface $arguments = null
    ) {
        $connection = $this->resourceConnection->getConnectionByName('sales');
        $order = $this->orderRepository->get($orderId);
        $this->invoiceBuilder->setOrder($order);
        $this->invoiceBuilder->setItems($items);
        $this->invoiceBuilder->setComment($comment);
        $this->invoiceBuilder->setCreationArguments($arguments);
        $invoice = $this->invoiceBuilder->create();
        $errorMessages = $this->invoiceValidator->validate($invoice, $order);
        if (!empty($errorMessages)) {
//            throw new SalesDocumentValidationException($messages);
        }
        $connection->beginTransaction();
        try {
            $order = $this->paymentAdapter->captureOffline($order, $invoice);
            $order = $this->invoiceStatistic->register($order, $invoice);
            $order->setState($this->stateChecker->getStateForOrder($order, [StateCheckerInterface::PROCESSING]));
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

    /**
     * @param int $orderId
     * @param \Magento\Sales\Api\Data\InvoiceItemCreationInterface[] $items
     * @param bool|false $notify
     * @param Data\InvoiceCommentCreationInterface|null $comment
     * @param Data\InvoiceCreationArgumentsInterface|null $arguments
     * @return int
     */
    public function captureOnline(
        $orderId,
        array $items = [],
        $notify = false,
        \Magento\Sales\Api\Data\InvoiceCommentCreationInterface $comment = null,
        \Magento\Sales\Api\Data\InvoiceCreationArgumentsInterface $arguments = null
    ) {
        $connection = $this->resourceConnection->getConnectionByName('sales');
        $order = $this->orderRepository->get($orderId);
        $this->invoiceBuilder->setOrder($order);
        $this->invoiceBuilder->setItems($items);
        $this->invoiceBuilder->setComment($comment);
        $this->invoiceBuilder->setCreationArguments($arguments);
        $invoice = $this->invoiceBuilder->create();
        $errorMessages = $this->invoiceValidator->validate($invoice, $order);
        if (!empty($errorMessages)) {
//            throw new SalesDocumentValidationException($messages);
        }
        $connection->beginTransaction();
        try {
            $order = $this->paymentAdapter->captureOnline($order, $invoice);
            $order = $this->invoiceStatistic->register($order, $invoice);
            $order->setState($this->stateChecker->getStateForOrder($order, [StateCheckerInterface::PROCESSING]));
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
