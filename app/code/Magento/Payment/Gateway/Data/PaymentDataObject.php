<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Payment\Gateway\Data;

use Magento\Payment\Model\InfoInterface;

class PaymentDataObject implements PaymentDataObjectInterface
{
    /**
     * @var OrderAdapterInterface
     */
    private $order;

    /**
     * @var InfoInterface
     */
    private $payment;

    /**
     * @param OrderAdapterInterface $order
     * @param InfoInterface $payment
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
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Returns payment
     *
     * @return InfoInterface
     */
    public function getPayment()
    {
        return $this->payment;
    }
}
