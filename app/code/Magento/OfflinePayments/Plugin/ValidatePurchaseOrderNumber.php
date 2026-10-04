<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OfflinePayments\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Magento\OfflinePayments\Model\Purchaseorder;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteManagement;

/**
 * Class ValidatePurchaseOrderNumber
 *
 * Validate purchase order number before submit order
 */
class ValidatePurchaseOrderNumber
{
    /**
     * Before submitOrder plugin.
     *
     * @param QuoteManagement $subject
     * @param Quote $quote
     * @param array $orderData
     * @return void
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSubmit(
        QuoteManagement $subject,
        Quote $quote,
        array $orderData = []
    ): void {
        $payment = $quote->getPayment();
        if ($payment->getMethod() === Purchaseorder::PAYMENT_METHOD_PURCHASEORDER_CODE
            && empty($payment->getPoNumber())) {
            throw new LocalizedException(__('Purchase order number is a required field.'));
        }
    }
}
