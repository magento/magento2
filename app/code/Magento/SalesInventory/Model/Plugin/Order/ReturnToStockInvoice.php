<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesInventory\Model\Plugin\Order;

/**
 * Class ReturnToStockInvoice
 * @since 2.2.0
 */
class ReturnToStockInvoice
{
    /**
     * @var \Magento\SalesInventory\Model\Order\ReturnProcessor
     * @since 2.2.0
     */
    private $returnProcessor;

    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface
     * @since 2.2.0
     */
    private $creditmemoRepository;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     * @since 2.2.0
     */
    private $orderRepository;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     * @since 2.2.0
     */
    private $invoiceRepository;

    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     * @since 2.2.0
     */
    private $stockConfiguration;

    /**
     * ReturnToStockInvoice constructor.
     * @param \Magento\SalesInventory\Model\Order\ReturnProcessor $returnProcessor
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @since 2.2.0
     */
    public function __construct(
        \Magento\SalesInventory\Model\Order\ReturnProcessor $returnProcessor,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
    ) {
        $this->returnProcessor = $returnProcessor;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * @param \Magento\Sales\Api\RefundInvoiceInterface $refundService
     * @param int $resultEntityId
     * @param int $invoiceId
     * @param \Magento\Sales\Api\Data\CreditmemoItemCreationInterface[] $items
     * @param bool|null $isOnline
     * @param bool|null $notify
     * @param bool|null $appendComment
     * @param \Magento\Sales\Api\Data\CreditmemoCommentCreationInterface|null $comment
     * @param \Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface|null $arguments
     * @return int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function afterExecute(
        \Magento\Sales\Api\RefundInvoiceInterface $refundService,
        $resultEntityId,
        $invoiceId,
        array $items = [],
        $isOnline = false,
        $notify = false,
        $appendComment = false,
        \Magento\Sales\Api\Data\CreditmemoCommentCreationInterface $comment = null,
        \Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface $arguments = null
    ) {
        $invoice = $this->invoiceRepository->get($invoiceId);
        $order = $this->orderRepository->get($invoice->getOrderId());

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
