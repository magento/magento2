<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Address;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Model\Data\Address as AddressData;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Address abstract model
 *
 * @method string getPrefix()
 * @method string getSuffix()
 * @method string getFirstname()
 * @method string getMiddlename()
 * @method string getLastname()
 * @method int getCountryId()
 * @method string getCity()
 * @method string getTelephone()
 * @method string getCompany()
 * @method string getFax()
 * @method string getPostcode()
 * @method bool getShouldIgnoreValidation()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @api
 * @since 2.0.0
 */
class AbstractAddress extends AbstractExtensibleModel implements AddressModelInterface
{
    /**
     * Possible customer address types
     */
    const TYPE_BILLING = 'billing';

    const TYPE_SHIPPING = 'shipping';

    /**
     * Prefix of model events
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventPrefix = 'customer_address';

    /**
     * Name of event object
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventObject = 'customer_address';

    /**
     * Directory country models
     *
     * @var \Magento\Directory\Model\Country[]
     * @since 2.0.0
     */
    protected static $_countryModels = [];

    /**
     * Directory region models
     *
     * @var \Magento\Directory\Model\Region[]
     * @since 2.0.0
     */
    protected static $_regionModels = [];

    /**
     * Directory data
     *
     * @var \Magento\Directory\Helper\Data
     * @since 2.0.0
     */
    protected $_directoryData = null;

    /**
     * @var \Magento\Eav\Model\Config
     * @since 2.0.0
     */
    protected $_eavConfig;

    /**
     * @var Config
     * @since 2.0.0
     */
    protected $_addressConfig;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     * @since 2.0.0
     */
    protected $_regionFactory;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     * @since 2.0.0
     */
    protected $_countryFactory;

    /**
     * @var AddressMetadataInterface
     * @since 2.0.0
     */
    protected $metadataService;

    /**
     * @var AddressInterfaceFactory
     * @since 2.0.0
     */
    protected $addressDataFactory;

    /**
     * @var RegionInterfaceFactory
     * @since 2.0.0
     */
    protected $regionDataFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     * @since 2.0.0
     */
    protected $dataObjectHelper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param Config $addressConfig
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param AddressMetadataInterface $metadataService
     * @param AddressInterfaceFactory $addressDataFactory
     * @param RegionInterfaceFactory $regionDataFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\Eav\Model\Config $eavConfig,
        Config $addressConfig,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        AddressMetadataInterface $metadataService,
        AddressInterfaceFactory $addressDataFactory,
        RegionInterfaceFactory $regionDataFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_directoryData = $directoryData;
        $data = $this->_implodeArrayField($data);
        $this->_eavConfig = $eavConfig;
        $this->_addressConfig = $addressConfig;
        $this->_regionFactory = $regionFactory;
        $this->_countryFactory = $countryFactory;
        $this->metadataService = $metadataService;
        $this->addressDataFactory = $addressDataFactory;
        $this->regionDataFactory = $regionDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
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
     * Get full customer name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName()
    {
        $name = '';
        if ($this->_eavConfig->getAttribute('customer_address', 'prefix')->getIsVisible() && $this->getPrefix()) {
            $name .= $this->getPrefix() . ' ';
        }
        $name .= $this->getFirstname();
        $middleName = $this->_eavConfig->getAttribute('customer_address', 'middlename');
        if ($middleName->getIsVisible() && $this->getMiddlename()) {
            $name .= ' ' . $this->getMiddlename();
        }
        $name .= ' ' . $this->getLastname();
        if ($this->_eavConfig->getAttribute('customer_address', 'suffix')->getIsVisible() && $this->getSuffix()) {
            $name .= ' ' . $this->getSuffix();
        }
        return $name;
    }

    /**
     * Retrieve street field of an address
     *
     * @return string[]
     * @since 2.0.0
     */
    public function getStreet()
    {
        if (is_array($this->getStreetFull())) {
            return $this->getStreetFull();
        }
        return explode("\n", $this->getStreetFull());
    }

    /**
     * Get steet line by number
     *
     * @param int $number
     * @return string
     * @since 2.0.0
     */
    public function getStreetLine($number)
    {
        $lines = $this->getStreet();
        return isset($lines[$number - 1]) ? $lines[$number - 1] : '';
    }

    /**
     * Retrieve text of street lines, concatenated using LF symbol
     *
     * @return string
     * @since 2.0.0
     */
    public function getStreetFull()
    {
        $street = $this->getData('street');
        return is_array($street) ? implode("\n", $street) : $street;
    }

    /**
     * Alias for a street setter. To be used though setDataUsingMethod('street_full', $value).
     *
     * @param string|string[] $street
     * @return $this
     * @since 2.0.0
     */
    public function setStreetFull($street)
    {
        return $this->setStreet($street);
    }

    /**
     * Non-magic setter for a street field
     *
     * @param string|string[] $street
     * @return $this
     * @since 2.0.0
     */
    public function setStreet($street)
    {
        $this->setData('street', $street);
        return $this;
    }

    /**
     * Enforce format of the street field or other multiline custom attributes
     *
     * @param array|string $key
     * @param null $value
     * @return \Magento\Framework\DataObject
     * @since 2.0.0
     */
    public function setData($key, $value = null)
    {
        if (is_array($key)) {
            $key = $this->_implodeArrayField($key);
        } elseif (is_array($value) && !empty($value) && $this->isAddressMultilineAttribute($key)) {
            $value = $this->_implodeArrayValues($value);
        }
        return parent::setData($key, $value);
    }

    /**
     * Check that address can have multiline attribute by this code (as street or some custom attribute)
     * @param string $code
     * @return bool
     * @since 2.0.0
     */
    protected function isAddressMultilineAttribute($code)
    {
        return $code == 'street' || in_array($code, $this->getCustomAttributesCodes());
    }

    /**
     * Implode value of the array field, if it is present among other fields
     *
     * @param array $data
     * @return array
     * @since 2.0.0
     */
    protected function _implodeArrayField(array $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value) && $this->isAddressMultilineAttribute($key)) {
                $data[$key] = $this->_implodeArrayValues($data[$key]);
            }
        }
        return $data;
    }

    /**
     * Combine values of field lines into a single string
     *
     * @param string[]|string $value
     * @return string
     * @since 2.0.0
     */
    protected function _implodeArrayValues($value)
    {
        if (is_array($value) && count($value)) {
            $isScalar = false;
            foreach ($value as $val) {
                if (is_scalar($val)) {
                    $isScalar = true;
                }
            }
            if ($isScalar) {
                $value = trim(implode("\n", $value));
            }
        }
        return $value;
    }

    /**
     * Create fields street1, street2, etc.
     *
     * To be used in controllers for views data
     *
     * @return $this
     * @since 2.0.0
     */
    public function explodeStreetAddress()
    {
        $streetLines = $this->getStreet();
        foreach ($streetLines as $i => $line) {
            $this->setData('street' . ($i + 1), $line);
        }
        return $this;
    }

    /**
     * Retrieve region name
     *
     * @return string
     * @since 2.0.0
     */
    public function getRegion()
    {
        $regionId = $this->getData('region_id');
        $region = $this->getData('region');

        if (!$regionId && is_numeric($region)) {
            if ($this->getRegionModel($region)->getCountryId() == $this->getCountryId()) {
                $this->setData('region', $this->getRegionModel($region)->getName());
                $this->setData('region_id', $region);
            }
        } elseif ($regionId) {
            if ($this->getRegionModel($regionId)->getCountryId() == $this->getCountryId()) {
                $this->setData('region', $this->getRegionModel($regionId)->getName());
            }
        } elseif (is_string($region)) {
            $this->setData('region', $region);
        }

        return $this->getData('region');
    }

    /**
     * Return 2 letter state code if available, otherwise full region name
     *
     * @return string
     * @since 2.0.0
     */
    public function getRegionCode()
    {
        $regionId = $this->getData('region_id');
        $region = $this->getData('region');

        if (!$regionId && is_numeric($region)) {
            if ($this->getRegionModel($region)->getCountryId() == $this->getCountryId()) {
                $this->setData('region_code', $this->getRegionModel($region)->getCode());
            }
        } elseif ($regionId) {
            if ($this->getRegionModel($regionId)->getCountryId() == $this->getCountryId()) {
                $this->setData('region_code', $this->getRegionModel($regionId)->getCode());
            }
        } elseif (is_string($region)) {
            $this->setData('region_code', $region);
        }
        return $this->getData('region_code');
    }

    /**
     * @return int
     * @since 2.0.0
     */
    public function getRegionId()
    {
        $regionId = $this->getData('region_id');
        $region = $this->getData('region');
        if (!$regionId) {
            if (is_numeric($region)) {
                $this->setData('region_id', $region);
                $this->unsRegion();
            } else {
                $regionModel = $this->_createRegionInstance()->loadByCode(
                    $this->getRegionCode(),
                    $this->getCountryId()
                );
                $this->setData('region_id', $regionModel->getId());
            }
        }
        return $this->getData('region_id');
    }

    /**
     * @return int
     * @since 2.0.0
     */
    public function getCountry()
    {
        $country = $this->getCountryId();
        return $country ? $country : $this->getData('country');
    }

    /**
     * Retrieve country model
     *
     * @return \Magento\Directory\Model\Country
     * @since 2.0.0
     */
    public function getCountryModel()
    {
        if (!isset(self::$_countryModels[$this->getCountryId()])) {
            $country = $this->_createCountryInstance();
            $country->load($this->getCountryId());
            self::$_countryModels[$this->getCountryId()] = $country;
        }

        return self::$_countryModels[$this->getCountryId()];
    }

    /**
     * Retrieve country model
     *
     * @param int|null $regionId
     * @return \Magento\Directory\Model\Region
     * @since 2.0.0
     */
    public function getRegionModel($regionId = null)
    {
        if ($regionId === null) {
            $regionId = $this->getRegionId();
        }

        if (!isset(self::$_regionModels[$regionId])) {
            $region = $this->_createRegionInstance();
            $region->load($regionId);
            self::$_regionModels[$regionId] = $region;
        }

        return self::$_regionModels[$regionId];
    }

    /**
     * Format address in a specific way
     *
     * Deprecated, use this code instead:
     * $renderer = $this->_addressConfig->getFormatByCode('html')->getRenderer();
     * $addressMapper = \Magento\Customer\Model\Address\Mapper type
     * $addressData = $addressMapper->toFlatArray($address);
     * $formattedAddress = $renderer->renderArray($addressData);
     *
     * @param string $type
     * @return string|null
     * @since 2.0.0
     */
    public function format($type)
    {
        if (!($formatType = $this->getConfig()->getFormatByCode($type)) || !$formatType->getRenderer()) {
            return null;
        }
        $this->_eventManager->dispatch('customer_address_format', ['type' => $formatType, 'address' => $this]);
        return $formatType->getRenderer()->render($this);
    }

    /**
     * Retrieve address config object
     *
     * @return Config
     * @since 2.0.0
     */
    public function getConfig()
    {
        return $this->_addressConfig;
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    public function beforeSave()
    {
        parent::beforeSave();
        $this->getRegion();
        return $this;
    }

    /**
     * Create address data object based on current address model.
     *
     * @param int|null $defaultBillingAddressId
     * @param int|null $defaultShippingAddressId
     * @return AddressInterface
     * Use Api/Data/AddressInterface as a result of service operations. Don't rely on the model to provide
     * the instance of Api/Data/AddressInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    public function getDataModel($defaultBillingAddressId = null, $defaultShippingAddressId = null)
    {
        $addressId = $this->getId();

        $attributes = $this->metadataService->getAllAttributesMetadata();
        $addressData = [];
        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();
            if ($this->getData($code) !== null) {
                if ($code === AddressInterface::STREET) {
                    $addressData[$code] = $this->getDataUsingMethod($code);
                } else {
                    $addressData[$code] = $this->getData($code);
                }
            }
        }

        /** @var RegionInterface $region */
        $region = $this->regionDataFactory->create();
        $region->setRegion($this->getRegion())
            ->setRegionCode($this->getRegionCode())
            ->setRegionId($this->getRegionId());

        $addressData[AddressData::REGION] = $region;

        $addressDataObject = $this->addressDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $addressDataObject,
            $addressData,
            \Magento\Customer\Api\Data\AddressInterface::class
        );
        if ($addressId) {
            $addressDataObject->setId($addressId);
        }

        if ($this->getCustomerId() || $this->getParentId()) {
            $customerId = $this->getCustomerId() ?: $this->getParentId();
            $addressDataObject->setCustomerId($customerId);
            if ($defaultBillingAddressId == $addressId) {
                $addressDataObject->setIsDefaultBilling(true);
            }
            if ($defaultShippingAddressId == $addressId) {
                $addressDataObject->setIsDefaultShipping(true);
            }
        }

        return $addressDataObject;
    }

    /**
     * Validate address attribute values
     *
     *
     *
     * @return bool|array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    public function validate()
    {
        $errors = [];
        if (!\Zend_Validate::is($this->getFirstname(), 'NotEmpty')) {
            $errors[] = __('%fieldName is a required field.', ['fieldName' => 'firstname']);
        }

        if (!\Zend_Validate::is($this->getLastname(), 'NotEmpty')) {
            $errors[] = __('%fieldName is a required field.', ['fieldName' => 'lastname']);
        }

        if (!\Zend_Validate::is($this->getStreetLine(1), 'NotEmpty')) {
            $errors[] = __('%fieldName is a required field.', ['fieldName' => 'street']);
        }

        if (!\Zend_Validate::is($this->getCity(), 'NotEmpty')) {
            $errors[] = __('%fieldName is a required field.', ['fieldName' => 'city']);
        }

        if ($this->isTelephoneRequired()) {
            if (!\Zend_Validate::is($this->getTelephone(), 'NotEmpty')) {
                $errors[] = __('%fieldName is a required field.', ['fieldName' => 'telephone']);
            }
        }

        if ($this->isFaxRequired()) {
            if (!\Zend_Validate::is($this->getFax(), 'NotEmpty')) {
                $errors[] = __('%fieldName is a required field.', ['fieldName' => 'fax']);
            }
        }

        if ($this->isCompanyRequired()) {
            if (!\Zend_Validate::is($this->getCompany(), 'NotEmpty')) {
                $errors[] = __('%fieldName is a required field.', ['fieldName' => 'company']);
            }
        }

        $_havingOptionalZip = $this->_directoryData->getCountriesWithOptionalZip();
        if (!in_array(
            $this->getCountryId(),
            $_havingOptionalZip
        ) && !\Zend_Validate::is(
            $this->getPostcode(),
            'NotEmpty'
        )
        ) {
            $errors[] = __('%fieldName is a required field.', ['fieldName' => 'postcode']);
        }

        if (!\Zend_Validate::is($this->getCountryId(), 'NotEmpty')) {
            $errors[] = __('%fieldName is a required field.', ['fieldName' => 'countryId']);
        }

        if ($this->getCountryModel()->getRegionCollection()->getSize() && !\Zend_Validate::is(
            $this->getRegionId(),
            'NotEmpty'
        ) && $this->_directoryData->isRegionRequired(
            $this->getCountryId()
        )
        ) {
            $errors[] = __('%fieldName is a required field.', ['fieldName' => 'regionId']);
        }

        if (empty($errors) || $this->getShouldIgnoreValidation()) {
            return true;
        }
        return $errors;
    }

    /**
     * @return \Magento\Directory\Model\Region
     * @since 2.0.0
     */
    protected function _createRegionInstance()
    {
        return $this->_regionFactory->create();
    }

    /**
     * @return \Magento\Directory\Model\Country
     * @since 2.0.0
     */
    protected function _createCountryInstance()
    {
        return $this->_countryFactory->create();
    }

    /**
     * Unset Region from address
     * @return $this
     * @since 2.2.0
     */
    public function unsRegion()
    {
        return $this->unsetData("region");
    }

    /**
     * @return bool
     * @since 2.2.0
     */
    protected function isCompanyRequired()
    {
        return ($this->_eavConfig->getAttribute('customer_address', 'company')->getIsRequired());
    }

    /**
     * @return bool
     * @since 2.2.0
     */
    protected function isTelephoneRequired()
    {
        return ($this->_eavConfig->getAttribute('customer_address', 'telephone')->getIsRequired());
    }

    /**
     * @return bool
     * @since 2.2.0
     */
    protected function isFaxRequired()
    {
        return ($this->_eavConfig->getAttribute('customer_address', 'fax')->getIsRequired());
    }
}
