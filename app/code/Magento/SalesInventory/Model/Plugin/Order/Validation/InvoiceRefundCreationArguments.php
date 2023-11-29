<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesInventory\Model\Plugin\Order\Validation;

use Magento\Sales\Api\Data\CreditmemoCommentCreationInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Model\Order\Validation\RefundInvoiceInterface;
use Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\SalesInventory\Model\Order\ReturnValidator;
use Magento\Sales\Model\ValidatorResultInterface;

/**
 * Class CreditmemoCreationArguments
 */
class InvoiceRefundCreationArguments
{
    /**
     * InvoiceRefundCreationArguments constructor.
     * @param ReturnValidator $returnValidator
     */
    public function __construct(
        private readonly ReturnValidator $returnValidator
    ) {
    }

    /**
     * @param RefundInvoiceInterface $refundInvoiceValidator
     * @param ValidatorResultInterface $validationResults
     * @param InvoiceInterface $invoice
     * @param OrderInterface $order
     * @param CreditmemoInterface $creditmemo
     * @param array $items
     * @param bool $isOnline
     * @param bool $notify
     * @param bool $appendComment
     * @param CreditmemoCommentCreationInterface|null $comment
     * @param CreditmemoCreationArgumentsInterface|null $arguments
     * @return ValidatorResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function afterValidate(
        RefundInvoiceInterface $refundInvoiceValidator,
        ValidatorResultInterface $validationResults,
        InvoiceInterface $invoice,
        OrderInterface $order,
        CreditmemoInterface $creditmemo,
        array $items = [],
        $isOnline = false,
        $notify = false,
        $appendComment = false,
        CreditmemoCommentCreationInterface $comment = null,
        CreditmemoCreationArgumentsInterface $arguments = null
    ) {
        if ($this->isReturnToStockItems($arguments)) {
            return $validationResults;
        }

        /** @var int[] $returnToStockItems */
        $returnToStockItems = $arguments->getExtensionAttributes()->getReturnToStockItems();
        $validationMessage = $this->returnValidator->validate($returnToStockItems, $creditmemo);
        if ($validationMessage) {
            $validationResults->addMessage($validationMessage);
        }

        return $validationResults;
    }

    /**
     * @param CreditmemoCreationArgumentsInterface|null $arguments
     * @return bool
     */
    private function isReturnToStockItems($arguments)
    {
        return $arguments === null
        || $arguments->getExtensionAttributes() === null
        || $arguments->getExtensionAttributes()->getReturnToStockItems() === null;
    }
}
