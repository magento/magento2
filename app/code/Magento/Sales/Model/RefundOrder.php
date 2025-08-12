<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\RefundOrderInterface;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Sales\Model\Order\Creditmemo\NotifierInterface;
use Magento\Sales\Model\Order\CreditmemoDocumentFactory;
use Magento\Sales\Model\Order\OrderStateResolverInterface;
use Magento\Sales\Model\Order\RefundAdapterInterface;
use Magento\Sales\Model\Order\Validation\RefundOrderInterface as RefundOrderValidator;
use Psr\Log\LoggerInterface;

/**
 * Class RefundOrder for an order
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RefundOrder implements RefundOrderInterface
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
     * @var RefundOrderValidator
     */
    private $validator;

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
     * @var OrderMutexInterface
     */
    private $orderMutex;

    /**
     * RefundOrder constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param OrderStateResolverInterface $orderStateResolver
     * @param OrderRepositoryInterface $orderRepository
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param RefundAdapterInterface $refundAdapter
     * @param CreditmemoDocumentFactory $creditmemoDocumentFactory
     * @param RefundOrderValidator $validator
     * @param NotifierInterface $notifier
     * @param OrderConfig $config
     * @param LoggerInterface $logger
     * @param OrderMutex|null $orderMutex
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        OrderStateResolverInterface $orderStateResolver,
        OrderRepositoryInterface $orderRepository,
        CreditmemoRepositoryInterface $creditmemoRepository,
        RefundAdapterInterface $refundAdapter,
        CreditmemoDocumentFactory $creditmemoDocumentFactory,
        RefundOrderValidator $validator,
        NotifierInterface $notifier,
        OrderConfig $config,
        LoggerInterface $logger,
        ?OrderMutexInterface $orderMutex = null
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->orderStateResolver = $orderStateResolver;
        $this->orderRepository = $orderRepository;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->refundAdapter = $refundAdapter;
        $this->creditmemoDocumentFactory = $creditmemoDocumentFactory;
        $this->validator = $validator;
        $this->notifier = $notifier;
        $this->config = $config;
        $this->logger = $logger;
        $this->orderMutex = $orderMutex ?: ObjectManager::getInstance()->get(OrderMutexInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function execute(
        $orderId,
        array $items = [],
        $notify = false,
        $appendComment = false,
        ?\Magento\Sales\Api\Data\CreditmemoCommentCreationInterface $comment = null,
        ?\Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface $arguments = null
    ) {
        return $this->orderMutex->execute(
            (int) $orderId,
            \Closure::fromCallable([$this, 'createRefund']),
            [
                $orderId,
                $items,
                $notify,
                $appendComment,
                $comment,
                $arguments
            ]
        );
    }

    /**
     * Creates refund for provided order ID
     *
     * @param int $orderId
     * @param array $items
     * @param bool $notify
     * @param bool $appendComment
     * @param \Magento\Sales\Api\Data\InvoiceCommentCreationInterface|null $comment
     * @param \Magento\Sales\Api\Data\InvoiceCreationArgumentsInterface|null $arguments
     * @return int
     * @throws \Magento\Sales\Api\Exception\DocumentValidationExceptionInterface
     * @throws \Magento\Sales\Api\Exception\CouldNotRefundException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \DomainException
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function createRefund(
        $orderId,
        array $items = [],
        $notify = false,
        $appendComment = false,
        ?\Magento\Sales\Api\Data\CreditmemoCommentCreationInterface $comment = null,
        ?\Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface $arguments = null
    ) {
        $order = $this->orderRepository->get($orderId);
        $creditmemo = $this->creditmemoDocumentFactory->createFromOrder(
            $order,
            $items,
            $comment,
            ($appendComment && $notify),
            $arguments
        );
        $validationMessages = $this->validator->validate(
            $order,
            $creditmemo,
            $items,
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
            $order = $this->refundAdapter->refund($creditmemo, $order);
            $orderState = $this->orderStateResolver->getStateForOrder($order, []);
            $order->setState($orderState);
            $statuses = $this->config->getStateStatuses($orderState, false);
            $status = in_array($order->getStatus(), $statuses, true)
                ? $order->getStatus()
                : $this->config->getStateDefaultStatus($orderState);
            $order->setStatus($status);

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
