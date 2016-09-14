<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Invoice\Validation;

use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Invoice\InvoiceValidatorInterface;
use Magento\Sales\Model\Order\Validation\RefundArguments as OrderRefundArgumentsValidator;

/**
 * Class RefundArgumentValidator
 */
class RefundArguments
{
    /**
     * @var OrderRefundArgumentsValidator
     */
    private $refundArgumentsValidator;

    /**
     * @var InvoiceValidatorInterface
     */
    private $invoiceValidator;

    /**
     * @inheritDoc
     */
    public function __construct(
        OrderRefundArgumentsValidator $refundArgumentsValidator,
        InvoiceValidatorInterface $invoiceValidator
    ) {
        $this->refundArgumentsValidator = $refundArgumentsValidator;
        $this->invoiceValidator = $invoiceValidator;
    }

    /**
     * @param InvoiceInterface $invoice
     * @param OrderInterface $order
     * @param CreditmemoInterface $creditmemo
     * @param array $items
     * @param bool $isOnline
     * @param bool $notify
     * @param bool $appendComment
     * @param \Magento\Sales\Api\Data\CreditmemoCommentCreationInterface|null $comment
     * @param \Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface|null $arguments
     * @return array
     */
    public function validate(
        InvoiceInterface $invoice,
        OrderInterface $order,
        CreditmemoInterface $creditmemo,
        array $items = [],
        $isOnline = false,
        $notify = false,
        $appendComment = false,
        \Magento\Sales\Api\Data\CreditmemoCommentCreationInterface $comment = null,
        \Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface $arguments = null
    ) {
        $validationMessages = $this->refundArgumentsValidator->validate(
            $order,
            $creditmemo,
            $items,
            $notify,
            $appendComment,
            $comment,
            $arguments
        );
        $invoiceValidationResult = $this->invoiceValidator->validate(
            $invoice,
            [
                \Magento\Sales\Model\Order\Invoice\Validation\CanRefund::class
            ]
        );

        return array_merge(
            $validationMessages,
            $invoiceValidationResult
        );
    }
}
