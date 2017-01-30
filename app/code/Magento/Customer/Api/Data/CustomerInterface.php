<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Api\Data;

/**
 * Customer interface.
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
     * @api
     * @return int|null
     */
    public function getId();

    /**
     * Set customer id
     *
     * @api
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get group id
     *
     * @api
     * @return int|null
     */
    public function getGroupId();

    /**
     * Set group id
     *
     * @api
     * @param int $groupId
     * @return $this
     */
    public function setGroupId($groupId);

    /**
     * Get default billing address id
     *
     * @api
     * @return string|null
     */
    public function getDefaultBilling();

    /**
     * Set default billing address id
     *
     * @api
     * @param string $defaultBilling
     * @return $this
     */
    public function setDefaultBilling($defaultBilling);

    /**
     * Get default shipping address id
     *
     * @api
     * @return string|null
     */
    public function getDefaultShipping();

    /**
     * Set default shipping address id
     *
     * @api
     * @param string $defaultShipping
     * @return $this
     */
    public function setDefaultShipping($defaultShipping);

    /**
     * Get confirmation
     *
     * @api
     * @return string|null
     */
    public function getConfirmation();

    /**
     * Set confirmation
     *
     * @api
     * @param string $confirmation
     * @return $this
     */
    public function setConfirmation($confirmation);

    /**
     * Get created at time
     *
     * @api
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created at time
     *
     * @api
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated at time
     *
     * @api
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated at time
     *
     * @api
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get created in area
     *
     * @api
     * @return string|null
     */
    public function getCreatedIn();

    /**
     * Set created in area
     *
     * @api
     * @param string $createdIn
     * @return $this
     */
    public function setCreatedIn($createdIn);

    /**
     * Get date of birth
     *
     * @api
     * @return string|null
     */
    public function getDob();

    /**
     * Set date of birth
     *
     * @api
     * @param string $dob
     * @return $this
     */
    public function setDob($dob);

    /**
     * Get email address
     *
     * @api
     * @return string
     */
    public function getEmail();

    /**
     * Set email address
     *
     * @api
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * Get first name
     *
     * @api
     * @return string
     */
    public function getFirstname();

    /**
     * Set first name
     *
     * @api
     * @param string $firstname
     * @return $this
     */
    public function setFirstname($firstname);

    /**
     * Get last name
     *
     * @api
     * @return string
     */
    public function getLastname();

    /**
     * Set last name
     *
     * @api
     * @param string $lastname
     * @return $this
     */
    public function setLastname($lastname);

    /**
     * Get middle name
     *
     * @api
     * @return string|null
     */
    public function getMiddlename();

    /**
     * Set middle name
     *
     * @api
     * @param string $middlename
     * @return $this
     */
    public function setMiddlename($middlename);

    /**
     * Get prefix
     *
     * @api
     * @return string|null
     */
    public function getPrefix();

    /**
     * Set prefix
     *
     * @api
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix);

    /**
     * Get suffix
     *
     * @api
     * @return string|null
     */
    public function getSuffix();

    /**
     * Set suffix
     *
     * @api
     * @param string $suffix
     * @return $this
     */
    public function setSuffix($suffix);

    /**
     * Get gender
     *
     * @api
     * @return int|null
     */
    public function getGender();

    /**
     * Set gender
     *
     * @api
     * @param int $gender
     * @return $this
     */
    public function setGender($gender);

    /**
     * Get store id
     *
     * @api
     * @return int|null
     */
    public function getStoreId();

    /**
     * Set store id
     *
     * @api
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get tax Vat
     *
     * @api
     * @return string|null
     */
    public function getTaxvat();

    /**
     * Set tax Vat
     *
     * @api
     * @param string $taxvat
     * @return $this
     */
    public function setTaxvat($taxvat);

    /**
     * Get website id
     *
     * @api
     * @return int|null
     */
    public function getWebsiteId();

    /**
     * Set website id
     *
     * @api
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId);

    /**
     * Get customer addresses.
     *
     * @api
     * @return \Magento\Customer\Api\Data\AddressInterface[]|null
     */
    public function getAddresses();

    /**
     * Set customer addresses.
     *
     * @api
     * @param \Magento\Customer\Api\Data\AddressInterface[] $addresses
     * @return $this
     */
    public function setAddresses(array $addresses = null);

    /**
     * Get disable auto group change flag.
     *
     * @api
     * @return int|null
     */
    public function getDisableAutoGroupChange();

    /**
     * Set disable auto group change flag.
     *
     * @api
     * @param int $disableAutoGroupChange
     * @return $this
     */
    public function setDisableAutoGroupChange($disableAutoGroupChange);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @api
     * @return \Magento\Customer\Api\Data\CustomerExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @api
     * @param \Magento\Customer\Api\Data\CustomerExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Customer\Api\Data\CustomerExtensionInterface $extensionAttributes);
}
