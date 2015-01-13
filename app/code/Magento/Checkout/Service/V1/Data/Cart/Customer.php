<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Data\Cart;

/**
 * Customer data for quote.
 *
 * @codeCoverageIgnore
 */
class Customer extends \Magento\Framework\Api\AbstractExtensibleObject
{
    /**
     * Customer ID.
     */
    const ID = 'id';

    /**
     * Customer tax class ID.
     */
    const TAX_CLASS_ID = 'tax_class_id';

    const GROUP_ID = 'group_id';

    const EMAIL = 'email';

    const PREFIX = 'prefix';

    const FIRST_NAME = 'first_name';

    const MIDDLE_NAME = 'middle_name';

    const LAST_NAME = 'last_name';

    const SUFFIX = 'suffix';

    const DOB = 'dob';

    const NOTE = 'note';

    const NOTE_NOTIFY = 'note_notify';

    const IS_GUEST = 'is_guest';

    const TAXVAT = 'taxvat';

    const GENDER = 'gender';

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
     * Get customer tax class id
     *
     * @return int|null
     */
    public function getTaxClassId()
    {
        return $this->_get(self::TAX_CLASS_ID);
    }

    /**
     * Get customer group id
     *
     * @return int|null
     */
    public function getGroupId()
    {
        return $this->_get(self::GROUP_ID);
    }

    /**
     * Get customer email
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->_get(self::EMAIL);
    }

    /**
     * Get customer name prefix
     *
     * @return string|null
     */
    public function getPrefix()
    {
        return $this->_get(self::PREFIX);
    }

    /**
     * Get customer first name
     *
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->_get(self::FIRST_NAME);
    }

    /**
     * Get customer middle name
     *
     * @return string|null
     */
    public function getMiddleName()
    {
        return $this->_get(self::MIDDLE_NAME);
    }

    /**
     * Get customer last name
     *
     * @return string|null
     */
    public function getLastName()
    {
        return $this->_get(self::LAST_NAME);
    }

    /**
     * Get customer name suffix
     *
     * @return string|null
     */
    public function getSuffix()
    {
        return $this->_get(self::SUFFIX);
    }

    /**
     * Get customer date of birth
     *
     * @return string|null
     */
    public function getDob()
    {
        return $this->_get(self::DOB);
    }

    /**
     * Get note
     *
     * @return string|null
     */
    public function getNote()
    {
        return $this->_get(self::NOTE);
    }

    /**
     * Get notification status
     *
     * @return string|null
     */
    public function getNoteNotify()
    {
        return $this->_get(self::NOTE_NOTIFY);
    }

    /**
     * Is customer a guest?
     *
     * @return bool
     */
    public function getIsGuest()
    {
        return (bool)$this->_get(self::IS_GUEST);
    }

    /**
     * Get  taxvat value
     *
     * @return string|null
     */
    public function getTaxVat()
    {
        return $this->_get(self::TAXVAT);
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
}
