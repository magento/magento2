<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Data;

class Customer extends \Magento\Framework\Api\AbstractExtensibleObject implements
    \Magento\Customer\Api\Data\CustomerInterface
{
    /**
     * @return string|null
     */
    public function getDefaultBilling()
    {
        return $this->_get(self::DEFAULT_BILLING);
    }

    /**
     * Get default shipping address id
     *
     * @return string|null
     */
    public function getDefaultShipping()
    {
        return $this->_get(self::DEFAULT_SHIPPING);
    }

    /**
     * Get confirmation
     *
     * @return string|null
     */
    public function getConfirmation()
    {
        return $this->_get(self::CONFIRMATION);
    }

    /**
     * Get created at time
     *
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Get created in area
     *
     * @return string|null
     */
    public function getCreatedIn()
    {
        return $this->_get(self::CREATED_IN);
    }

    /**
     * Get date of birth
     *
     * @return string|null
     */
    public function getDob()
    {
        return $this->_get(self::DOB);
    }

    /**
     * Get email address
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->_get(self::EMAIL);
    }

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->_get(self::FIRSTNAME);
    }

    /**
     * Get gender
     *
     * @return string|null
     */
    public function getGender()
    {
        return $this->_get(self::GENDER);
    }

    /**
     * Get group id
     *
     * @return string|null
     */
    public function getGroupId()
    {
        return $this->_get(self::GROUP_ID);
    }

    /**
     * Get customer id
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->_get(self::LASTNAME);
    }

    /**
     * Get middle name
     *
     * @return string|null
     */
    public function getMiddlename()
    {
        return $this->_get(self::MIDDLENAME);
    }

    /**
     * Get prefix
     *
     * @return string|null
     */
    public function getPrefix()
    {
        return $this->_get(self::PREFIX);
    }

    /**
     * Get store id
     *
     * @return int|null
     */
    public function getStoreId()
    {
        return $this->_get(self::STORE_ID);
    }

    /**
     * Get suffix
     *
     * @return string|null
     */
    public function getSuffix()
    {
        return $this->_get(self::SUFFIX);
    }

    /**
     * Get tax Vat.
     *
     * @return string|null
     */
    public function getTaxvat()
    {
        return $this->_get(self::TAXVAT);
    }

    /**
     * Get website id
     *
     * @return int|null
     */
    public function getWebsiteId()
    {
        return $this->_get(self::WEBSITE_ID);
    }

    /**
     * Get addresses
     *
     * @return \Magento\Customer\Api\Data\AddressInterface[]|null
     */
    public function getAddresses()
    {
        return $this->_get(self::KEY_ADDRESSES);
    }
}
