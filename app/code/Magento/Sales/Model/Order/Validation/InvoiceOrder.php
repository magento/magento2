<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Validation;

use Magento\Sales\Api\Data\InvoiceCommentCreationInterface;
use Magento\Sales\Api\Data\InvoiceCreationArgumentsInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Invoice\InvoiceValidatorInterface;
use Magento\Sales\Model\Order\InvoiceQuantityValidator;
use Magento\Sales\Model\Order\OrderValidatorInterface;
use Magento\Sales\Model\ValidatorResultInterface;
use Magento\Sales\Model\ValidatorResultMerger;

/**
 * Class InvoiceOrder
 * Validation for invoice order operation
 */
class InvoiceOrder implements InvoiceOrderInterface
{
    /**
     * @var InvoiceValidatorInterface
     */
    private $invoiceValidator;

    /**
     * @var OrderValidatorInterface
     */
    private $orderValidator;

    /**
     * @var ValidatorResultMerger
     */
    private $validatorResultMerger;

    /**
     * InvoiceOrder constructor.
     * @param InvoiceValidatorInterface $invoiceValidator
     * @param OrderValidatorInterface $orderValidator
     * @param ValidatorResultMerger $validatorResultMerger
     */
    public function __construct(
        InvoiceValidatorInterface $invoiceValidator,
        OrderValidatorInterface $orderValidator,
        ValidatorResultMerger $validatorResultMerger
    ) {
        $this->invoiceValidator = $invoiceValidator;
        $this->orderValidator = $orderValidator;
        $this->validatorResultMerger = $validatorResultMerger;
    }

    /**
     * @inheritdoc
     */
    public function validate(
        OrderInterface $order,
        InvoiceInterface $invoice,
        $capture = false,
        array $items = [],
        $notify = false,
        $appendComment = false,
        InvoiceCommentCreationInterface $comment = null,
        InvoiceCreationArgumentsInterface $arguments = null
    ) {
        return $this->validatorResultMerger->merge(
            $this->invoiceValidator->validate(
                $invoice,
                [InvoiceQuantityValidator::class]
            ),
            $this->orderValidator->validate(
                $order,
                [CanInvoice::class]
            )
        );
    }
}
