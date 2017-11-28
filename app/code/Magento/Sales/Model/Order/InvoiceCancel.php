<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\InvoiceCancelInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;

/**
 * Class InvoiceCancel
 */
class InvoiceCancel implements InvoiceCancelInterface
{

    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * InvoiceCancel constructor.
     * @param InvoiceRepositoryInterface $invoiceRepository
     */
    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository
    ) {
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * Cancel invoice
     *
     * @param int $invoiceId
     * @return bool
     */
    public function cancel($invoiceId)
    {
        /** @var \Magento\Sales\Api\Data\InvoiceInterface $invoice */
        $invoice = $this->invoiceRepository->get($invoiceId);
        if ($invoice && $invoice->canCancel()) {
            $invoice->cancel();
            $this->invoiceRepository->save($invoice);
            return true;
        }

        return false;
    }
}
