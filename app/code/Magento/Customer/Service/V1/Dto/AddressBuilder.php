<?php
/**
 * Address class acts as a DTO for the Customer Service
 *
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
namespace Magento\Customer\Service\V1\Dto;

use Magento\Customer\Service\V1\Dto\Region;

class AddressBuilder extends \Magento\Service\Entity\AbstractDtoBuilder
{
    protected $_regionBuilder;

    /**
     * @param \Magento\Customer\Service\V1\Dto\RegionBuilder $regionBuilder
     */
    public function __construct(
      \Magento\Customer\Service\V1\Dto\RegionBuilder $regionBuilder
    ) {
        parent::__construct();
        $this->_regionBuilder = $regionBuilder;
        $this->_data['region'] = $regionBuilder->create();
    }

    /**
     * @param int $id
     * @return AddressBuilder
     */
    public function setId($id)
    {
        return $this->_set('id', (string)$id);
    }

    /**
     * @param boolean $defaultShipping
     * @return AddressBuilder
     */
    public function setDefaultShipping($defaultShipping)
    {
        return $this->_set('default_shipping', (bool)$defaultShipping);
    }

    /**
     * @param boolean $defaultBilling
     * @return AddressBuilder
     */
    public function setDefaultBilling($defaultBilling)
    {
        return $this->_set('default_billing', (bool)$defaultBilling);
    }

    /**
     * @param string[] $data
     * @return AddressBuilder
     */
    public function populateWithArray(array $data)
    {
        unset($data['region_id']);
        if (isset($data['region'])) {
            $region = $data['region'];
            if (!($region instanceof Region)) {
                unset($data['region']);
            }
        }

        parent::populateWithArray($data);

        return $this;
    }

    /**
     * @param Region $region
     * @return AddressBuilder
     */
    public function setRegion(Region $region)
    {
        return $this->_set('region', $region);
    }

    /**
     * @param int $countryId
     * @return AddressBuilder
     */
    public function setCountryId($countryId)
    {
        return $this->_set('country_id', $countryId);
    }

    /**
     * @param \string[] $street
     * @return AddressBuilder
     */
    public function setStreet($street)
    {
        return $this->_set('street', $street);
    }

    /**
     * @param string $company
     * @return AddressBuilder
     */
    public function setCompany($company)
    {
        return $this->_set('company', $company);
    }

    /**
     * @param string $telephone
     * @return AddressBuilder
     */
    public function setTelephone($telephone)
    {
        return $this->_set('telephone', $telephone);
    }

    /**
     * @param string $fax
     * @return AddressBuilder
     */
    public function setFax($fax)
    {
        return $this->_set('fax', $fax);
    }

    /**
     * @param string $postcode
     * @return AddressBuilder
     */
    public function setPostcode($postcode)
    {
        return $this->_set('postcode', $postcode);
    }

    /**
     * @param string $city
     * @return AddressBuilder
     */
    public function setCity($city)
    {
        return $this->_set('city', $city);
    }

    /**
     * @param string $firstname
     * @return AddressBuilder
     */
    public function setFirstname($firstname)
    {
        return $this->_set('firstname', $firstname);
    }

    /**
     * @param string $lastname
     * @return AddressBuilder
     */
    public function setLastname($lastname)
    {
        return $this->_set('lastname', $lastname);
    }

    /**
     * @param string $middlename
     * @return AddressBuilder
     */
    public function setMiddlename($middlename)
    {
        return $this->_set('middlename', $middlename);
    }

    /**
     * @param string $prefix
     * @return AddressBuilder
     */
    public function setPrefix($prefix)
    {
        return $this->_set('prefix', $prefix);
    }

    /**
     * @param string $suffix
     * @return AddressBuilder
     */
    public function setSuffix($suffix)
    {
        return $this->_set('suffix', $suffix);
    }

    /**
     * @param string $vatId
     * @return AddressBuilder
     */
    public function setVatId($vatId)
    {
        return $this->_set('vat_id', $vatId);
    }

    /**
     * @param string $customerId
     * @return AddressBuilder
     */
    public function setCustomerId($customerId)
    {
        /** XXX: (string) Needed for tests to pass */
        return $this->_set('customer_id', (string)$customerId);
    }
}
