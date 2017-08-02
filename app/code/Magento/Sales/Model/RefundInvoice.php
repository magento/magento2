<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\RefundInvoiceInterface;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Sales\Model\Order\Creditmemo\NotifierInterface;
use Magento\Sales\Model\Order\CreditmemoDocumentFactory;
use Magento\Sales\Model\Order\OrderStateResolverInterface;
use Magento\Sales\Model\Order\RefundAdapterInterface;
use Magento\Sales\Model\Order\Validation\RefundInvoiceInterface as RefundInvoiceValidator;
use Psr\Log\LoggerInterface;

/**
 * Class RefundInvoice
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.2.0
 */
class RefundInvoice implements RefundInvoiceInterface
{
    /**
     * @var ResourceConnection
     * @since 2.2.0
     */
    private $resourceConnection;

    /**
     * @var OrderStateResolverInterface
     * @since 2.2.0
     */
    private $orderStateResolver;

    /**
     * @var OrderRepositoryInterface
     * @since 2.2.0
     */
    private $orderRepository;

    /**
     * @var InvoiceRepositoryInterface
     * @since 2.2.0
     */
    private $invoiceRepository;

    /**
     * @var CreditmemoRepositoryInterface
     * @since 2.2.0
     */
    private $creditmemoRepository;

    /**
     * @var RefundAdapterInterface
     * @since 2.2.0
     */
    private $refundAdapter;

    /**
     * @var CreditmemoDocumentFactory
     * @since 2.2.0
     */
    private $creditmemoDocumentFactory;

    /**
     * @var NotifierInterface
     * @since 2.2.0
     */
    private $notifier;

    /**
     * @var OrderConfig
     * @since 2.2.0
     */
    private $config;

    /**
     * @var LoggerInterface
     * @since 2.2.0
     */
    private $logger;

    /**
     * @var RefundInvoiceValidator
     * @since 2.2.0
     */
    private $validator;

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
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.2.0
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
        LoggerInterface $logger
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
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function execute(
        $invoiceId,
        array $items = [],
        $isOnline = false,
        $notify = false,
        $appendComment = false,
        \Magento\Sales\Api\Data\CreditmemoCommentCreationInterface $comment = null,
        \Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface $arguments = null
    ) {
        $connection = $this->resourceConnection->getConnection('sales');
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
        $connection->beginTransaction();
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
            $connection->commit();
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $connection->rollBack();
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
