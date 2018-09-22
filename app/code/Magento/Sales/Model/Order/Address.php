<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Customer\Model\Address\AddressModelInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Model\AbstractModel;

/**
 * Sales order address model
 *
 * @api
 * @method \Magento\Customer\Api\Data\AddressInterface getCustomerAddressData()
 * @method Address setCustomerAddressData(\Magento\Customer\Api\Data\AddressInterface $value)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @since 100.0.2
 */
class Address extends AbstractModel implements OrderAddressInterface, AddressModelInterface
{
    /**
     * Possible customer address types
     */
    const TYPE_BILLING = 'billing';

    const TYPE_SHIPPING = 'shipping';

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

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
    protected $orderFactory;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $regionFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $data = $this->implodeStreetField($data);
        $this->regionFactory = $regionFactory;
        $this->orderFactory = $orderFactory;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Sales\Model\ResourceModel\Order\Address::class);
    }

    /**
     * Set order
     *
     * @codeCoverageIgnore
     *
     * @param \Magento\Sales\Model\Order $order
     * @return $this
     */
    public function setOrder(\Magento\Sales\Model\Order $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Return 2 letter state code if available, otherwise full region name
     *
     * @return null|string
     */
    public function getRegionCode()
    {
        $regionId = (!$this->getRegionId() && is_numeric($this->getRegion())) ?
            $this->getRegion() :
            $this->getRegionId();
        $model = $this->regionFactory->create()->load($regionId);
        if ($model->getCountryId() == $this->getCountryId()) {
            return $model->getCode();
        } elseif (is_string($this->getRegion())) {
            return $this->getRegion();
        } else {
            return null;
        }
    }

    /**
     * Get full customer name
     *
     * @return string
     */
    public function getName()
    {
        $name = '';
        if ($this->getPrefix()) {
            $name .= $this->getPrefix() . ' ';
        }
        $name .= $this->getFirstname();
        if ($this->getMiddlename()) {
            $name .= ' ' . $this->getMiddlename();
        }
        $name .= ' ' . $this->getLastname();
        if ($this->getSuffix()) {
            $name .= ' ' . $this->getSuffix();
        }
        return $name;
    }

    /**
     * Combine values of street lines into a single string
     *
     * @param string[]|string $value
     * @return string
     */
    protected function implodeStreetValue($value)
    {
        if (is_array($value)) {
            $value = trim(implode(PHP_EOL, $value));
        }
        return $value;
    }

    /**
     * Enforce format of the street field
     *
     * @param array|string $key
     * @param array|string $value
     * @return \Magento\Framework\DataObject
     */
    public function setData($key, $value = null)
    {
        if (is_array($key)) {
            $key = $this->implodeStreetField($key);
        } elseif ($key == OrderAddressInterface::STREET) {
            $value = $this->implodeStreetValue($value);
        }
        return parent::setData($key, $value);
    }

    /**
     * Implode value of the street field, if it is present among other fields
     *
     * @param array $data
     * @return array
     */
    protected function implodeStreetField(array $data)
    {
        if (array_key_exists(OrderAddressInterface::STREET, $data)) {
            $data[OrderAddressInterface::STREET] = $this->implodeStreetValue($data[OrderAddressInterface::STREET]);
        }
        return $data;
    }

    /**
     * Create fields street1, street2, etc.
     *
     * To be used in controllers for views data
     *
     * @return $this
     */
    public function explodeStreetAddress()
    {
        $streetLines = $this->getStreet();
        foreach ($streetLines as $lineNumber => $lineValue) {
            $this->setData(OrderAddressInterface::STREET . ($lineNumber + 1), $lineValue);
        }
        return $this;
    }

    /**
     * Get order
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if (!$this->order) {
            $this->order = $this->orderFactory->create()->load($this->getParentId());
        }
        return $this->order;
    }

    /**
     * Retrieve street field of an address
     *
     * @return string[]
     */
    public function getStreet()
    {
        if (is_array($this->getData(OrderAddressInterface::STREET))) {
            return $this->getData(OrderAddressInterface::STREET);
        }
        return explode(PHP_EOL, $this->getData(OrderAddressInterface::STREET));
    }

    /**
     * Get street line by number
     *
     * @param int $number
     * @return string
     */
    public function getStreetLine($number)
    {
        $lines = $this->getStreet();
        return $lines[$number - 1] ?? '';
    }

    //@codeCoverageIgnoreStart

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

    /**
     * Set Parent ID
     *
     * @param int $id
     *
     * @return \Magento\Framework\DataObject|Address
     */
    public function setParentId($id)
    {
        return $this->setData(OrderAddressInterface::PARENT_ID, $id);
    }

    /**
     * Sets the country address ID for the order address.
     *
     * @param int $id
     *
     * @return \Magento\Framework\DataObject|Address
     */
    public function setCustomerAddressId($id)
    {
        return $this->setData(OrderAddressInterface::CUSTOMER_ADDRESS_ID, $id);
    }

    /**
     * Sets the region ID for the order address.
     *
     * @param int $id
     *
     * @return \Magento\Framework\DataObject|Address
     */
    public function setRegionId($id)
    {
        return $this->setData(OrderAddressInterface::REGION_ID, $id);
    }

    /**
     * Sets the customer ID for the order address.
     *
     * @param int $id
     *
     * @return \Magento\Framework\DataObject|Address
     */
    public function setStreet($street)
    {
        return $this->setData(OrderAddressInterface::STREET, $street);
    }

    /**
     * Sets the fax number for the order address.
     *
     * @param string $fax
     *
     * @return \Magento\Framework\DataObject|Address
     */
    public function setCustomerId($id)
    {
        return $this->setData(OrderAddressInterface::CUSTOMER_ID, $id);
    }

    /**
     * Sets the region for the order address.
     *
     * @param string $region
     *
     * @return \Magento\Framework\DataObject|Address
     */
    public function setFax($fax)
    {
        return $this->setData(OrderAddressInterface::FAX, $fax);
    }

    /**
     * Sets the postal code for the order address.
     *
     * @param string $postcode
     *
     * @return \Magento\Framework\DataObject|Address
     */
    public function setRegion($region)
    {
        return $this->setData(OrderAddressInterface::REGION, $region);
    }

    /**
     * Sets the last name for the order address.
     *
     * @param string $lastname
     *
     * @return \Magento\Framework\DataObject|Address
     */
    public function setPostcode($postcode)
    {
        return $this->setData(OrderAddressInterface::POSTCODE, $postcode);
    }

    /**
     * Sets the street values, if any, for the order address.
     *
     * @param string|string[] $street
     *
     * @return \Magento\Framework\DataObject|Address
     */
    public function setLastname($lastname)
    {
        return $this->setData(OrderAddressInterface::LASTNAME, $lastname);
    }

    /**
     * Sets the city for the order address.
     *
     * @param string $city
     *
     * @return \Magento\Framework\DataObject|Address
     */
    public function setCity($city)
    {
        return $this->setData(OrderAddressInterface::CITY, $city);
    }

    /**
     * Sets the email address for the order address.
     *
     * @param string $email
     *
     * @return \Magento\Framework\DataObject|Address
     */
    public function setEmail($email)
    {
        return $this->setData(OrderAddressInterface::EMAIL, $email);
    }

    /**
     * Sets the telephone number for the order address.
     *
     * @param string $telephone
     *
     * @return \Magento\Framework\DataObject|Address
     */
    public function setTelephone($telephone)
    {
        return $this->setData(OrderAddressInterface::TELEPHONE, $telephone);
    }

    /**
     * Sets the country ID for the order address.
     *
     * @param string $id
     *
     * @return \Magento\Framework\DataObject|Address
     */
    public function setCountryId($id)
    {
        return $this->setData(OrderAddressInterface::COUNTRY_ID, $id);
    }

    /**
     * Sets the first name for the order address.
     *
     * @param string $firstname
     *
     * @return \Magento\Framework\DataObject|Address
     */
    public function setFirstname($firstname)
    {
        return $this->setData(OrderAddressInterface::FIRSTNAME, $firstname);
    }

    /**
     * Sets the address type for the order address.
     *
     * @param string $addressType
     *
     * @return \Magento\Framework\DataObject|Address
     */
    public function setAddressType($addressType)
    {
        return $this->setData(OrderAddressInterface::ADDRESS_TYPE, $addressType);
    }

    /**
     * Sets the prefix for the order address.
     *
     * @param string $prefix
     *
     * @return \Magento\Framework\DataObject|Address
     */
    public function setPrefix($prefix)
    {
        return $this->setData(OrderAddressInterface::PREFIX, $prefix);
    }

    /**
     * Sets the middle name for the order address.
     *
     * @param string $middlename
     *
     * @return \Magento\Framework\DataObject|Address
     */
    public function setMiddlename($middlename)
    {
        return $this->setData(OrderAddressInterface::MIDDLENAME, $middlename);
    }

    /**
     * Sets the suffix for the order address.
     *
     * @param string $suffix
     *
     * @return \Magento\Framework\DataObject|Address
     */
    public function setSuffix($suffix)
    {
        return $this->setData(OrderAddressInterface::SUFFIX, $suffix);
    }

    /**
     * Sets the company for the order address.
     *
     * @param string $company
     *
     * @return \Magento\Framework\DataObject|Address
     */
    public function setCompany($company)
    {
        return $this->setData(OrderAddressInterface::COMPANY, $company);
    }

    /**
     * Sets the VAT ID for the order address.
     *
     * @param string $id
     *
     * @return \Magento\Framework\DataObject|Address
     */
    public function setVatId($id)
    {
        return $this->setData(OrderAddressInterface::VAT_ID, $id);
    }

    /**
     * Sets the VAT-is-valid flag value for the order address.
     *
     * @param int $vatIsValid
     *
     * @return \Magento\Framework\DataObject|Address
     */
    public function setVatIsValid($vatIsValid)
    {
        return $this->setData(OrderAddressInterface::VAT_IS_VALID, $vatIsValid);
    }

    /**
     * Sets the VAT request ID for the order address.
     *
     * @param string $id
     *
     * @return \Magento\Framework\DataObject|Address
     */
    public function setVatRequestId($id)
    {
        return $this->setData(OrderAddressInterface::VAT_REQUEST_ID, $id);
    }

    /**
     * Set region code
     *
     * @param string $regionCode
     *
     * @return \Magento\Framework\DataObject|Address
     */
    public function setRegionCode($regionCode)
    {
        return $this->setData(OrderAddressInterface::KEY_REGION_CODE, $regionCode);
    }

    /**
     * Sets the VAT request date for the order address.
     *
     * @param string $vatRequestDate
     *
     * @return \Magento\Framework\DataObject|Address
     */
    public function setVatRequestDate($vatRequestDate)
    {
        return $this->setData(OrderAddressInterface::VAT_REQUEST_DATE, $vatRequestDate);
    }

    /**
     * Sets the VAT-request-success flag value for the order address.
     *
     * @param int $vatRequestSuccess
     *
     * @return \Magento\Framework\DataObject|Address
     */
    public function setVatRequestSuccess($vatRequestSuccess)
    {
        return $this->setData(OrderAddressInterface::VAT_REQUEST_SUCCESS, $vatRequestSuccess);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Sales\Api\Data\OrderAddressExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
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
