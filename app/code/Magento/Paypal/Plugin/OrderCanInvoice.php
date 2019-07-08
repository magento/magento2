<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Plugin;

use Magento\Paypal\Model\Adminhtml\Express;
use Magento\Sales\Model\Order;

/**
 * Decorates Order::canInvoice method for PayPal Express payments.
 */
class OrderCanInvoice
{
    /**
     * @var Express
     */
    private $express;

    /**
     * Initialize dependencies.
     *
     * @param Express $express
     */
    public function __construct(Express $express)
    {
        $this->express = $express;
    }

    /**
     * Checks a possibility to invoice of PayPal Express payments when payment action is "order".
     *
     * @param Order $order
     * @param bool $result
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterCanInvoice(Order $order, bool $result): bool
    {
        if (!$order->getPayment()) {
            return false;
        }

        if ($this->express->isOrderAuthorizationAllowed($order->getPayment())) {
            return false;
        }

        return $result;
    }
}
