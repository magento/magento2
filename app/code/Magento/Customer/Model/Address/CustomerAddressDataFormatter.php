<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Address;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\Address\Mapper as AddressMapper;
use Magento\Customer\Model\Address\Config as AddressConfig;

/**
 * Provides method to format customer address data.
 */
class CustomerAddressDataFormatter
{
    /**
     * @var AddressMapper
     */
    private $addressMapper;

    /**
     * @var AddressConfig
     */
    private $addressConfig;

    /**
     * @var CustomAttributesProcessor
     */
    private $customAttributesProcessor;

    /**
     * @param Mapper $addressMapper
     * @param Config $addressConfig
     * @param CustomAttributesProcessor $customAttributesProcessor
     */
    public function __construct(
        AddressMapper $addressMapper,
        AddressConfig $addressConfig,
        CustomAttributesProcessor $customAttributesProcessor
    ) {
        $this->addressMapper = $addressMapper;
        $this->addressConfig = $addressConfig;
        $this->customAttributesProcessor = $customAttributesProcessor;
    }

    /**
     * Prepare list of addressed that was selected by customer on checkout page.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param \Magento\Quote\Model\Quote $quote
     * @param array $prepareAddressList
     * @return array
     */
    public function prepareSelectedAddresses(
        \Magento\Customer\Api\Data\CustomerInterface $customer,
        \Magento\Quote\Model\Quote $quote,
        array $prepareAddressList
    ): array {
        /** @var AddressInterface $billingAddress */
        $billingAddress = $quote->getBillingAddress();
        $billingAddressId = $billingAddress->getOrigData('customer_address_id');
        $prepareAddressList = $this->prepareSelectedAddress($customer, $prepareAddressList, $billingAddressId);

        $shippingAddressId = null;
        $shippingAssignments = $quote->getExtensionAttributes()->getShippingAssignments();
        if (isset($shippingAssignments[0])) {
            $shipping = current($shippingAssignments)->getData('shipping');
            /** @var AddressInterface $shippingAddress */
            $shippingAddress = $shipping->getAddress();
            $shippingAddressId = $shippingAddress->getOrigData('customer_address_id');
        }

        $prepareAddressList = $this->prepareSelectedAddress($customer, $prepareAddressList, $shippingAddressId);

        return $prepareAddressList;
    }

    /**
     * Prepare customer address data.
     *
     * @param AddressInterface $customerAddress
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareAddress(AddressInterface $customerAddress)
    {
        $resultAddress = [
            'id' => $customerAddress->getId(),
            'customer_id' => $customerAddress->getCustomerId(),
            'company' => $customerAddress->getCompany(),
            'prefix' => $customerAddress->getPrefix(),
            'firstname' => $customerAddress->getFirstname(),
            'lastname' => $customerAddress->getLastname(),
            'middlename' => $customerAddress->getMiddlename(),
            'suffix' => $customerAddress->getSuffix(),
            'street' => $customerAddress->getStreet(),
            'city' => $customerAddress->getCity(),
            'region' => [
                'region' => $customerAddress->getRegion()->getRegion(),
                'region_code' => $customerAddress->getRegion()->getRegionCode(),
                'region_id' => $customerAddress->getRegion()->getRegionId(),
            ],
            'region_id' => $customerAddress->getRegionId(),
            'postcode' => $customerAddress->getPostcode(),
            'country_id' => $customerAddress->getCountryId(),
            'telephone' => $customerAddress->getTelephone(),
            'fax' => $customerAddress->getFax(),
            'default_billing' => $customerAddress->isDefaultBilling(),
            'default_shipping' => $customerAddress->isDefaultShipping(),
            'inline' => $this->getCustomerAddressInline($customerAddress),
            'custom_attributes' => [],
            'extension_attributes' => $customerAddress->getExtensionAttributes(),
        ];

        if ($customerAddress->getCustomAttributes()) {
            $customerAddress = $customerAddress->__toArray();
            $resultAddress['custom_attributes'] = $this->customAttributesProcessor->filterNotVisibleAttributes(
                $customerAddress['custom_attributes']
            );
        }

        return $resultAddress;
    }

    /**
     * Prepared address by for given customer with given address id.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param array $addressList
     * @param int|null $addressId
     * @return array
     */
    private function prepareSelectedAddress(
        \Magento\Customer\Api\Data\CustomerInterface $customer,
        array $addressList,
        $addressId = null
    ): array {
        if (null !== $addressId && !isset($addressList[$addressId])) {
            $selectedAddress = $this->prepareAddress($customer->getAddresses()[$addressId]);
            if (isset($selectedAddress['id'])) {
                $addressList[$selectedAddress['id']] = $selectedAddress;
            }
        }

        return $addressList;
    }

    /**
     * Set additional customer address data
     *
     * @param AddressInterface $address
     * @return string
     */
    private function getCustomerAddressInline(AddressInterface $address): string
    {
        $builtOutputAddressData = $this->addressMapper->toFlatArray($address);
        return $this->addressConfig
            ->getFormatByCode(AddressConfig::DEFAULT_ADDRESS_FORMAT)
            ->getRenderer()
            ->renderArray($builtOutputAddressData);
    }
}
