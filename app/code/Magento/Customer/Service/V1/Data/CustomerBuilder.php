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

use Magento\Framework\Service\Data\AbstractExtensibleObject as ExtensibleObject;
use Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder;
use Magento\Framework\Service\Data\AttributeValueBuilder;
use Magento\Customer\Service\V1\CustomerMetadataServiceInterface;

/**
 * Builder for the Customer Service Data Object
 *
 * @method Customer create()
 * @method Customer mergeDataObjectWithArray(ExtensibleObject $dataObject, array $data)
 * @method Customer mergeDataObjects(ExtensibleObject $firstDataObject, ExtensibleObject $secondDataObject)
 */
class CustomerBuilder extends AbstractExtensibleObjectBuilder
{
    /**
     * @param \Magento\Framework\Service\Data\ObjectFactory $objectFactory
     * @param AttributeValueBuilder $valueBuilder
     * @param CustomerMetadataServiceInterface $metadataService
     */
    public function __construct(
        \Magento\Framework\Service\Data\ObjectFactory $objectFactory,
        AttributeValueBuilder $valueBuilder,
        CustomerMetadataServiceInterface $metadataService
    ) {
        parent::__construct($objectFactory, $valueBuilder, $metadataService);
    }

    /**
     * Sets the default billing
     *
     * @param string $defaultBilling
     * @return $this
     */
    public function setDefaultBilling($defaultBilling)
    {
        return $this->_set(Customer::DEFAULT_BILLING, $defaultBilling);
    }

    /**
     * Sets the default shipping
     *
     * @param string $defaultShipping
     * @return $this
     */
    public function setDefaultShipping($defaultShipping)
    {
        return $this->_set(Customer::DEFAULT_SHIPPING, $defaultShipping);
    }

    /**
     * Set confirmation
     *
     * @param string $confirmation
     * @return $this
     */
    public function setConfirmation($confirmation)
    {
        return $this->_set(Customer::CONFIRMATION, $confirmation);
    }

    /**
     * Set created time
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->_set(Customer::CREATED_AT, $createdAt);
    }

    /**
     * Set created area
     *
     * @param string $createdIn
     * @return $this
     */
    public function setCreatedIn($createdIn)
    {
        return $this->_set(Customer::CREATED_IN, $createdIn);
    }

    /**
     * Set date of birth
     *
     * @param string $dob
     * @return $this
     */
    public function setDob($dob)
    {
        return $this->_set(Customer::DOB, $dob);
    }

    /**
     * Set email address
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        return $this->_set(Customer::EMAIL, $email);
    }

    /**
     * Set first name
     *
     * @param string $firstname
     * @return $this
     */
    public function setFirstname($firstname)
    {
        return $this->_set(Customer::FIRSTNAME, $firstname);
    }

    /**
     * Set gender
     *
     * @param string $gender
     * @return $this
     */
    public function setGender($gender)
    {
        return $this->_set(Customer::GENDER, $gender);
    }

    /**
     * Set group id
     *
     * @param string $groupId
     * @return $this
     */
    public function setGroupId($groupId)
    {
        return $this->_set(Customer::GROUP_ID, $groupId);
    }

    /**
     * Set customer id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->_set(Customer::ID, $id);
    }

    /**
     * Set last name
     *
     * @param string $lastname
     * @return $this
     */
    public function setLastname($lastname)
    {
        return $this->_set(Customer::LASTNAME, $lastname);
    }

    /**
     * Set middle name
     *
     * @param string $middlename
     * @return $this
     */
    public function setMiddlename($middlename)
    {
        return $this->_set(Customer::MIDDLENAME, $middlename);
    }

    /**
     * Set prefix
     *
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix)
    {
        return $this->_set(Customer::PREFIX, $prefix);
    }

    /**
     * Set store id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return $this->_set(Customer::STORE_ID, $storeId);
    }

    /**
     * Set suffix
     *
     * @param string $suffix
     * @return $this
     */
    public function setSuffix($suffix)
    {
        return $this->_set(Customer::SUFFIX, $suffix);
    }

    /**
     * Set tax Vat
     *
     * @param string $taxvat
     * @return $this
     */
    public function setTaxvat($taxvat)
    {
        return $this->_set(Customer::TAXVAT, $taxvat);
    }

    /**
     * Set website id
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId)
    {
        return $this->_set(Customer::WEBSITE_ID, $websiteId);
    }
}
