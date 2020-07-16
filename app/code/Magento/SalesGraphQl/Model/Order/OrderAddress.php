<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Order;

use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Address as SalesOrderAddress;

/**
 * Class to fetch the order address details
 */
class OrderAddress
{
    /**
     * Get the order Shipping address
     *
     * @param OrderInterface $order
     * @return array
     */
    public function getOrderShippingAddress(
        OrderInterface $order
    ): array {
        $shippingAddress = [];
        $orderAddresses = $order->getAddresses();
        foreach ($orderAddresses as $orderAddress) {
            if ($orderAddress->getDataByKey("address_type") === address::TYPE_SHIPPING) {
                $shippingAddress = $this->OrderAddressDataFormatter(
                    $orderAddress,
                    address::TYPE_SHIPPING
                );
            }
        }
        return $shippingAddress;
    }

    /**
     * Get the order billing address
     *
     * @param OrderInterface $order
     * @return array
     */
    public function getOrderBillingAddress(
        OrderInterface $order
    ): array {
        $billingAddress = [];
        $orderAddresses = $order->getAddresses();
        foreach ($orderAddresses as $orderAddress) {
            if ($orderAddress->getDataByKey("address_type") === address::TYPE_BILLING) {
                $billingAddress = $this->OrderAddressDataFormatter(
                    $orderAddress,
                    address::TYPE_BILLING
                );
            }
        }
        return $billingAddress;
    }

    /**
     * Customer Order address data formatter
     *
     * @param SalesOrderAddress $orderAddress
     * @param string $addressType
     * @return array
     */
    private function OrderAddressDataFormatter(
        SalesOrderAddress $orderAddress,
        string $addressType
    ): array {
        $orderAddressData = [];
        if ($addressType === $orderAddress->getDataByKey("address_type")) {
            $orderAddressData = [
                    'firstname' => $orderAddress->getDataByKey('firstname'),
                    'lastname' => $orderAddress->getDataByKey('lastname'),
                    'middlename' => $orderAddress->getDataByKey('middlename'),
                    'postcode' => $orderAddress->getDataByKey('postcode'),
                    'prefix' => $orderAddress->getDataByKey('prefix'),
                    'suffix' => $orderAddress->getDataByKey('suffix'),
                    'city' => $orderAddress->getDataByKey('city'),
                    'company' => $orderAddress->getDataByKey('company'),
                    'fax' => $orderAddress->getDataByKey('fax'),
                    'telephone' => $orderAddress->getDataByKey('telephone'),
                    'vat_id' => $orderAddress->getDataByKey('vat_id'),
                    'street' => explode("\n", $orderAddress->getDataByKey('street')),
                    'country_code' => $orderAddress->getDataByKey('country_id'),
                    'region' => $orderAddress->getDataByKey('region'),
                    'region_id' => $orderAddress->getDataByKey('region_id')
                ];
        }
        return $orderAddressData;
    }
}
