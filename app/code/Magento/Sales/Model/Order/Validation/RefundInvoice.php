<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Validation;

use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Invoice\InvoiceValidatorInterface;
use Magento\Sales\Model\Order\Creditmemo\CreditmemoValidatorInterface;
use Magento\Sales\Model\Order\Creditmemo\Item\Validation\CreationQuantityValidator;
use Magento\Sales\Model\Order\Creditmemo\ItemCreationValidatorInterface;
use Magento\Sales\Model\Order\Creditmemo\Validation\QuantityValidator;
use Magento\Sales\Model\Order\Creditmemo\Validation\TotalsValidator;
use Magento\Sales\Model\Order\OrderValidatorInterface;
use Magento\Sales\Model\ValidatorResultMerger;

/**
 * Class RefundInvoice
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RefundInvoice implements RefundInvoiceInterface
{
    /**
     * @var OrderValidatorInterface
     */
    private $orderValidator;

    /**
     * @var CreditmemoValidatorInterface
     */
    private $creditmemoValidator;

    /**
     * @var ItemCreationValidatorInterface
     */
    private $itemCreationValidator;

    /**
     * @var InvoiceValidatorInterface
     */
    private $invoiceValidator;

    /**
     * @var ValidatorResultMerger
     */
    private $validatorResultMerger;

    /**
     * RefundArguments constructor.
     * @param OrderValidatorInterface $orderValidator
     * @param CreditmemoValidatorInterface $creditmemoValidator
     * @param ItemCreationValidatorInterface $itemCreationValidator
     * @param InvoiceValidatorInterface $invoiceValidator
     * @param ValidatorResultMerger $validatorResultMerger
     */
    public function __construct(
        OrderValidatorInterface $orderValidator,
        CreditmemoValidatorInterface $creditmemoValidator,
        ItemCreationValidatorInterface $itemCreationValidator,
        InvoiceValidatorInterface $invoiceValidator,
        ValidatorResultMerger $validatorResultMerger
    ) {
        $this->orderValidator = $orderValidator;
        $this->creditmemoValidator = $creditmemoValidator;
        $this->itemCreationValidator = $itemCreationValidator;
        $this->invoiceValidator = $invoiceValidator;
        $this->validatorResultMerger = $validatorResultMerger;
    }

    /**
     * @inheritdoc
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
        $orderValidationResult = $this->orderValidator->validate(
            $order,
            [
                CanRefund::class
            ]
        );
        $creditmemoValidationResult = $this->creditmemoValidator->validate(
            $creditmemo,
            [
                QuantityValidator::class,
                TotalsValidator::class
            ]
        );

        $itemsValidation = [];
        foreach ($items as $item) {
            $itemValidation = $this->itemCreationValidator->validate(
                $item,
                [CreationQuantityValidator::class],
                $order
            )->getMessages();

            $itemsValidation = array_merge($itemsValidation, $itemValidation);
        }

        $invoiceValidationResult = $this->invoiceValidator->validate(
            $invoice,
            [
                \Magento\Sales\Model\Order\Invoice\Validation\CanRefund::class
            ]
        );

        return $this->validatorResultMerger->merge(
            $orderValidationResult,
            $creditmemoValidationResult,
            $invoiceValidationResult->getMessages(),
            $itemsValidation
        );
    }
}
