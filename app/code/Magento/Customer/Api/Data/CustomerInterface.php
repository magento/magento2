<?php
/**
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

namespace Magento\Customer\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Customer interface.
 */
interface CustomerInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const CONFIRMATION = 'confirmation';
    const CREATED_AT = 'created_at';
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
    /**#@-*/

    /**
     * Get customer id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get group id
     *
     * @return int|null
     */
    public function getGroupId();

    /**
     * Get default billing address id
     *
     * @return int|null
     */
    public function getDefaultBilling();

    /**
     * Get default shipping address id
     *
     * @return int|null
     */
    public function getDefaultShipping();

    /**
     * Get confirmation
     *
     * @return string|null
     */
    public function getConfirmation();

    /**
     * Get created at time
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Get created in area
     *
     * @return string|null
     */
    public function getCreatedIn();

    /**
     * Get date of birth
     *
     * @return string|null
     */
    public function getDob();

    /**
     * Get email address
     *
     * @return string
     */
    public function getEmail();

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstname();

    /**
     * Get gender
     *
     * @return string|null
     */
    public function getGender();

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastname();

    /**
     * Get middle name
     *
     * @return string|null
     */
    public function getMiddlename();

    /**
     * Get prefix
     *
     * @return string|null
     */
    public function getPrefix();

    /**
     * Get store id
     *
     * @return int|null
     */
    public function getStoreId();

    /**
     * Get suffix
     *
     * @return string|null
     */
    public function getSuffix();

    /**
     * Get tax Vat
     *
     * @return string|null
     */
    public function getTaxvat();

    /**
     * Get website id
     *
     * @return int|null
     */
    public function getWebsiteId();

    /**
     * Get customer addresses.
     *
     * @return \Magento\Customer\Api\Data\AddressInterface[]
     */
    public function getAddresses();
}
