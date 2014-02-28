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
namespace Magento\Customer\Service\V1\Dto;

/**
 * Class Customer
 * Uses array to hold data, setters return $this so they can be chained.
 *
 * @package Magento\Customer\Service\V1\Dto
 */
class Customer extends \Magento\Service\Entity\AbstractDto implements Eav\EntityInterface
{
    /**#@+
     * constants defined for keys of array, makes typos less likely
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
    const RP_TOKEN = 'rp_token';
    const RP_TOKEN_CREATED_AT = 'rp_token_created_at';
    /**#@-*/

    /**
     * A list of valid customer DTO attributes.
     *
     * @var string[]
     */
    protected $_validAttributes = [
        self::ID,
        self::CONFIRMATION,
        self::CREATED_AT,
        self::CREATED_IN,
        self::DOB,
        self::EMAIL,
        self::FIRSTNAME,
        self::GENDER,
        self::GROUP_ID,
        self::LASTNAME,
        self::MIDDLENAME,
        self::PREFIX,
        self::STORE_ID,
        self::SUFFIX,
        self::TAXVAT,
        self::WEBSITE_ID,
        self::DEFAULT_BILLING,
        self::DEFAULT_SHIPPING,
        self::RP_TOKEN,
        self::RP_TOKEN_CREATED_AT,
    ];

    /**
     * Retrieve array of all attributes, in the form of 'attribute code' => 'attribute value'
     *
     * @return array
     */
    public function getAttributes()
    {
        $unvalidatedData = $this->__toArray();
        $validData = [];
        foreach ($this->_validAttributes as $attributeCode) {
            if (array_key_exists($attributeCode, $unvalidatedData)) {
                $validData[$attributeCode] = $unvalidatedData[$attributeCode];
            }
        }
        return $validData;
    }

    /**
     * Get an attribute value.
     *
     * @param string $attributeCode
     * @return mixed The attribute value or null if the attribute has not been set
     */
    public function getAttribute($attributeCode)
    {
        if (in_array($attributeCode, $this->_validAttributes) && isset($this->_data[$attributeCode])) {
            return $this->_data[$attributeCode];
        } else {
            return null;
        }
    }

    /**
     * @return string
     */
    public function getDefaultBilling()
    {
        return $this->_get(self::DEFAULT_BILLING);
    }

    /**
     * @return string
     */
    public function getDefaultShipping()
    {
        return $this->_get(self::DEFAULT_SHIPPING);
    }

    /**
     * @return string
     */
    public function getConfirmation()
    {
        return $this->_get(self::CONFIRMATION);
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * @return string
     */
    public function getCreatedIn()
    {
        return $this->_get(self::CREATED_IN);
    }

    /**
     * @return string
     */
    public function getDob()
    {
        return $this->_get(self::DOB);
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->_get(self::EMAIL);
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->_get(self::FIRSTNAME);
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->_get(self::GENDER);
    }

    /**
     * @return string
     */
    public function getGroupId()
    {
        return $this->_get(self::GROUP_ID);
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->_get(self::ID);
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->_get(self::LASTNAME);
    }

    /**
     * @return string
     */
    public function getMiddlename()
    {
        return $this->_get(self::MIDDLENAME);
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->_get(self::PREFIX);
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->_get(self::STORE_ID);
    }

    /**
     * @return string
     */
    public function getSuffix()
    {
        return $this->_get(self::SUFFIX);
    }

    /**
     * @return string
     */
    public function getTaxvat()
    {
        return $this->_get(self::TAXVAT);
    }

    /**
     * @return int
     */
    public function getWebsiteId()
    {
        return (int)$this->_get(self::WEBSITE_ID);
    }

    /**
     * @return string
     */
    public function getRpToken()
    {
        return $this->_get(self::RP_TOKEN);
    }

    /**
     * @return string
     */
    public function getRpTokenCreatedAt()
    {
        return $this->_get(self::RP_TOKEN_CREATED_AT);
    }
}
