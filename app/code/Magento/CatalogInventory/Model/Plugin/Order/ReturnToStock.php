<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Plugin\Order;

use Magento\CatalogInventory\Model\Order\ReturnProcessor;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class ReturnToStock
 */
class ReturnToStock
{
    /**
     * @var ReturnProcessor
     */
    private $returnProcessor;

    /**
     * @var CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * ReturnToStockPlugin constructor.
     *
     * @param ReturnProcessor $returnProcessor
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        ReturnProcessor $returnProcessor,
        CreditmemoRepositoryInterface $creditmemoRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->returnProcessor = $returnProcessor;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param \Magento\Sales\Model\RefundOrder|\Magento\Sales\Model\RefundInvoice $refundService
     * @param int $resultEntityId
     * @param int $orderId
     * @param array $items
     * @param bool $notify
     * @param bool $appendComment
     * @param \Magento\Sales\Api\Data\CreditmemoCommentCreationInterface|null $comment
     * @param \Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface|null $arguments
     * @return int
     */
    public function afterExecute(
        $refundService,
        $resultEntityId,
        $orderId,
        array $items = [],
        $notify = false,
        $appendComment = false,
        \Magento\Sales\Api\Data\CreditmemoCommentCreationInterface $comment = null,
        \Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface $arguments = null
    ) {
        $order = $this->orderRepository->get($orderId);

        $returnToStockItems = [];
        if ($arguments !== null && $arguments->getReturnToStockItems() !== null) {
            $returnToStockItems = $arguments->getReturnToStockItems();
        }

        $creditmemo = $this->creditmemoRepository->get($resultEntityId);
        $this->returnProcessor->execute($creditmemo, $order, $returnToStockItems);

        return $resultEntityId;
    }
}
