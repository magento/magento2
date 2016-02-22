<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wonderland\Api\Data;

/**
 * Customer interface.
 */
interface FakeCustomerInterface extends \Magento\Framework\Api\CustomAttributesDataInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const EMAIL = 'email';
    const FIRSTNAME = 'firstname';
    const LASTNAME = 'lastname';
    const PREFIX = 'prefix';
    /**#@-*/

    /**
     * Get customer id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set customer id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get email address
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set email address
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstname();

    /**
     * Set first name
     *
     * @param string $firstname
     * @return $this
     */
    public function setFirstname($firstname);

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastname();

    /**
     * Set last name
     *
     * @param string $lastname
     * @return $this
     */
    public function setLastname($lastname);

    /**
     * Get prefix
     *
     * @return string|null
     */
    public function getPrefix();

    /**
     * Set prefix
     *
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Wonderland\Api\Data\FakeCustomerExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Wonderland\Api\Data\FakeCustomerExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Wonderland\Api\Data\FakeCustomerExtensionInterface $extensionAttributes
    );
}
