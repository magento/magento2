<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Sales\Api\Data\OrderAddressInterface;

/**
 * Sales order address model
 *
 * @method \Magento\Sales\Model\Resource\Order\Address _getResource()
 * @method \Magento\Sales\Model\Resource\Order\Address getResource()
 * @method \Magento\Customer\Api\Data\AddressInterface getCustomerAddressData()
 * @method Address setCustomerAddressData(\Magento\Customer\Api\Data\AddressInterface $value)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Address extends AbstractAddress implements OrderAddressInterface
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_order_address';

    /**
     * @var string
     */
    protected $_eventObject = 'address';

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param AddressMetadataInterface $metadataService
     * @param AddressInterfaceFactory $addressDataFactory
     * @param RegionInterfaceFactory $regionDataFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        AddressMetadataInterface $metadataService,
        AddressInterfaceFactory $addressDataFactory,
        RegionInterfaceFactory $regionDataFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $directoryData,
            $eavConfig,
            $addressConfig,
            $regionFactory,
            $countryFactory,
            $metadataService,
            $addressDataFactory,
            $regionDataFactory,
            $dataObjectHelper,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_orderFactory = $orderFactory;
    }

    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\Resource\Order\Address');
    }

    /**
     * Set order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return $this
     */
    public function setOrder(\Magento\Sales\Model\Order $order)
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * Get order
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if (!$this->_order) {
            $this->_order = $this->_orderFactory->create()->load($this->getParentId());
        }
        return $this->_order;
    }

    /**
     * Returns address_type
     *
     * @return string
     */
    public function getAddressType()
    {
        return $this->getData(OrderAddressInterface::ADDRESS_TYPE);
    }

    /**
     * Returns city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->getData(OrderAddressInterface::CITY);
    }

    /**
     * Returns company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->getData(OrderAddressInterface::COMPANY);
    }

    /**
     * Returns country_id
     *
     * @return string
     */
    public function getCountryId()
    {
        return $this->getData(OrderAddressInterface::COUNTRY_ID);
    }

    /**
     * Returns customer_address_id
     *
     * @return int
     */
    public function getCustomerAddressId()
    {
        return $this->getData(OrderAddressInterface::CUSTOMER_ADDRESS_ID);
    }

    /**
     * Returns customer_id
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->getData(OrderAddressInterface::CUSTOMER_ID);
    }

    /**
     * Returns email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getData(OrderAddressInterface::EMAIL);
    }

    /**
     * Returns entity_id
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->getData(OrderAddressInterface::ENTITY_ID);
    }

    /**
     * Sets the ID for the order address.
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId)
    {
        return $this->setData(OrderAddressInterface::ENTITY_ID, $entityId);
    }

    /**
     * Returns fax
     *
     * @return string
     */
    public function getFax()
    {
        return $this->getData(OrderAddressInterface::FAX);
    }

    /**
     * Returns firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->getData(OrderAddressInterface::FIRSTNAME);
    }

    /**
     * Returns lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->getData(OrderAddressInterface::LASTNAME);
    }

    /**
     * Returns middlename
     *
     * @return string
     */
    public function getMiddlename()
    {
        return $this->getData(OrderAddressInterface::MIDDLENAME);
    }

    /**
     * Returns parent_id
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->getData(OrderAddressInterface::PARENT_ID);
    }

    /**
     * Returns postcode
     *
     * @return string
     */
    public function getPostcode()
    {
        return $this->getData(OrderAddressInterface::POSTCODE);
    }

    /**
     * Returns prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->getData(OrderAddressInterface::PREFIX);
    }

    /**
     * Returns quote_address_id
     *
     * @return int
     */
    public function getQuoteAddressId()
    {
        return $this->getData(OrderAddressInterface::QUOTE_ADDRESS_ID);
    }

    /**
     * Returns region
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->getData(OrderAddressInterface::REGION);
    }

    /**
     * Returns region_id
     *
     * @return int
     */
    public function getRegionId()
    {
        return $this->getData(OrderAddressInterface::REGION_ID);
    }

    /**
     * Returns street
     *
     * @return string[]
     */
    public function getStreet()
    {
        return parent::getStreet();
    }

    /**
     * Returns suffix
     *
     * @return string
     */
    public function getSuffix()
    {
        return $this->getData(OrderAddressInterface::SUFFIX);
    }

    /**
     * Returns telephone
     *
     * @return string
     */
    public function getTelephone()
    {
        return $this->getData(OrderAddressInterface::TELEPHONE);
    }

    /**
     * Returns vat_id
     *
     * @return string
     */
    public function getVatId()
    {
        return $this->getData(OrderAddressInterface::VAT_ID);
    }

    /**
     * Returns vat_is_valid
     *
     * @return int
     */
    public function getVatIsValid()
    {
        return $this->getData(OrderAddressInterface::VAT_IS_VALID);
    }

    /**
     * Returns vat_request_date
     *
     * @return string
     */
    public function getVatRequestDate()
    {
        return $this->getData(OrderAddressInterface::VAT_REQUEST_DATE);
    }

    /**
     * Returns vat_request_id
     *
     * @return string
     */
    public function getVatRequestId()
    {
        return $this->getData(OrderAddressInterface::VAT_REQUEST_ID);
    }

    /**
     * Returns vat_request_success
     *
     * @return int
     */
    public function getVatRequestSuccess()
    {
        return $this->getData(OrderAddressInterface::VAT_REQUEST_SUCCESS);
    }

    //@codeCoverageIgnoreStart
    /**
     * {@inheritdoc}
     */
    public function setParentId($id)
    {
        return $this->setData(OrderAddressInterface::PARENT_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerAddressId($id)
    {
        return $this->setData(OrderAddressInterface::CUSTOMER_ADDRESS_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setQuoteAddressId($id)
    {
        return $this->setData(OrderAddressInterface::QUOTE_ADDRESS_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegionId($id)
    {
        return $this->setData(OrderAddressInterface::REGION_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerId($id)
    {
        return $this->setData(OrderAddressInterface::CUSTOMER_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setFax($fax)
    {
        return $this->setData(OrderAddressInterface::FAX, $fax);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegion($region)
    {
        return $this->setData(OrderAddressInterface::REGION, $region);
    }

    /**
     * {@inheritdoc}
     */
    public function setPostcode($postcode)
    {
        return $this->setData(OrderAddressInterface::POSTCODE, $postcode);
    }

    /**
     * {@inheritdoc}
     */
    public function setLastname($lastname)
    {
        return $this->setData(OrderAddressInterface::LASTNAME, $lastname);
    }

    /**
     * {@inheritdoc}
     */
    public function setCity($city)
    {
        return $this->setData(OrderAddressInterface::CITY, $city);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail($email)
    {
        return $this->setData(OrderAddressInterface::EMAIL, $email);
    }

    /**
     * {@inheritdoc}
     */
    public function setTelephone($telephone)
    {
        return $this->setData(OrderAddressInterface::TELEPHONE, $telephone);
    }

    /**
     * {@inheritdoc}
     */
    public function setCountryId($id)
    {
        return $this->setData(OrderAddressInterface::COUNTRY_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setFirstname($firstname)
    {
        return $this->setData(OrderAddressInterface::FIRSTNAME, $firstname);
    }

    /**
     * {@inheritdoc}
     */
    public function setAddressType($addressType)
    {
        return $this->setData(OrderAddressInterface::ADDRESS_TYPE, $addressType);
    }

    /**
     * {@inheritdoc}
     */
    public function setPrefix($prefix)
    {
        return $this->setData(OrderAddressInterface::PREFIX, $prefix);
    }

    /**
     * {@inheritdoc}
     */
    public function setMiddlename($middlename)
    {
        return $this->setData(OrderAddressInterface::MIDDLENAME, $middlename);
    }

    /**
     * {@inheritdoc}
     */
    public function setSuffix($suffix)
    {
        return $this->setData(OrderAddressInterface::SUFFIX, $suffix);
    }

    /**
     * {@inheritdoc}
     */
    public function setCompany($company)
    {
        return $this->setData(OrderAddressInterface::COMPANY, $company);
    }

    /**
     * {@inheritdoc}
     */
    public function setVatId($id)
    {
        return $this->setData(OrderAddressInterface::VAT_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setVatIsValid($vatIsValid)
    {
        return $this->setData(OrderAddressInterface::VAT_IS_VALID, $vatIsValid);
    }

    /**
     * {@inheritdoc}
     */
    public function setVatRequestId($id)
    {
        return $this->setData(OrderAddressInterface::VAT_REQUEST_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setVatRequestDate($vatRequestDate)
    {
        return $this->setData(OrderAddressInterface::VAT_REQUEST_DATE, $vatRequestDate);
    }

    /**
     * {@inheritdoc}
     */
    public function setVatRequestSuccess($vatRequestSuccess)
    {
        return $this->setData(OrderAddressInterface::VAT_REQUEST_SUCCESS, $vatRequestSuccess);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Sales\Api\Data\OrderAddressExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Sales\Api\Data\OrderAddressExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Sales\Api\Data\OrderAddressExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
    //@codeCoverageIgnoreEnd
}
