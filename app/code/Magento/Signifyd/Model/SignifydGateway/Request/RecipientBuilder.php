<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Request;

use Magento\Sales\Model\Order;

/**
 * Prepare data related to person or organization receiving the items purchased.
 */
class RecipientBuilder
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
     * Returns recipient data params
     *
     * @param Order $order
     * @return array
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
