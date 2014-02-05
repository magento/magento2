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

class Address extends \Magento\Service\Entity\AbstractDto implements Eav\EntityInterface
{

    const KEY_COUNTRY_ID = 'country_id';

    /**
     * @var array
     */
    private static $_nonAttributes = ['id', 'customer_id', 'region', 'default_billing', 'default_shipping'];

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->_get('id');
    }

    /**
     * @return boolean|null
     */
    public function isDefaultShipping()
    {
        return $this->_get('default_shipping');
    }

    /**
     * @return boolean|null
     */
    public function isDefaultBilling()
    {
        return $this->_get('default_billing');
    }

    /**
     * @return string[]
     */
    public function getAttributes()
    {
        $attributes = $this->_data;
        foreach (self::$_nonAttributes as $keyName) {
            unset ($attributes[$keyName]);
        }

        /** This triggers some code in _updateAddressModel in CustomerV1 Service */
        if (!is_null($this->getRegion())) {
            $attributes['region_id'] = $this->getRegion()->getRegionId();

            $attributes['region'] = $this->getRegion()->getRegion();
        }

        return $attributes;
    }

    /**
     * @param string $attributeCode
     * @return string|null
     */
    public function getAttribute($attributeCode)
    {
        $attributes = $this->getAttributes();
        if (isset($attributes[$attributeCode])
            && !in_array($attributeCode, self::$_nonAttributes)) {
            return $attributes[$attributeCode];
        }
        return null;
    }

    /**
     * @return Region
     */
    public function getRegion()
    {
        return $this->_get('region');
    }

    /**
     * @return int|null
     */
    public function getCountryId()
    {
        return $this->_get(self::KEY_COUNTRY_ID);
    }

    /**
     * @return \string[]|null
     */
    public function getStreet()
    {
        return $this->_get('street');
    }

    /**
     * @return string|null
     */
    public function getCompany()
    {
        return $this->_get('company');
    }

    /**
     * @return string|null
     */
    public function getTelephone()
    {
        return $this->_get('telephone');
    }

    /**
     * @return string|null
     */
    public function getFax()
    {
        return $this->_get('fax');
    }

    /**
     * @return string|null
     */
    public function getPostcode()
    {
        return $this->_get('postcode');
    }

    /**
     * @return string|null
     */
    public function getCity()
    {
        return $this->_get('city');
    }

    /**
     * @return string|null
     */
    public function getFirstname()
    {
        return $this->_get('firstname');
    }

    /**
     * @return string|null
     */
    public function getLastname()
    {
        return $this->_get('lastname');
    }

    /**
     * @return string|null
     */
    public function getMiddlename()
    {
        return $this->_get('middlename');
    }

    /**
     * @return string|null
     */
    public function getPrefix()
    {
        return $this->_get('prefix');
    }

    /**
     * @return string|null
     */
    public function getSuffix()
    {
        return $this->_get('suffix');
    }

    /**
     * @return string|null
     */
    public function getVatId()
    {
        return $this->_get('vat_id');
    }

    /**
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->_get('customer_id');
    }
}
