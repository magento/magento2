<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\Data\AddressDataBuilder;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\RegionDataBuilder;
use Magento\Framework\Api\AttributeDataBuilder;

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
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataProcessor;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\MetadataServiceInterface $metadataService
     * @param AttributeDataBuilder $customAttributeBuilder
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param Address\Config $addressConfig
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param AddressMetadataInterface $addressMetadataService
     * @param AddressDataBuilder $addressBuilder
     * @param RegionDataBuilder $regionBuilder
     * @param CustomerFactory $customerFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\MetadataServiceInterface $metadataService,
        AttributeDataBuilder $customAttributeBuilder,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        AddressMetadataInterface $addressMetadataService,
        AddressDataBuilder $addressBuilder,
        RegionDataBuilder $regionBuilder,
        CustomerFactory $customerFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->_customerFactory = $customerFactory;
        parent::__construct(
            $context,
            $registry,
            $metadataService,
            $customAttributeBuilder,
            $directoryData,
            $eavConfig,
            $addressConfig,
            $regionFactory,
            $countryFactory,
            $addressMetadataService,
            $addressBuilder,
            $regionBuilder,
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

        $customAttributes = $address->getCustomAttributes();
        if (!is_null($customAttributes)) {
            foreach ($customAttributes as $attribute) {
                $this->setData($attribute->getAttributeCode(), $attribute->getValue());
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataModel($defaultBillingAddressId = null, $defaultShippingAddressId = null)
    {
        if ($this->getCustomerId() || $this->getParentId()) {
            if ($this->getCustomer()->getDefaultBillingAddress()) {
                $defaultBillingAddressId = $this->getCustomer()->getDefaultBillingAddress()->getId();
            }
            if ($this->getCustomer()->getDefaultShippingAddress()) {
                $defaultShippingAddressId = $this->getCustomer()->getDefaultShippingAddress()->getId();
            }
        }
        return parent::getDataModel($defaultBillingAddressId, $defaultShippingAddressId);
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
