<?php
/**
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
namespace Magento\Customer\Service\V1\Data;

/**
 * Class Customer
 */
class Customer extends \Magento\Framework\Service\Data\AbstractExtensibleObject
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
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

    /**#@-*/

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
        return (int)$this->_get(self::WEBSITE_ID);
    }
}
