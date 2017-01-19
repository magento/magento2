<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Request;

use Magento\Sales\Model\Order;

/**
 * Prepare data related to the card that was used for the purchase and its cardholder.
 */
class CardBuilder
{
    /**
     * @var AddressBuilder
     */
    private $addressBuilder;

    /**
     * @param AddressBuilder $addressBuilder
     */
    public function __construct(
        AddressBuilder $addressBuilder
    ) {
        $this->addressBuilder = $addressBuilder;
    }

    /**
     * Returns card data params based on payment and billing address info
     *
     * @param Order $order
     * @return array
     */
    public function build(Order $order)
    {
        $result = [];
        $address = $order->getBillingAddress();
        if ($address === null) {
            return $result;
        }

        $payment = $order->getPayment();
        $result = [
            'card' => [
                'cardHolderName' => $address->getFirstname() . ' ' . $address->getLastname(),
                'last4' => $payment->getCcLast4(),
                'expiryMonth' => $payment->getCcExpMonth(),
                'expiryYear' =>  $payment->getCcExpYear(),
                'billingAddress' => $this->addressBuilder->build($address)
            ]
        ];

        return $result;
    }
}
