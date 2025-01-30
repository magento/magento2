<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesInventory\Model\Plugin\Order;

use Magento\Sales\Api\Data\CreditmemoCommentCreationInterface;
use Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\CreditmemoDocumentFactory;

/**
 * Synchronize "back_to_stock" with extension attribute "return_to_stock_items"
 *
 * @see \Magento\SalesInventory\Observer\RefundOrderInventoryObserver
 */
class CreditmemoDocumentFactoryPlugin
{
    /**
     * Synchronize "back_to_stock" with extension attribute "return_to_stock_items" for creditmemo items
     *
     * @param CreditmemoDocumentFactory $subject
     * @param CreditmemoInterface $creditmemo
     * @param OrderInterface $order
     * @param array $items
     * @param CreditmemoCommentCreationInterface|null $comment
     * @param bool $appendComment
     * @param CreditmemoCreationArgumentsInterface|null $arguments
     * @return CreditmemoInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreateFromOrder(
        CreditmemoDocumentFactory $subject,
        CreditmemoInterface $creditmemo,
        OrderInterface $order,
        array $items = [],
        ?CreditmemoCommentCreationInterface $comment = null,
        $appendComment = false,
        ?CreditmemoCreationArgumentsInterface $arguments = null
    ): CreditmemoInterface {
        if ($arguments !== null
            && $arguments->getExtensionAttributes() !== null
            && $arguments->getExtensionAttributes()->getReturnToStockItems() !== null
        ) {
            $returnToStockItems = $arguments->getExtensionAttributes()->getReturnToStockItems();
            foreach ($creditmemo->getItems() as $item) {
                if (in_array($item->getOrderItemId(), $returnToStockItems)) {
                    $item->setBackToStock(true);
                }
            }
        }
        return $creditmemo;
    }

    /**
     * Synchronize "back_to_stock" with extension attribute "return_to_stock_items" for creditmemo items
     *
     * @param CreditmemoDocumentFactory $subject
     * @param CreditmemoInterface $creditmemo
     * @param InvoiceInterface $invoice
     * @param array $items
     * @param CreditmemoCommentCreationInterface|null $comment
     * @param bool $appendComment
     * @param CreditmemoCreationArgumentsInterface|null $arguments
     * @return CreditmemoInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreateFromInvoice(
        CreditmemoDocumentFactory $subject,
        CreditmemoInterface $creditmemo,
        InvoiceInterface $invoice,
        array $items = [],
        ?CreditmemoCommentCreationInterface $comment = null,
        $appendComment = false,
        ?CreditmemoCreationArgumentsInterface $arguments = null
    ): CreditmemoInterface {
        if ($arguments !== null
            && $arguments->getExtensionAttributes() !== null
            && $arguments->getExtensionAttributes()->getReturnToStockItems() !== null
        ) {
            $returnToStockItems = $arguments->getExtensionAttributes()->getReturnToStockItems();
            foreach ($creditmemo->getItems() as $item) {
                if (in_array($item->getOrderItemId(), $returnToStockItems)) {
                    $item->setBackToStock(true);
                }
            }
        }
        return $creditmemo;
    }
}
