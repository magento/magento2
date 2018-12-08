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

use Magento\Framework\Exception\LocalizedException;
use Magento\Paypal\Model\Adminhtml\Express;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Validation\CanInvoice;

/**
 * Decorates order canInvoice validator method for PayPal Express payments
 * when payment action set to "Order".
 */
class ValidatorCanInvoice
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
     * @param CanInvoice $subject
<<<<<<< HEAD
     * @param $result
=======
     * @param array $result
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     * @param OrderInterface $order
     * @return array
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
<<<<<<< HEAD
    public function afterValidate(CanInvoice $subject, $result, OrderInterface $order): array
=======
    public function afterValidate(CanInvoice $subject, array $result, OrderInterface $order): array
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    {
        if ($this->express->isOrderAuthorizationAllowed($order->getPayment())) {
            $result[] = __('An invoice cannot be created when none of authorization transactions available.');
        }

        return $result;
    }
}
