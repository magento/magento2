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
 * @since 2.1.3
 */
class OrderRefundCreationArguments
{
    /**
     * @var ReturnValidator
     * @since 2.1.3
     */
    private $returnValidator;

    /**
     * OrderRefundCreationArguments constructor.
     * @param ReturnValidator $returnValidator
     * @since 2.1.3
     */
    public function __construct(
        ReturnValidator $returnValidator
    ) {
        $this->returnValidator = $returnValidator;
    }

    /**
     * @param RefundOrderInterface $refundOrderValidator
     * @param ValidatorResultInterface $validationResults
     * @param OrderInterface $order
     * @param CreditmemoInterface $creditmemo
     * @param array $items
     * @param bool $notify
     * @param bool $appendComment
     * @param CreditmemoCommentCreationInterface|null $comment
     * @param CreditmemoCreationArgumentsInterface|null $arguments
     * @return ValidatorResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function afterValidate(
        RefundOrderInterface $refundOrderValidator,
        ValidatorResultInterface $validationResults,
        OrderInterface $order,
        CreditmemoInterface $creditmemo,
        array $items = [],
        $notify = false,
        $appendComment = false,
        CreditmemoCommentCreationInterface $comment = null,
        CreditmemoCreationArgumentsInterface $arguments = null
    ) {
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
     * @since 2.1.3
     */
    private function isReturnToStockItems($arguments)
    {
        return $arguments === null
        || $arguments->getExtensionAttributes() === null
        || $arguments->getExtensionAttributes()->getReturnToStockItems() === null;
    }
}
