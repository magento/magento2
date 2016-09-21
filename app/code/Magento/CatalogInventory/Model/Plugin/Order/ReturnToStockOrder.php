<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Plugin\Order;

use Magento\CatalogInventory\Model\Order\ReturnProcessor;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\RefundOrderInterface;

/**
 * Class ReturnToStock
 */
class ReturnToStockOrder
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
     * @param RefundOrderInterface $refundService
     * @param int $resultEntityId
     * @param int $orderId
     * @param \Magento\Sales\Api\Data\CreditmemoItemCreationInterface[] $items
     * @param bool|null $notify
     * @param bool|null $appendComment
     * @param \Magento\Sales\Api\Data\CreditmemoCommentCreationInterface|null $comment
     * @param \Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface|null $arguments
     * @return int
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
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
        if ($arguments !== null
            && $arguments->getExtensionAttributes() !== null
            && $arguments->getExtensionAttributes()->getReturnToStockItems() !== null
        ) {
            $returnToStockItems = $arguments->getExtensionAttributes()->getReturnToStockItems();
        }

        $creditmemo = $this->creditmemoRepository->get($resultEntityId);
        $this->returnProcessor->execute($creditmemo, $order, $returnToStockItems);

        return $resultEntityId;
    }
}
