<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Updates invoice transaction id for PayPal PayflowPro payment.
 */
class TransparentOrderPayment
{
    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @param InvoiceRepositoryInterface $invoiceRepository
     */
    public function __construct(InvoiceRepositoryInterface $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * Updates invoice transaction id.
     *
     * Accepting PayPal PayflowPro payment actually means executing new reference transaction
     * based on account verification. So for existing pending invoice, transaction id should be updated
     * with the id of last reference transaction.
     *
     * @param Payment $subject
     * @param Payment $result
     * @return Payment
     * @throws LocalizedException
     */
    public function afterAccept(Payment $subject, Payment $result): Payment
    {
        $paymentMethod = $subject->getMethodInstance();
        if (!$paymentMethod instanceof \Magento\Paypal\Model\Payflow\Transparent) {
            return $result;
        }

        $invoices = iterator_to_array($subject->getOrder()->getInvoiceCollection());
        $invoice = reset($invoices);
        if ($invoice) {
            $invoice->setTransactionId($subject->getLastTransId());
            $this->invoiceRepository->save($invoice);
        }

        return $result;
    }
}
