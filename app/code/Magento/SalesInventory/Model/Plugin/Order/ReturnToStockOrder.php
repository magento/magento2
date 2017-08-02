<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesInventory\Model\Plugin\Order;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\SalesInventory\Model\Order\ReturnProcessor;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\RefundOrderInterface;

/**
 * Class ReturnToStock
 * @since 2.1.3
 */
class ReturnToStockOrder
{
    /**
     * @var ReturnProcessor
     * @since 2.1.3
     */
    private $returnProcessor;

    /**
     * @var CreditmemoRepositoryInterface
     * @since 2.1.3
     */
    private $creditmemoRepository;

    /**
     * @var OrderRepositoryInterface
     * @since 2.1.3
     */
    private $orderRepository;

    /**
     * @var StockConfigurationInterface
     * @since 2.1.3
     */
    private $stockConfiguration;

    /**
     * ReturnToStockPlugin constructor.
     *
     * @param ReturnProcessor $returnProcessor
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param StockConfigurationInterface $stockConfiguration
     * @since 2.1.3
     */
    public function __construct(
        ReturnProcessor $returnProcessor,
        CreditmemoRepositoryInterface $creditmemoRepository,
        OrderRepositoryInterface $orderRepository,
        StockConfigurationInterface $stockConfiguration
    ) {
        $this->returnProcessor = $returnProcessor;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->orderRepository = $orderRepository;
        $this->stockConfiguration = $stockConfiguration;
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function afterExecute(
        RefundOrderInterface $refundService,
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
        $isAutoReturn = $this->stockConfiguration->isAutoReturnEnabled();
        if ($isAutoReturn || !empty($returnToStockItems)) {
            $creditmemo = $this->creditmemoRepository->get($resultEntityId);
            $this->returnProcessor->execute($creditmemo, $order, $returnToStockItems, $isAutoReturn);
        }
        return $resultEntityId;
    }
}
