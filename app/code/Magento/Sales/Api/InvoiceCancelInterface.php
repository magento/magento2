<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api;

/**
 * Interface InvoiceCancelInterface
 *
 * @package Magento\Sales\Api
 */
interface InvoiceCancelInterface
{

    /**
     * Cancel invoice
     *
     * @param int $invoiceId
     * @return bool
     */
    public function cancel($invoiceId);
}
