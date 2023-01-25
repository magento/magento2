<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Order;

use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class to get the order address details
 */
class OrderAddress
{
    /**
     * Get the order Shipping address
     *
     * @param OrderInterface $order
     * @return array|null
     */
    public function getOrderShippingAddress(
        OrderInterface $order
    ): ?array {
        $shippingAddress = null;
        if ($order->getShippingAddress()) {
            $shippingAddress = $this->formatAddressData($order->getShippingAddress());
        }
        return $shippingAddress;
    }

    /**
     * Get the order billing address
     *
     * @param OrderInterface $order
     * @return array|null
     */
    public function getOrderBillingAddress(
        OrderInterface $order
    ): ?array {
        $billingAddress = null;
        if ($order->getBillingAddress()) {
            $billingAddress = $this->formatAddressData($order->getBillingAddress());
        }
        return $billingAddress;
    }

    /**
     * Customer Order address data formatter
     *
     * @param OrderAddressInterface $orderAddress
     * @return array
     */
    private function formatAddressData(
        OrderAddressInterface $orderAddress
    ): array {
        return
            [
                'firstname' => $orderAddress->getFirstname(),
                'lastname' => $orderAddress->getLastname(),
                'middlename' => $orderAddress->getMiddlename(),
                'postcode' => $orderAddress->getPostcode(),
                'prefix' => $orderAddress->getPrefix(),
                'suffix' => $orderAddress->getSuffix(),
                'street' => $orderAddress->getStreet(),
                'country_code' => $orderAddress->getCountryId(),
                'city' => $orderAddress->getCity(),
                'company' => $orderAddress->getCompany(),
                'fax' => $orderAddress->getFax(),
                'telephone' => $orderAddress->getTelephone(),
                'vat_id' => $orderAddress->getVatId(),
                'region_id' => $orderAddress->getRegionId(),
                'region' => $orderAddress->getRegion()
            ];
    }
}
