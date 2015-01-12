<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Data\Cart;

use Magento\Checkout\Service\V1\Data\Cart\Address\Region;
use Magento\Checkout\Service\V1\Data\Cart\Address\RegionBuilder;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\Api\ExtensibleObjectBuilder;
use Magento\Framework\Api\AttributeDataBuilder;

/**
 * Quote address data object builder
 *
 * @codeCoverageIgnore
 */
class AddressBuilder extends ExtensibleObjectBuilder
{
    /**
     * Region builder
     *
     * @var \Magento\Checkout\Service\V1\Data\Cart\Address\RegionBuilder
     */
    protected $_regionBuilder;

    /**
     * @param \Magento\Framework\Api\ObjectFactory $objectFactory
     * @param AttributeDataBuilder $valueBuilder
     * @param CustomerMetadataInterface $metadataService
     * @param RegionBuilder $regionBuilder
     */
    public function __construct(
        \Magento\Framework\Api\ObjectFactory $objectFactory,
        AttributeDataBuilder $valueBuilder,
        CustomerMetadataInterface $metadataService,
        RegionBuilder $regionBuilder
    ) {
        parent::__construct($objectFactory, $valueBuilder, $metadataService);
        $this->_regionBuilder = $regionBuilder;
        $this->data[Address::KEY_REGION] = $regionBuilder->create();
    }

    /**
     * Convenience method to return region builder
     *
     * @return RegionBuilder
     */
    public function getRegionBuilder()
    {
        return $this->_regionBuilder;
    }

    /**
     * Set id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->_set(Address::KEY_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    protected function _setDataValues(array $data)
    {
        if (array_key_exists(Address::KEY_REGION, $data)) {
            if (!is_array($data[Address::KEY_REGION])) {
                // Region data has been submitted as individual keys of Address object. Let's extract it.
                $regionData = [];
                foreach ([Region::REGION, Region::REGION_CODE, Region::REGION_ID] as $attrCode) {
                    if (isset($data[$attrCode])) {
                        $regionData[$attrCode] = $data[$attrCode];
                    }
                }
            } else {
                $regionData = $data[Address::KEY_REGION];
            }
            $data[Address::KEY_REGION] = $this->_regionBuilder->populateWithArray($regionData)->create();
        }
        return parent::_setDataValues($data);
    }

    /**
     * Set region
     *
     * @param \Magento\Checkout\Service\V1\Data\Cart\Address\Region $region
     * @return $this
     */
    public function setRegion(\Magento\Checkout\Service\V1\Data\Cart\Address\Region $region)
    {
        return $this->_set(Address::KEY_REGION, $region);
    }

    /**
     * Set country id
     *
     * @param int $countryId
     * @return $this
     */
    public function setCountryId($countryId)
    {
        return $this->_set(Address::KEY_COUNTRY_ID, $countryId);
    }

    /**
     * Set street
     *
     * @param string[] $street
     * @return $this
     */
    public function setStreet($street)
    {
        return $this->_set(Address::KEY_STREET, $street);
    }

    /**
     * Set company
     *
     * @param string $company
     * @return $this
     */
    public function setCompany($company)
    {
        return $this->_set(Address::KEY_COMPANY, $company);
    }

    /**
     * Set telephone number
     *
     * @param string $telephone
     * @return $this
     */
    public function setTelephone($telephone)
    {
        return $this->_set(Address::KEY_TELEPHONE, $telephone);
    }

    /**
     * Set fax number
     *
     * @param string $fax
     * @return $this
     */
    public function setFax($fax)
    {
        return $this->_set(Address::KEY_FAX, $fax);
    }

    /**
     * Set postcode
     *
     * @param string $postcode
     * @return $this
     */
    public function setPostcode($postcode)
    {
        return $this->_set(Address::KEY_POSTCODE, $postcode);
    }

    /**
     * Set city name
     *
     * @param string $city
     * @return $this
     */
    public function setCity($city)
    {
        return $this->_set(Address::KEY_CITY, $city);
    }

    /**
     * Set first name
     *
     * @param string $firstname
     * @return $this
     */
    public function setFirstname($firstname)
    {
        return $this->_set(Address::KEY_FIRSTNAME, $firstname);
    }

    /**
     * Set last name
     *
     * @param string $lastname
     * @return $this
     */
    public function setLastname($lastname)
    {
        return $this->_set(Address::KEY_LASTNAME, $lastname);
    }

    /**
     * Set middle name
     *
     * @param string $middlename
     * @return $this
     */
    public function setMiddlename($middlename)
    {
        return $this->_set(Address::KEY_MIDDLENAME, $middlename);
    }

    /**
     * Set prefix
     *
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix)
    {
        return $this->_set(Address::KEY_PREFIX, $prefix);
    }

    /**
     * Set suffix
     *
     * @param string $suffix
     * @return $this
     */
    public function setSuffix($suffix)
    {
        return $this->_set(Address::KEY_SUFFIX, $suffix);
    }

    /**
     * Set Vat id
     *
     * @param string $vatId
     * @return $this
     */
    public function setVatId($vatId)
    {
        return $this->_set(Address::KEY_VAT_ID, $vatId);
    }

    /**
     * Set customer id
     *
     * @param string $customerId
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        return $this->_set(Address::KEY_CUSTOMER_ID, $customerId);
    }

    /**
     * @param $value string
     * @return $this
     */
    public function setEmail($value)
    {
        return $this->_set(Address::KEY_EMAIL, $value);
    }
}
