<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\Data\InvoiceCommentCreationInterface;
use Magento\Sales\Api\Data\InvoiceCreationArgumentsInterface;
use Magento\Sales\Api\InvoiceOrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Sales\Model\Order\Invoice\NotifierInterface;
use Magento\Sales\Model\Order\InvoiceDocumentFactory;
use Magento\Sales\Model\Order\InvoiceRepository;
use Magento\Sales\Model\Order\OrderStateResolverInterface;
use Magento\Sales\Model\Order\PaymentAdapterInterface;
use Magento\Sales\Model\Order\Validation\InvoiceOrderInterface as InvoiceOrderValidator;
use Psr\Log\LoggerInterface;

/**
 * Class InvoiceOrder
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.2.0
 */
class InvoiceOrder implements InvoiceOrderInterface
{
    /**
     * @var ResourceConnection
     * @since 2.2.0
     */
    private $resourceConnection;

    /**
     * @var OrderRepositoryInterface
     * @since 2.2.0
     */
    private $orderRepository;

    /**
     * @var InvoiceDocumentFactory
     * @since 2.2.0
     */
    private $invoiceDocumentFactory;

    /**
     * @var PaymentAdapterInterface
     * @since 2.2.0
     */
    private $paymentAdapter;

    /**
     * @var OrderStateResolverInterface
     * @since 2.2.0
     */
    private $orderStateResolver;

    /**
     * @var OrderConfig
     * @since 2.2.0
     */
    private $config;

    /**
     * @var InvoiceRepository
     * @since 2.2.0
     */
    private $invoiceRepository;

    /**
     * @var InvoiceOrderValidator
     * @since 2.2.0
     */
    private $invoiceOrderValidator;

    /**
     * @var NotifierInterface
     * @since 2.2.0
     */
    private $notifierInterface;

    /**
     * @var LoggerInterface
     * @since 2.2.0
     */
    private $logger;

    /**
     * InvoiceOrder constructor.
     * @param ResourceConnection $resourceConnection
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceDocumentFactory $invoiceDocumentFactory
     * @param PaymentAdapterInterface $paymentAdapter
     * @param OrderStateResolverInterface $orderStateResolver
     * @param OrderConfig $config
     * @param InvoiceRepository $invoiceRepository
     * @param InvoiceOrderValidator $invoiceOrderValidator
     * @param NotifierInterface $notifierInterface
     * @param LoggerInterface $logger
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.2.0
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        OrderRepositoryInterface $orderRepository,
        InvoiceDocumentFactory $invoiceDocumentFactory,
        PaymentAdapterInterface $paymentAdapter,
        OrderStateResolverInterface $orderStateResolver,
        OrderConfig $config,
        InvoiceRepository $invoiceRepository,
        InvoiceOrderValidator $invoiceOrderValidator,
        NotifierInterface $notifierInterface,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->orderRepository = $orderRepository;
        $this->invoiceDocumentFactory = $invoiceDocumentFactory;
        $this->paymentAdapter = $paymentAdapter;
        $this->orderStateResolver = $orderStateResolver;
        $this->config = $config;
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceOrderValidator = $invoiceOrderValidator;
        $this->notifierInterface = $notifierInterface;
        $this->logger = $logger;
    }

    /**
     * @param int $orderId
     * @param bool $capture
     * @param array $items
     * @param bool $notify
     * @param bool $appendComment
     * @param \Magento\Sales\Api\Data\InvoiceCommentCreationInterface|null $comment
     * @param \Magento\Sales\Api\Data\InvoiceCreationArgumentsInterface|null $arguments
     * @return int
     * @throws \Magento\Sales\Api\Exception\DocumentValidationExceptionInterface
     * @throws \Magento\Sales\Api\Exception\CouldNotInvoiceExceptionInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \DomainException
     * @since 2.2.0
     */
    public function execute(
        $orderId,
        $capture = false,
        array $items = [],
        $notify = false,
        $appendComment = false,
        InvoiceCommentCreationInterface $comment = null,
        InvoiceCreationArgumentsInterface $arguments = null
    ) {
        $connection = $this->resourceConnection->getConnection('sales');
        $order = $this->orderRepository->get($orderId);
        $invoice = $this->invoiceDocumentFactory->create(
            $order,
            $items,
            $comment,
            ($appendComment && $notify),
            $arguments
        );
        $errorMessages = $this->invoiceOrderValidator->validate(
            $order,
            $invoice,
            $capture,
            $items,
            $notify,
            $appendComment,
            $comment,
            $arguments
        );
        if ($errorMessages->hasMessages()) {
            throw new \Magento\Sales\Exception\DocumentValidationException(
                __("Invoice Document Validation Error(s):\n" . implode("\n", $errorMessages->getMessages()))
            );
        }
        $connection->beginTransaction();
        try {
            $order = $this->paymentAdapter->pay($order, $invoice, $capture);
            $order->setState(
                $this->orderStateResolver->getStateForOrder($order, [OrderStateResolverInterface::IN_PROGRESS])
            );
            $order->setStatus($this->config->getStateDefaultStatus($order->getState()));
            $invoice->setState(\Magento\Sales\Model\Order\Invoice::STATE_PAID);
            $this->invoiceRepository->save($invoice);
            $this->orderRepository->save($order);
            $connection->commit();
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $connection->rollBack();
            throw new \Magento\Sales\Exception\CouldNotInvoiceException(
                __('Could not save an invoice, see error log for details')
            );
        }
        if ($notify) {
            if (!$appendComment) {
                $comment = null;
            }
            $this->notifierInterface->notify($order, $invoice, $comment);
        }
        return $invoice->getEntityId();
    }
}
