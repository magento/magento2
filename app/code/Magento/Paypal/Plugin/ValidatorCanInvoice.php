<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @param OrderInterface $order
     * @return array
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
<<<<<<< HEAD
    public function afterValidate(CanInvoice $subject, $result, OrderInterface $order): array
=======
    public function afterValidate(CanInvoice $subject, array $result, OrderInterface $order): array
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        if ($this->express->isOrderAuthorizationAllowed($order->getPayment())) {
            $result[] = __('An invoice cannot be created when none of authorization transactions available.');
        }

        return $result;
    }
}
