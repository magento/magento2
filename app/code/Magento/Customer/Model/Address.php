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
namespace Magento\Customer\Model;

use Magento\Customer\Model\Data\Address as AddressData;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\Data\AddressDataBuilder;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Api\Data\RegionDataBuilder;

/**
 * Customer address model
 *
 * @method int getParentId() getParentId()
 * @method \Magento\Customer\Model\Address setParentId() setParentId(int $parentId)
 */
class Address extends \Magento\Customer\Model\Address\AbstractAddress
{
    /**
     * Customer entity
     *
     * @var Customer
     */
    protected $_customer;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var AddressMetadataInterface
     */
    protected $_addressMetadataService;

    /**
     * @var AddressDataBuilder
     */
    protected $_addressBuilder;

    /**
     * @var RegionDataBuilder
     */
    protected $_regionBuilder;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataProcessor;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\MetadataServiceInterface $metadataService
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param Address\Config $addressConfig
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param CustomerFactory $customerFactory
     * @param AddressMetadataInterface $addressMetadataService
     * @param AddressDataBuilder $addressBuilder
     * @param RegionDataBuilder $regionBuilder
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\MetadataServiceInterface $metadataService,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        CustomerFactory $customerFactory,
        AddressMetadataInterface $addressMetadataService,
        AddressDataBuilder $addressBuilder,
        RegionDataBuilder $regionBuilder,
        \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->_customerFactory = $customerFactory;
        $this->_addressMetadataService = $addressMetadataService;
        $this->_addressBuilder = $addressBuilder;
        $this->_regionBuilder = $regionBuilder;
        parent::__construct(
            $context,
            $registry,
            $metadataService,
            $directoryData,
            $eavConfig,
            $addressConfig,
            $regionFactory,
            $countryFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Customer\Model\Resource\Address');
    }

    /**
     * Update Model with the data from Data Interface
     *
     * @param AddressInterface $address
     * @return $this
     * @deprecated Use Api/RepositoryInterface for the operations in the Data Interfaces. Don't rely on Address Model
     */
    public function updateData(AddressInterface $address)
    {
        // Set all attributes
        $attributes = $this->dataProcessor
            ->buildOutputDataArray($address, '\Magento\Customer\Api\Data\AddressInterface');

        foreach ($attributes as $attributeCode => $attributeData) {
            if (AddressInterface::REGION === $attributeCode) {
                $this->setRegion($address->getRegion()->getRegion());
                $this->setRegionCode($address->getRegion()->getRegionCode());
                $this->setRegionId($address->getRegion()->getRegionId());
            } else {
                $this->setDataUsingMethod($attributeCode, $attributeData);
            }
        }
        // Need to explicitly set this due to discrepancy in the keys between model and data object
        $this->setIsDefaultBilling($address->isDefaultBilling());
        $this->setIsDefaultShipping($address->isDefaultShipping());

        // Need to use attribute set or future updates can cause data loss
        if (!$this->getAttributeSetId()) {
            $this->setAttributeSetId(AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS);
        }
        return $this;
    }

    /**
     * Retrieve Data Model with the Address data
     *
     * @return AddressInterface
     * @deprecated Use Api/Data/AddressInterface as a result of service operations. Don't rely on the model to provide
     * the instance of Api/Data/AddressInterface
     */
    public function getDataModel()
    {
        $addressId = $this->getId();

        $attributes = $this->_addressMetadataService->getAllAttributesMetadata();
        $addressData = array();
        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();
            if (!is_null($this->getData($code))) {
                $addressData[$code] = $this->getDataUsingMethod($code);
            }
        }

        /** @var RegionInterface $region */
        $region = $this->_regionBuilder
            ->populateWithArray(
                array(
                    RegionInterface::REGION => $this->getRegion(),
                    RegionInterface::REGION_ID => $this->getRegionId(),
                    RegionInterface::REGION_CODE => $this->getRegionCode()
                )
            )
            ->create();

        $addressData[AddressData::REGION] = $region;

        $this->_addressBuilder->populateWithArray($addressData);
        if ($addressId) {
            $this->_addressBuilder->setId($addressId);
        }

        if ($this->getCustomerId() || $this->getParentId()) {
            $customerId = $this->getCustomerId() ?: $this->getParentId();
            $this->_addressBuilder->setCustomerId($customerId);
            if ($this->getCustomer()->getDefaultBillingAddress()
                && ($this->getCustomer()->getDefaultBillingAddress()->getId() == $addressId)
            ) {
                $this->_addressBuilder->setDefaultBilling(true);
            }
            if ($this->getCustomer()->getDefaultShippingAddress()
                && ($this->getCustomer()->getDefaultShippingAddress()->getId() == $addressId)
            ) {
                $this->_addressBuilder->setDefaultShipping(true);
            }
        }

        $addressDataObject = $this->_addressBuilder->create();
        return $addressDataObject;
    }

    /**
     * Retrieve address customer identifier
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->_getData('customer_id') ? $this->_getData('customer_id') : $this->getParentId();
    }

    /**
     * Declare address customer identifier
     *
     * @param int $id
     * @return $this
     */
    public function setCustomerId($id)
    {
        $this->setParentId($id);
        $this->setData('customer_id', $id);
        return $this;
    }

    /**
     * Retrieve address customer
     *
     * @return Customer|false
     */
    public function getCustomer()
    {
        if (!$this->getCustomerId()) {
            return false;
        }
        if (empty($this->_customer)) {
            $this->_customer = $this->_createCustomer()->load($this->getCustomerId());
        }
        return $this->_customer;
    }

    /**
     * Specify address customer
     *
     * @param Customer $customer
     * @return $this
     */
    public function setCustomer(Customer $customer)
    {
        $this->_customer = $customer;
        $this->setCustomerId($customer->getId());
        return $this;
    }

    /**
     * Retrieve address entity attributes
     *
     * @return Attribute[]
     */
    public function getAttributes()
    {
        $attributes = $this->getData('attributes');
        if (is_null($attributes)) {
            $attributes = $this->_getResource()->loadAllAttributes($this)->getSortedAttributes();
            $this->setData('attributes', $attributes);
        }
        return $attributes;
    }

    /**
     * Get attributes created by default
     *
     * @return string[]
     */
    public function getDefaultAttributeCodes()
    {
        return $this->_getResource()->getDefaultAttributes();
    }

    /**
     * @return void
     */
    public function __clone()
    {
        $this->setId(null);
    }

    /**
     * Return Entity Type instance
     *
     * @return \Magento\Eav\Model\Entity\Type
     */
    public function getEntityType()
    {
        return $this->_getResource()->getEntityType();
    }

    /**
     * Return Entity Type ID
     *
     * @return int
     */
    public function getEntityTypeId()
    {
        $entityTypeId = $this->getData('entity_type_id');
        if (!$entityTypeId) {
            $entityTypeId = $this->getEntityType()->getId();
            $this->setData('entity_type_id', $entityTypeId);
        }
        return $entityTypeId;
    }

    /**
     * Return Region ID
     *
     * @return int
     */
    public function getRegionId()
    {
        return (int)$this->getData('region_id');
    }

    /**
     * Set Region ID. $regionId is automatically converted to integer
     *
     * @param int $regionId
     * @return $this
     */
    public function setRegionId($regionId)
    {
        $this->setData('region_id', (int)$regionId);
        return $this;
    }

    /**
     * @return Customer
     */
    protected function _createCustomer()
    {
        return $this->_customerFactory->create();
    }
}
