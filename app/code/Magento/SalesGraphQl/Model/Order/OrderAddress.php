<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Order;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\CustomerGraphQl\Model\Customer\Address\GetCustomerAddress;
use Magento\CustomerGraphQl\Model\Customer\ExtractCustomerData;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class to get the order address details
 */
class OrderAddress
{
    /**
     * @var GetCustomerAddress
     */
    private $getCustomerAddress;

    /**
     * @var ExtractCustomerData
     */
    private $extractCustomerData;

    /**
     * @param GetCustomerAddress $getCustomerAddress
     * @param ExtractCustomerData $extractCustomerData
     */
    public function __construct(
        GetCustomerAddress $getCustomerAddress,
        ExtractCustomerData $extractCustomerData
    ) {
        $this->getCustomerAddress = $getCustomerAddress;
        $this->extractCustomerData = $extractCustomerData;
    }

    /**
     * Get the order Shipping address
     *
     * @param OrderInterface $order
     * @param array $addressIds
     * @return array
     */
    public function getShippingAddress(
        OrderInterface $order,
        array $addressIds
    ): array {
        $shippingAddress = [];
        $orderAddresses = $order->getAddresses();
        foreach ($orderAddresses as $orderAddress) {
            $addressType = $orderAddress->getDataByKey("address_type");
            if ($addressType === 'shipping') {
                $customerAddressId = (int)$orderAddress->getDataByKey('customer_address_id');
                if (in_array($customerAddressId, $addressIds)) {
                    $customerData = $this->getCustomerAddress->execute(
                        $customerAddressId,
                        (int)$order->getCustomerId()
                    );
                    $shippingAddress = $this->extractOrderAddress($customerData);
                } else {
                    $shippingAddress = $this->curateCustomerOrderAddress($order, $addressType);
                }
            }
        }
        return $shippingAddress;
    }

    /**
     * Get the order billing address
     *
     * @param OrderInterface $order
     * @param array $addressIds
     * @return array
     */
    public function getBillingAddress(
        OrderInterface $order,
        array $addressIds
    ): array {
        $billingAddress = [];
        $orderAddresses = $order->getAddresses();
        foreach ($orderAddresses as $orderAddress) {
            $addressType = $orderAddress->getDataByKey("address_type");
            if ($addressType === 'billing') {
                $customerAddressId = (int)$orderAddress->getDataByKey('customer_address_id');
                if (in_array($customerAddressId, $addressIds)) {
                    $customerData = $this->getCustomerAddress->execute(
                        $customerAddressId,
                        (int)$order->getCustomerId()
                    );
                    $billingAddress = $this->extractOrderAddress($customerData);
                } else {
                    $billingAddress = $this->curateCustomerOrderAddress($order, $addressType);
                }
            }
        }
        return $billingAddress;
    }

    /**
     * Customer Order address data formatter
     *
     * @param OrderInterface $order
     * @param string $addressType
     * @return array
     */
    private function curateCustomerOrderAddress(
        OrderInterface $order,
        string $addressType
    ): array {
        $orderAddressFields = [];
        $orderAddressData = [];
        $orderAddresses = $order->getAddresses();
        foreach ($orderAddresses as $orderAddress) {
            if ($addressType === $orderAddress->getDataByKey("address_type")) {
                $orderAddressData = $orderAddress->getData();
                $orderAddressFields = [
                    'id' => $orderAddress->getDataByKey('entity_id'),
                    'street' => [$street = $orderAddress->getDataByKey('street')],
                    'country_code' => $orderAddress->getDataByKey('country_id'),
                    'region' => [
                        'region' => $orderAddress->getDataByKey('region'),
                        'region_id' => $orderAddress->getDataByKey('region_id'),
                        'region_code' => $orderAddress->getDataByKey('region')
                    ],
                    'default_billing' => 0,
                    'default_shipping' => 0,
                    'extension_attributes' => [],
                ];
            }
        }
        return array_merge($orderAddressData, $orderAddressFields);
    }

    private function extractOrderAddress(AddressInterface $customerData)
    {
        return [
            'id' => $customerData->getId(),
            'firstname' => $customerData->getFirstname(),
            'lastname' => $customerData->getLastname(),
            'middlename' => $customerData->getMiddlename(),
            'postcode' => $customerData->getPostcode(),
            'prefix' => $customerData->getFirstname(),
            'suffix' => $customerData->getFirstname(),
            'street' => $customerData->getStreet(),
            'country_code' => $customerData->getCountryId(),
            'city' => $customerData->getCity(),
            'company' => $customerData->getCompany(),
            'fax' => $customerData->getFax(),
            'telephone' => $customerData->getTelephone(),
            'vat_id' => $customerData->getVatId(),
            'default_billing' => $customerData->isDefaultBilling() ?? 0,
            'default_shipping' => $customerData->isDefaultShipping() ?? 0,
            'region_id' => $customerData->getRegion()->getRegionId(),
            'extension_attributes' => [
                $customerData->getExtensionAttributes()
                ],
            'region' => [
                'region' => $customerData->getRegion()->getRegion(),
                'region_id' => $customerData->getRegion()->getRegionId(),
                'region_code' => $customerData->getRegion()->getRegionCode()
            ],
        ];
    }
}
