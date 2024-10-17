<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Address;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;

/**
 * Class Mapper converts Address Service Data Object to an array
 */
class Mapper
{
    /**
     * @var ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    /**
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(ExtensibleDataObjectConverter $extensibleDataObjectConverter)
    {
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * Convert address data object to a flat array
     *
     * @param AddressInterface $addressDataObject
     * @return array
     */
    public function toFlatArray(AddressInterface $addressDataObject): array
    {
        $flatAddressArray = $this->extensibleDataObjectConverter->toFlatArray(
            $addressDataObject,
            [],
            AddressInterface::class
        );
        //preserve street
        $street = $addressDataObject->getStreet();
        if (!empty($street) && is_array($street)) {
            // Unset flat street data
            $streetKeys = array_keys($street);
            foreach ($streetKeys as $key) {
                unset($flatAddressArray[$key]);
            }
            //Restore street as an array
            $flatAddressArray[AddressInterface::STREET] = $street;
        }
        return $flatAddressArray;
    }
}
