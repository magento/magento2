<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Request;

use Magento\Sales\Model\Order;

/**
 * Prepare data related to person or organization receiving the items purchased
 * @since 2.2.0
 */
class RecipientBuilder
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
     * Returns recipient data params based on shipping address
     *
     * @param Order $order
     * @return array
     * @since 2.2.0
     */
    public function build(Order $order)
    {
        $result = [];
        $address = $order->getShippingAddress();
        if ($address === null) {
            return $result;
        }

        $result = [
            'recipient' => [
                'fullName' => $address->getName(),
                'confirmationEmail' =>  $address->getEmail(),
                'confirmationPhone' => $address->getTelephone(),
                'organization' => $address->getCompany(),
                'deliveryAddress' => $this->addressBuilder->build($address)
            ]
        ];

        return $result;
    }
}
