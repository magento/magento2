<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Data\Cart;

/**
 * Customer data builder for quote
 *
 * @codeCoverageIgnore
 */
class CustomerBuilder extends \Magento\Framework\Api\ExtensibleObjectBuilder
{
    /**
     * Set customer id
     *
     * @param int|null $value
     * @return $this
     */
    public function setId($value)
    {
        return $this->_set(Customer::ID, $value);
    }

    /**
     * Set customer tax class id
     *
     * @param int|null $value
     * @return $this
     */
    public function setTaxClassId($value)
    {
        return $this->_set(Customer::TAX_CLASS_ID, $value);
    }

    /**
     * Set customer group id
     *
     * @param int|null $value
     * @return $this
     */
    public function setGroupId($value)
    {
        return $this->_set(Customer::GROUP_ID, $value);
    }

    /**
     * Set customer email
     *
     * @param string|null $value
     * @return $this
     */
    public function setEmail($value)
    {
        return $this->_set(Customer::EMAIL, $value);
    }

    /**
     * Set customer name prefix
     *
     * @param string|null $value
     * @return $this
     */
    public function setPrefix($value)
    {
        return $this->_set(Customer::PREFIX, $value);
    }

    /**
     * Set customer first name
     *
     * @param string|null $value
     * @return $this
     */
    public function setFirstName($value)
    {
        return $this->_set(Customer::FIRST_NAME, $value);
    }

    /**
     * Set customer middle name
     *
     * @param string|null $value
     * @return $this
     */
    public function setMiddleName($value)
    {
        return $this->_set(Customer::MIDDLE_NAME, $value);
    }

    /**
     * Set customer last name
     *
     * @param string|null $value
     * @return $this
     */
    public function setLastName($value)
    {
        return $this->_set(Customer::LAST_NAME, $value);
    }

    /**
     * Set customer name suffix
     *
     * @param string|null $value
     * @return $this
     */
    public function setSuffix($value)
    {
        return $this->_set(Customer::SUFFIX, $value);
    }

    /**
     * Set customer date of birth
     *
     * @param string|null $value
     * @return $this
     */
    public function setDob($value)
    {
        return $this->_set(Customer::DOB, $value);
    }

    /**
     * Set note
     *
     * @param string|null $value
     * @return $this
     */
    public function setNote($value)
    {
        return $this->_set(Customer::NOTE, $value);
    }

    /**
     * Set notification status
     *
     * @param string|null $value
     * @return $this
     */
    public function setNoteNotify($value)
    {
        return $this->_set(Customer::NOTE_NOTIFY, $value);
    }

    /**
     * Is customer a guest?
     *
     * @param bool $value
     * @return $this
     */
    public function setIsGuest($value)
    {
        return (bool)$this->_set(Customer::IS_GUEST, $value);
    }

    /**
     * Get  taxvat value
     *
     * @param string $value
     * @return $this
     */
    public function setTaxVat($value)
    {
        return $this->_set(Customer::TAXVAT, $value);
    }

    /**
     * Get gender
     *
     * @param string $value
     * @return $this
     */
    public function setGender($value)
    {
        return $this->_set(Customer::GENDER, $value);
    }
}
