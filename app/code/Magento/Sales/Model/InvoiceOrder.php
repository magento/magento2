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
use Magento\Sales\Model\Order\Invoice\InvoiceValidatorInterface;
use Magento\Sales\Model\Order\Invoice\NotifierInterface;
use Magento\Sales\Model\Order\InvoiceDocumentFactory;
use Magento\Sales\Model\Order\InvoiceRepository;
use Magento\Sales\Model\Order\OrderStateResolverInterface;
use Magento\Sales\Model\Order\OrderValidatorInterface;
use Magento\Sales\Model\Order\PaymentAdapterInterface;
use Magento\Sales\Model\Order\Validation\InvoiceOrderInterface as InvoiceOrderValidator;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Class InvoiceOrder
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InvoiceOrder implements InvoiceOrderInterface
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
     * @var PaymentAdapterInterface
     */
    private $paymentAdapter;

    /**
     * @var OrderStateResolverInterface
     */
    private $orderStateResolver;

    /**
     * @var OrderConfig
     */
    private $config;

    /**
     * @var InvoiceRepository
     */
    private $invoiceRepository;

    /**
     * @var InvoiceOrderValidator
     */
    private $invoiceOrderValidator;

    /**
     * @var NotifierInterface
     */
    private $notifierInterface;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * InvoiceOrder constructor.
     * @param ResourceConnection $resourceConnection
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceDocumentFactory $invoiceDocumentFactory
     * @param InvoiceValidatorInterface $invoiceValidator
     * @param OrderValidatorInterface $orderValidator
     * @param PaymentAdapterInterface $paymentAdapter
     * @param OrderStateResolverInterface $orderStateResolver
     * @param OrderConfig $config
     * @param InvoiceRepository $invoiceRepository
     * @param NotifierInterface $notifierInterface
     * @param LoggerInterface $logger
     * @param InvoiceOrderValidator|null $invoiceOrderValidator
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        OrderRepositoryInterface $orderRepository,
        InvoiceDocumentFactory $invoiceDocumentFactory,
        InvoiceValidatorInterface $invoiceValidator,
        OrderValidatorInterface $orderValidator,
        PaymentAdapterInterface $paymentAdapter,
        OrderStateResolverInterface $orderStateResolver,
        OrderConfig $config,
        InvoiceRepository $invoiceRepository,
        NotifierInterface $notifierInterface,
        LoggerInterface $logger,
        InvoiceOrderValidator $invoiceOrderValidator = null
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->orderRepository = $orderRepository;
        $this->invoiceDocumentFactory = $invoiceDocumentFactory;
        $this->paymentAdapter = $paymentAdapter;
        $this->orderStateResolver = $orderStateResolver;
        $this->config = $config;
        $this->invoiceRepository = $invoiceRepository;
        $this->notifierInterface = $notifierInterface;
        $this->logger = $logger;
        $this->invoiceOrderValidator = $invoiceOrderValidator ?: ObjectManager::getInstance()->get(
            InvoiceOrderValidator::class
        );
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
