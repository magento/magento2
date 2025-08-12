<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoCommentCreationInterface;
use Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\RefundInvoiceInterface;
use Magento\Sales\Exception\CouldNotRefundException;
use Magento\Sales\Exception\DocumentValidationException;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Sales\Model\Order\Creditmemo\NotifierInterface;
use Magento\Sales\Model\Order\CreditmemoDocumentFactory;
use Magento\Sales\Model\Order\OrderStateResolverInterface;
use Magento\Sales\Model\Order\RefundAdapterInterface;
use Magento\Sales\Model\Order\Validation\RefundInvoiceInterface as RefundInvoiceValidator;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RefundInvoice implements RefundInvoiceInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var OrderStateResolverInterface
     */
    private $orderStateResolver;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    /**
     * @var RefundAdapterInterface
     */
    private $refundAdapter;

    /**
     * @var CreditmemoDocumentFactory
     */
    private $creditmemoDocumentFactory;

    /**
     * @var NotifierInterface
     */
    private $notifier;

    /**
     * @var OrderConfig
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RefundInvoiceValidator
     */
    private $validator;

    /**
     * @var OrderMutexInterface
     */
    private $orderMutex;

    /**
     * RefundInvoice constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param OrderStateResolverInterface $orderStateResolver
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param RefundInvoiceValidator $validator
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param RefundAdapterInterface $refundAdapter
     * @param CreditmemoDocumentFactory $creditmemoDocumentFactory
     * @param NotifierInterface $notifier
     * @param OrderConfig $config
     * @param LoggerInterface $logger
     * @param OrderMutexInterface|null $orderMutex
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        OrderStateResolverInterface $orderStateResolver,
        OrderRepositoryInterface $orderRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        RefundInvoiceValidator $validator,
        CreditmemoRepositoryInterface $creditmemoRepository,
        RefundAdapterInterface $refundAdapter,
        CreditmemoDocumentFactory $creditmemoDocumentFactory,
        NotifierInterface $notifier,
        OrderConfig $config,
        LoggerInterface $logger,
        ?OrderMutexInterface $orderMutex = null
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->orderStateResolver = $orderStateResolver;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->validator = $validator;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->refundAdapter = $refundAdapter;
        $this->creditmemoDocumentFactory = $creditmemoDocumentFactory;
        $this->notifier = $notifier;
        $this->config = $config;
        $this->logger = $logger;
        $this->orderMutex = $orderMutex ?: ObjectManager::getInstance()->get(OrderMutexInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function execute(
        $invoiceId,
        array $items = [],
        $isOnline = false,
        $notify = false,
        $appendComment = false,
        ?\Magento\Sales\Api\Data\CreditmemoCommentCreationInterface $comment = null,
        ?\Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface $arguments = null
    ) {
        $invoice = $this->invoiceRepository->get($invoiceId);
        $order = $this->orderRepository->get($invoice->getOrderId());

        return $this->orderMutex->execute(
            (int) $order->getEntityId(),
            \Closure::fromCallable([$this, 'processRefund']),
            [
                $invoiceId,
                $items,
                $isOnline,
                $notify,
                $appendComment,
                $comment,
                $arguments
            ]
        );
    }

    /**
     * Refund process logic
     *
     * @param int $invoiceId
     * @param array $items
     * @param bool $isOnline
     * @param bool $notify
     * @param bool $appendComment
     * @param CreditmemoCommentCreationInterface|null $comment
     * @param CreditmemoCreationArgumentsInterface|null $arguments
     * @return int|null
     * @throws CouldNotRefundException
     * @throws DocumentValidationException
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod) This method is used in closure callback
     */
    private function processRefund(
        $invoiceId,
        array $items = [],
        bool $isOnline = false,
        bool $notify = false,
        bool $appendComment = false,
        ?\Magento\Sales\Api\Data\CreditmemoCommentCreationInterface $comment = null,
        ?\Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface $arguments = null
    ) {
        $invoice = $this->invoiceRepository->get($invoiceId);
        $order = $this->orderRepository->get($invoice->getOrderId());
        $creditmemo = $this->creditmemoDocumentFactory->createFromInvoice(
            $invoice,
            $items,
            $comment,
            ($appendComment && $notify),
            $arguments
        );

        $validationMessages = $this->validator->validate(
            $invoice,
            $order,
            $creditmemo,
            $items,
            $isOnline,
            $notify,
            $appendComment,
            $comment,
            $arguments
        );
        if ($validationMessages->hasMessages()) {
            throw new \Magento\Sales\Exception\DocumentValidationException(
                __("Creditmemo Document Validation Error(s):\n" . implode("\n", $validationMessages->getMessages()))
            );
        }
        try {
            $creditmemo->setState(\Magento\Sales\Model\Order\Creditmemo::STATE_REFUNDED);
            $order->setCustomerNoteNotify($notify);
            $order = $this->refundAdapter->refund($creditmemo, $order, $isOnline);
            $order->setState(
                $this->orderStateResolver->getStateForOrder($order, [])
            );
            $order->setStatus($this->config->getStateDefaultStatus($order->getState()));
            if (!$isOnline) {
                $invoice->setIsUsedForRefund(true);
                $invoice->setBaseTotalRefunded(
                    $invoice->getBaseTotalRefunded() + $creditmemo->getBaseGrandTotal()
                );
            }
            $this->invoiceRepository->save($invoice);
            $order = $this->orderRepository->save($order);
            $creditmemo = $this->creditmemoRepository->save($creditmemo);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new \Magento\Sales\Exception\CouldNotRefundException(
                __('Could not save a Creditmemo, see error log for details')
            );
        }
        if ($notify) {
            if (!$appendComment) {
                $comment = null;
            }
            $this->notifier->notify($order, $creditmemo, $comment);
        }

        return $creditmemo->getEntityId();
    }
}
