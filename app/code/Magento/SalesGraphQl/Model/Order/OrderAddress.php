<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Order;

use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class to fetch the order address details
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
    ) {
        $shippingAddress = null;
        $orderShippingAddress = $order->getShippingAddress() ?? null;
        if ($orderShippingAddress) {
            if ($orderShippingAddress->getAddressType()  === ADDRESS::TYPE_SHIPPING) {
                $shippingAddress = $this->OrderAddressDataFormatter($orderShippingAddress);
            }
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
    ) {
        $billingAddress = null;
        $orderBillingAddress = $order->getBillingAddress() ?? null;
        if ($orderBillingAddress) {
            if ($orderBillingAddress->getAddressType() === ADDRESS::TYPE_BILLING) {
                $billingAddress = $this->OrderAddressDataFormatter($orderBillingAddress);
            }
        }
        return $billingAddress;
    }

    /**
     * Customer Order address data formatter
     *
     * @param OrderAddressInterface $orderAddress
     * @return array
     */
    private function OrderAddressDataFormatter(
        OrderAddressInterface $orderAddress
    ): array {
        return
            [
                'firstname' => $orderAddress->getFirstname(),
                'lastname' => $orderAddress->getLastname(),
                'middlename' => $orderAddress->getMiddlename(),
                'postcode' => $orderAddress->getPostcode(),
                'prefix' => $orderAddress->getFirstname(),
                'suffix' => $orderAddress->getFirstname(),
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
