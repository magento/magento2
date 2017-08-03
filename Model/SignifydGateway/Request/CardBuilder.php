<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Request;

use Magento\Sales\Model\Order;

/**
 * Prepare data related to the card that was used for the purchase and its cardholder.
 * @since 2.2.0
 */
class CardBuilder
{
    /**
     * @var AddressBuilder
     * @since 2.2.0
     */
    private $addressBuilder;

    /**
     * @param AddressBuilder $addressBuilder
     * @since 2.2.0
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
     * @since 2.2.0
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
