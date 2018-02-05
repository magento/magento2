<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesInventory\Model\Plugin\Order\Validation;

use Magento\Sales\Api\Data\CreditmemoCommentCreationInterface;
use Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Validation\RefundOrderInterface;
use Magento\SalesInventory\Model\Order\ReturnValidator;
use Magento\Sales\Model\ValidatorResultInterface;

/**
 * Class OrderRefundCreationArguments
 */
class OrderRefundCreationArguments
{
    /**
     * @var ReturnValidator
     */
    private $returnValidator;

    /**
     * OrderRefundCreationArguments constructor.
     * @param ReturnValidator $returnValidator
     */
    public function __construct(
        ReturnValidator $returnValidator
    ) {
        $this->returnValidator = $returnValidator;
    }

    /**
     * @param RefundOrderInterface $refundOrderValidator
     * @param \Closure $proceed
     * @param OrderInterface $order
     * @param CreditmemoInterface $creditmemo
     * @param array $items
     * @param bool $notify
     * @param bool $appendComment
     * @param CreditmemoCommentCreationInterface|null $comment
     * @param CreditmemoCreationArgumentsInterface|null $arguments
     * @return ValidatorResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundValidate(
        RefundOrderInterface $refundOrderValidator,
        \Closure $proceed,
        OrderInterface $order,
        CreditmemoInterface $creditmemo,
        array $items = [],
        $notify = false,
        $appendComment = false,
        CreditmemoCommentCreationInterface $comment = null,
        CreditmemoCreationArgumentsInterface $arguments = null
    ) {
        $validationResults = $proceed($order, $creditmemo, $items, $notify, $appendComment, $comment, $arguments);
        if ($this->isReturnToStockItems($arguments)) {
            return $validationResults;
        }

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
