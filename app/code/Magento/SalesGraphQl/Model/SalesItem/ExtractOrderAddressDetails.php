<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\SalesItem;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class to extract the order address details
 */
class ExtractOrderAddressDetails
{
    /**
     * Get Shipping address details
     *
     * @param OrderInterface $order
     * @return array
     */
    public function getShippingAddressDetails(
        OrderInterface $order
    ): array {
        $shippingAddressFields = [];
        $shippingAddressData = [];
        $orderAddresses = $order->getAddresses();
        foreach ($orderAddresses as $orderAddress) {
            $addressType = $orderAddress->getDataByKey("address_type");
            if ($addressType === 'shipping') {
                $shippingAddressData = $orderAddress->getData();
                $shippingAddressFields = [
                    'id' => $orderAddress->getDataByKey('entity_id'),
                    'street' => $orderAddress->getDataByKey('street'),
                    'country_code' => $orderAddress->getDataByKey('country_id'),
                    'region' => [
                        'region' => $orderAddress->getDataByKey('region'),
                        'region_id' => $orderAddress->getDataByKey('region_id'),
                        'region_code' => $orderAddress->getDataByKey('region')
                    ],
                ];
            }
        }
        return array_merge($shippingAddressData, $shippingAddressFields);
    }

    /**
     * Get Billing address details
     *
     * @param OrderInterface $order
     * @return array
     */
    public function getBillingAddressDetails(
        OrderInterface $order
    ): array {
        $billingAddressFields = [];
        $billingAddressFieldsData = [];
        $orderAddresses = $order->getAddresses();
        foreach ($orderAddresses as $orderAddress) {
            $addressType = $orderAddress->getDataByKey("address_type");
            if ($addressType === 'billing') {
                $billingAddressFieldsData = $orderAddress->getData();
                $billingAddressFields = [
                    'id' => $orderAddress->getDataByKey('entity_id'),
                    'street' => $orderAddress->getDataByKey('street'),
                    'country_code' => $orderAddress->getDataByKey('country_id'),
                    'region' => [
                        'region' => $orderAddress->getDataByKey('region'),
                        'region_id' => $orderAddress->getDataByKey('region_id'),
                        'region_code' => $orderAddress->getDataByKey('region')
                    ],
                ];
            }
        }
        return array_merge($billingAddressFieldsData, $billingAddressFields);
    }
}
