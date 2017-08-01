<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Data;

use Magento\Payment\Model\InfoInterface;

/**
 * Class \Magento\Payment\Gateway\Data\PaymentDataObject
 *
 * @since 2.0.0
 */
class PaymentDataObject implements PaymentDataObjectInterface
{
    /**
     * @var OrderAdapterInterface
     * @since 2.0.0
     */
    private $order;

    /**
     * @var InfoInterface
     * @since 2.0.0
     */
    private $payment;

    /**
     * @param OrderAdapterInterface $order
     * @param InfoInterface $payment
     * @since 2.0.0
     */
    public function __construct(
        OrderAdapterInterface $order,
        InfoInterface $payment
    ) {
        $this->order = $order;
        $this->payment = $payment;
    }

    /**
     * Returns order
     *
     * @return OrderAdapterInterface
     * @since 2.0.0
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Returns payment
     *
     * @return InfoInterface
     * @since 2.0.0
     */
    public function getPayment()
    {
        return $this->payment;
    }
}
