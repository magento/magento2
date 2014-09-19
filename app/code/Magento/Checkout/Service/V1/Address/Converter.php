<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Checkout\Service\V1\Address;

use Magento\Checkout\Service\V1\Data\Cart\Address;
use Magento\Checkout\Service\V1\Data\Cart\AddressBuilder;
use Magento\Checkout\Service\V1\Data\Cart\Address\Region;
use Magento\Customer\Service\V1\CustomerMetadataServiceInterface;
use Magento\Framework\Service\Data\AttributeValue;
use Magento\Framework\Service\SimpleDataObjectConverter;

class Converter
{
    /**
     * @var AddressBuilder
     */
    protected $addressBuilder;

    /**
     * @var CustomerMetadataServiceInterface
     */
    protected $metadataService;

    /**
     * @param AddressBuilder $addressBuilder
     * @param CustomerMetadataServiceInterface $metadataService
     */
    public function __construct(AddressBuilder $addressBuilder, CustomerMetadataServiceInterface $metadataService)
    {
        $this->addressBuilder = $addressBuilder;
        $this->metadataService = $metadataService;
    }

    /**
     * @param \Magento\Sales\Model\Quote\Address $address
     * @return \Magento\Checkout\Service\V1\Data\Cart\Address
     */
    public function convertModelToDataObject(\Magento\Sales\Model\Quote\Address $address)
    {
        $data = [
            Address::KEY_COUNTRY_ID => $address->getCountryId(),
            Address::KEY_ID => $address->getId(),
            Address::KEY_CUSTOMER_ID => $address->getCustomerId(),
            Address::KEY_REGION => array(
                Region::REGION => $address->getRegion(),
                Region::REGION_ID => $address->getRegionId(),
                Region::REGION_CODE => $address->getRegionCode()
            ),
            Address::KEY_STREET => $address->getStreet(),
            Address::KEY_COMPANY => $address->getCompany(),
            Address::KEY_TELEPHONE => $address->getTelephone(),
            Address::KEY_FAX => $address->getFax(),
            Address::KEY_POSTCODE => $address->getPostcode(),
            Address::KEY_CITY => $address->getCity(),
            Address::KEY_FIRSTNAME => $address->getFirstname(),
            Address::KEY_LASTNAME => $address->getLastname(),
            Address::KEY_MIDDLENAME => $address->getMiddlename(),
            Address::KEY_PREFIX => $address->getPrefix(),
            Address::KEY_SUFFIX => $address->getSuffix(),
            Address::KEY_EMAIL => $address->getEmail(),
            Address::KEY_VAT_ID => $address->getVatId()
        ];

        foreach ($this->metadataService->getCustomAttributesMetadata() as $attributeMetadata) {
            $attributeCode = $attributeMetadata->getAttributeCode();
            $method = 'get' . SimpleDataObjectConverter::snakeCaseToUpperCamelCase($attributeCode);
            $data[Address::CUSTOM_ATTRIBUTES_KEY][] =
                [AttributeValue::ATTRIBUTE_CODE => $attributeCode, AttributeValue::VALUE => $address->$method()];
        }

        return $this->addressBuilder->populateWithArray($data)->create();
    }

    /**
     * Convert address data object to quote address model
     *
     * @param \Magento\Checkout\Service\V1\Data\Cart\Address $dataObject
     * @param \Magento\Sales\Model\Quote\Address $address
     * @return \Magento\Sales\Model\Quote\Address
     */
    public function convertDataObjectToModel($dataObject, $address)
    {
        $address->setData($dataObject->__toArray());

        //set custom attributes
        $customAttributes = $dataObject->getCustomAttributes();
        /** @var \Magento\Framework\Service\Data\AttributeValue $attributeData */
        foreach ($customAttributes as $attributeData) {
            $address->setData($attributeData->getAttributeCode(), $attributeData->getValue());
        }

        //set fields with custom logic
        $address->setStreet($dataObject->getStreet());
        $address->setRegionId($dataObject->getRegion()->getRegionId());
        $address->setRegion($dataObject->getRegion()->getRegion());

        return $address;
    }
}
