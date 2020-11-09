<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Plugin\Model\Service\Invoice;

use Magento\Framework\DB\TransactionFactory;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Service\InvoiceService;

/**
 * Plugin to add transaction comment after capture invoice
 */
class AddTransactionCommentAfterCapture
{
    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param TransactionFactory $transactionFactory
     */
    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository,
        TransactionFactory $transactionFactory
    ) {
        $this->transactionFactory = $transactionFactory;
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * Add transaction comment to the order after capture invoice
     *
     * @param InvoiceService $subject
     * @param bool $result
     * @param int $invoiceId
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSetCapture(InvoiceService $subject, bool $result, $invoiceId): bool
    {
        if ($result) {
            $invoice = $this->invoiceRepository->get($invoiceId);
            $invoice->getOrder()->setIsInProcess(true);
            $this->transactionFactory->create()
                ->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->save();
        }

        return $result;
    }
}
