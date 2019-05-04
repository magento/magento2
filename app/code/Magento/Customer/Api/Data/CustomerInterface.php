<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Api\Data;

/**
 * Customer interface.
 * @api
 * @since 100.0.2
 */
interface CustomerInterface extends \Magento\Framework\Api\CustomAttributesDataInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const CONFIRMATION = 'confirmation';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const CREATED_IN = 'created_in';
    const DOB = 'dob';
    const EMAIL = 'email';
    const FIRSTNAME = 'firstname';
    const GENDER = 'gender';
    const GROUP_ID = 'group_id';
    const LASTNAME = 'lastname';
    const MIDDLENAME = 'middlename';
    const PREFIX = 'prefix';
    const STORE_ID = 'store_id';
    const SUFFIX = 'suffix';
    const TAXVAT = 'taxvat';
    const WEBSITE_ID = 'website_id';
    const DEFAULT_BILLING = 'default_billing';
    const DEFAULT_SHIPPING = 'default_shipping';
    const KEY_ADDRESSES = 'addresses';
    const DISABLE_AUTO_GROUP_CHANGE = 'disable_auto_group_change';
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
     * Get group id
     *
     * @return int|null
     */
    public function getGroupId();

    /**
     * Set group id
     *
     * @param int $groupId
     * @return $this
     */
    public function setGroupId($groupId);

    /**
     * Get default billing address id
     *
     * @return string|null
     */
    public function getDefaultBilling();

    /**
     * Set default billing address id
     *
     * @param string $defaultBilling
     * @return $this
     */
    public function setDefaultBilling($defaultBilling);

    /**
     * Get default shipping address id
     *
     * @return string|null
     */
    public function getDefaultShipping();

    /**
     * Set default shipping address id
     *
     * @param string $defaultShipping
     * @return $this
     */
    public function setDefaultShipping($defaultShipping);

    /**
     * Get confirmation
     *
     * @return string|null
     */
    public function getConfirmation();

    /**
     * Set confirmation
     *
     * @param string $confirmation
     * @return $this
     */
    public function setConfirmation($confirmation);

    /**
     * Get created at time
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created at time
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated at time
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated at time
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get created in area
     *
     * @return string|null
     */
    public function getCreatedIn();

    /**
     * Set created in area
     *
     * @param string $createdIn
     * @return $this
     */
    public function setCreatedIn($createdIn);

    /**
     * Get date of birth
     *
     * @return string|null
     */
    public function getDob();

    /**
     * Set date of birth
     *
     * @param string $dob
     * @return $this
     */
    public function setDob($dob);

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
     * Get middle name
     *
     * @return string|null
     */
    public function getMiddlename();

    /**
     * Set middle name
     *
     * @param string $middlename
     * @return $this
     */
    public function setMiddlename($middlename);

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
     * Get suffix
     *
     * @return string|null
     */
    public function getSuffix();

    /**
     * Set suffix
     *
     * @param string $suffix
     * @return $this
     */
    public function setSuffix($suffix);

    /**
     * Get gender
     *
     * @return int|null
     */
    public function getGender();

    /**
     * Set gender
     *
     * @param int $gender
     * @return $this
     */
    public function setGender($gender);

    /**
     * Get store id
     *
     * @return int|null
     */
    public function getStoreId();

    /**
     * Set store id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get tax Vat
     *
     * @return string|null
     */
    public function getTaxvat();

    /**
     * Set tax Vat
     *
     * @param string $taxvat
     * @return $this
     */
    public function setTaxvat($taxvat);

    /**
     * Get website id
     *
     * @return int|null
     */
    public function getWebsiteId();

    /**
     * Set website id
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId);

    /**
     * Get customer addresses.
     *
     * @return \Magento\Customer\Api\Data\AddressInterface[]|null
     */
    public function getAddresses();

    /**
     * Set customer addresses.
     *
     * @param \Magento\Customer\Api\Data\AddressInterface[] $addresses
     * @return $this
     */
    public function setAddresses(array $addresses = null);

    /**
     * Get disable auto group change flag.
     *
     * @return int|null
     */
    public function getDisableAutoGroupChange();

    /**
     * Set disable auto group change flag.
     *
     * @param int $disableAutoGroupChange
     * @return $this
     */
    public function setDisableAutoGroupChange($disableAutoGroupChange);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Customer\Api\Data\CustomerExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Customer\Api\Data\CustomerExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Customer\Api\Data\CustomerExtensionInterface $extensionAttributes);
}
