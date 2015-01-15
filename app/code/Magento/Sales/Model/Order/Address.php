<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\Data\AddressDataBuilder;
use Magento\Customer\Api\Data\RegionDataBuilder;
use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Framework\Api\AttributeDataBuilder;
use Magento\Sales\Api\Data\OrderAddressInterface;

/**
 * Sales order address model
 *
 * @method \Magento\Sales\Model\Resource\Order\Address _getResource()
 * @method \Magento\Sales\Model\Resource\Order\Address getResource()
 * @method Address setParentId(int $value)
 * @method Address setCustomerAddressId(int $value)
 * @method \Magento\Customer\Api\Data\AddressInterface getCustomerAddressData()
 * @method Address setCustomerAddressData(\Magento\Customer\Api\Data\AddressInterface $value)
 * @method Address setQuoteAddressId(int $value)
 * @method Address setRegionId(int $value)
 * @method Address setCustomerId(int $value)
 * @method Address setFax(string $value)
 * @method Address setRegion(string $value)
 * @method Address setPostcode(string $value)
 * @method Address setLastname(string $value)
 * @method Address setCity(string $value)
 * @method Address setEmail(string $value)
 * @method Address setTelephone(string $value)
 * @method Address setCountryId(string $value)
 * @method Address setFirstname(string $value)
 * @method Address setAddressType(string $value)
 * @method Address setPrefix(string $value)
 * @method Address setMiddlename(string $value)
 * @method Address setSuffix(string $value)
 * @method Address setCompany(string $value)
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
     * @param \Magento\Framework\Api\MetadataServiceInterface $metadataService
     * @param AttributeDataBuilder $customAttributeBuilder
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param AddressMetadataInterface $addressMetadataService
     * @param AddressDataBuilder $addressBuilder
     * @param RegionDataBuilder $regionBuilder
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
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
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
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
}
