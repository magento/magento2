<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
declare(strict_types=1);

=======
>>>>>>> upstream/2.2-develop
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
     * @param array $result
=======
     * @param $result
>>>>>>> upstream/2.2-develop
     * @param OrderInterface $order
     * @return array
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
<<<<<<< HEAD
    public function afterValidate(CanInvoice $subject, array $result, OrderInterface $order): array
=======
    public function afterValidate(CanInvoice $subject, $result, OrderInterface $order): array
>>>>>>> upstream/2.2-develop
    {
        if ($this->express->isOrderAuthorizationAllowed($order->getPayment())) {
            $result[] = __('An invoice cannot be created when none of authorization transactions available.');
        }

        return $result;
    }
}
