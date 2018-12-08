<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);

>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
<<<<<<< HEAD
     * @param $result
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterCanInvoice(Order $order, $result): bool
    {
        if (!$order->getPayment()) {
            return false;
        }

=======
     * @param bool $result
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterCanInvoice(Order $order, bool $result): bool
    {
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        if ($this->express->isOrderAuthorizationAllowed($order->getPayment())) {
            return false;
        }

        return $result;
    }
}
