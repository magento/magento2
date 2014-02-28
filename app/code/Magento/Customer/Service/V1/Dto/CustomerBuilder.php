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
 * Class Customer. Uses array to hold data, setters return $this so they can be chained.
 *
 * @method Customer create() create()
 */
class CustomerBuilder extends \Magento\Service\Entity\AbstractDtoBuilder
{
    /**
     * @param string $confirmation
     * @return CustomerBuilder
     */
    public function setConfirmation($confirmation)
    {
        return $this->_set(Customer::CONFIRMATION, $confirmation);
    }

    /**
     * @param string $createdAt
     * @return CustomerBuilder
     */
    public function setCreatedAt($createdAt)
    {
        return $this->_set(Customer::CREATED_AT, $createdAt);
    }

    /**
     * @param string $createdIn
     * @return CustomerBuilder
     */
    public function setCreatedIn($createdIn)
    {
        return $this->_set(Customer::CREATED_IN, $createdIn);
    }

    /**
     * @param string $dob
     * @return CustomerBuilder
     */
    public function setDob($dob)
    {
        return $this->_set(Customer::DOB, $dob);
    }

    /**
     * @param string $email
     * @return CustomerBuilder
     */
    public function setEmail($email)
    {
        return $this->_set(Customer::EMAIL, $email);
    }

    /**
     * @param string $firstname
     * @return CustomerBuilder
     */
    public function setFirstname($firstname)
    {
        return $this->_set(Customer::FIRSTNAME, $firstname);
    }

    /**
     * @param string $gender
     * @return CustomerBuilder
     */
    public function setGender($gender)
    {
        return $this->_set(Customer::GENDER, $gender);
    }

    /**
     * @param string $groupId
     * @return CustomerBuilder
     */
    public function setGroupId($groupId)
    {
        return $this->_set(Customer::GROUP_ID, $groupId);
    }

    /**
     * @param int $id
     * @return CustomerBuilder
     */
    public function setCustomerId($id)
    {
        return $this->_set(Customer::ID, $id);
    }

    /**
     * @param string $lastname
     * @return CustomerBuilder
     */
    public function setLastname($lastname)
    {
        return $this->_set(Customer::LASTNAME, $lastname);
    }

    /**
     * @param string $middlename
     * @return CustomerBuilder
     */
    public function setMiddlename($middlename)
    {
        return $this->_set(Customer::MIDDLENAME, $middlename);
    }

    /**
     * @param string $prefix
     * @return CustomerBuilder
     */
    public function setPrefix($prefix)
    {
        return $this->_set(Customer::PREFIX, $prefix);
    }

    /**
     * @param int $storeId
     * @return CustomerBuilder
     */
    public function setStoreId($storeId)
    {
        return $this->_set(Customer::STORE_ID, $storeId);
    }

    /**
     * @param string $suffix
     * @return CustomerBuilder
     */
    public function setSuffix($suffix)
    {
        return $this->_set(Customer::SUFFIX, $suffix);
    }

    /**
     * @param string $taxvat
     * @return CustomerBuilder
     */
    public function setTaxvat($taxvat)
    {
        return $this->_set(Customer::TAXVAT, $taxvat);
    }

    /**
     * @param int $websiteId
     * @return CustomerBuilder
     */
    public function setWebsiteId($websiteId)
    {
        return $this->_set(Customer::WEBSITE_ID, $websiteId);
    }

    /**
     * @param string
     * @return CustomerBuilder
     */
    public function getRpToken($rpToken)
    {
        return $this->_set(self::RP_TOKEN, $rpToken);
    }

    /**
     * @param string
     * @return CustomerBuilder
     */
    public function getRpTokenCreatedAt($rpTokenCreatedAt)
    {
        return $this->_set(self::RP_TOKEN_CREATED_AT, $rpTokenCreatedAt);
    }
}
