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
namespace Magento\Customer\Model\Address;

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
 * @method string getPostcode()
 * @method bool getShouldIgnoreValidation()
 */
class AbstractAddress extends \Magento\Framework\Model\AbstractModel
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
     */
    protected $_eventPrefix = 'customer_address';

    /**
     * Name of event object
     *
     * @var string
     */
    protected $_eventObject = 'customer_address';

    /**
     * Directory country models
     *
     * @var \Magento\Directory\Model\Country[]
     */
    protected static $_countryModels = array();

    /**
     * Directory region models
     *
     * @var \Magento\Directory\Model\Region[]
     */
    protected static $_regionModels = array();

    /**
     * Directory data
     *
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryData = null;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var Config
     */
    protected $_addressConfig;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $_regionFactory;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $_countryFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param Config $addressConfig
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\Eav\Model\Config $eavConfig,
        Config $addressConfig,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_directoryData = $directoryData;
        $data = $this->_implodeStreetField($data);
        $this->_eavConfig = $eavConfig;
        $this->_addressConfig = $addressConfig;
        $this->_regionFactory = $regionFactory;
        $this->_countryFactory = $countryFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Get full customer name
     *
     * @return string
     */
    public function getName()
    {
        $name = '';
        if ($this->_eavConfig->getAttribute('customer_address', 'prefix')->getIsVisible() && $this->getPrefix()) {
            $name .= $this->getPrefix() . ' ';
        }
        $name .= $this->getFirstname();
        if ($this->_eavConfig->getAttribute('customer_address', 'middlename')->getIsVisible()
            && $this->getMiddlename()
        ) {
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
     * @param int|null $line Number of a line, value of which to return. Supported values:
     *                       0|null - return array of all lines
     *                       1..n   - return text of individual line
     * @return array|string
     */
    public function getStreet($line = 0)
    {
        $lines = explode("\n", $this->getStreetFull());
        if (0 === $line || $line === null) {
            return $lines;
        } elseif (isset($lines[$line - 1])) {
            return $lines[$line - 1];
        } else {
            return '';
        }
    }

    /**
     * @return string
     */
    public function getStreet1()
    {
        return $this->getStreet(1);
    }

    /**
     * @return string
     */
    public function getStreet2()
    {
        return $this->getStreet(2);
    }

    /**
     * @return string
     */
    public function getStreet3()
    {
        return $this->getStreet(3);
    }

    /**
     * @return string
     */
    public function getStreet4()
    {
        return $this->getStreet(4);
    }

    /**
     * Retrieve text of street lines, concatenated using LF symbol
     *
     * @return string
     */
    public function getStreetFull()
    {
        return $this->getData('street');
    }

    /**
     * Alias for a street setter. To be used though setDataUsingMethod('street_full', $value).
     *
     * @param string|string[] $street
     * @return $this
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
     */
    public function setStreet($street)
    {
        $this->setData('street', $street);
        return $this;
    }

    /**
     * Enforce format of the street field
     *
     * @param array|string $key
     * @param null $value
     * @return \Magento\Framework\Object
     */
    public function setData($key, $value = null)
    {
        if (is_array($key)) {
            $key = $this->_implodeStreetField($key);
        } elseif ($key == 'street') {
            $value = $this->_implodeStreetValue($value);
        }
        return parent::setData($key, $value);
    }

    /**
     * Implode value of the street field, if it is present among other fields
     *
     * @param array $data
     * @return array
     */
    protected function _implodeStreetField(array $data)
    {
        if (array_key_exists('street', $data)) {
            $data['street'] = $this->_implodeStreetValue($data['street']);
        }
        return $data;
    }

    /**
     * Combine values of street lines into a single string
     *
     * @param string[]|string $value
     * @return string
     */
    protected function _implodeStreetValue($value)
    {
        if (is_array($value)) {
            $value = trim(implode("\n", $value));
        }
        return $value;
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
        foreach ($streetLines as $i => $line) {
            $this->setData('street' . ($i + 1), $line);
        }
        return $this;
    }

    /**
     * Retrieve region name
     *
     * @return string
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
     */
    public function getRegionId()
    {
        $regionId = $this->getData('region_id');
        $region = $this->getData('region');
        if (!$regionId) {
            if (is_numeric($region)) {
                $this->setData('region_id', $region);
                //@TODO method unsRegion() is neither defined in abstract model nor in it's children
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
     */
    public function getRegionModel($regionId = null)
    {
        if (is_null($regionId)) {
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
     * $addressData = \Magento\Customer\Service\V1\Data\AddressConverter::toFlatArray($address);
     * $formattedAddress = $renderer->renderArray($addressData);
     *
     * @param string $type
     * @return string|null
     * @deprecated
     */
    public function format($type)
    {
        if (!($formatType = $this->getConfig()->getFormatByCode($type)) || !$formatType->getRenderer()) {
            return null;
        }
        $this->_eventManager->dispatch('customer_address_format', array('type' => $formatType, 'address' => $this));
        return $formatType->getRenderer()->render($this);
    }

    /**
     * Retrieve address config object
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->_addressConfig;
    }

    /**
     * @return $this
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        $this->getRegion();
        return $this;
    }

    /**
     * Validate address attribute values
     *
     * @return bool|array
     */
    public function validate()
    {
        $errors = array();
        if (!\Zend_Validate::is($this->getFirstname(), 'NotEmpty')) {
            $errors[] = __('Please enter the first name.');
        }

        if (!\Zend_Validate::is($this->getLastname(), 'NotEmpty')) {
            $errors[] = __('Please enter the last name.');
        }

        if (!\Zend_Validate::is($this->getStreet(1), 'NotEmpty')) {
            $errors[] = __('Please enter the street.');
        }

        if (!\Zend_Validate::is($this->getCity(), 'NotEmpty')) {
            $errors[] = __('Please enter the city.');
        }

        if (!\Zend_Validate::is($this->getTelephone(), 'NotEmpty')) {
            $errors[] = __('Please enter the phone number.');
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
            $errors[] = __('Please enter the zip/postal code.');
        }

        if (!\Zend_Validate::is($this->getCountryId(), 'NotEmpty')) {
            $errors[] = __('Please enter the country.');
        }

        if ($this->getCountryModel()->getRegionCollection()->getSize() && !\Zend_Validate::is(
            $this->getRegionId(),
            'NotEmpty'
        ) && $this->_directoryData->isRegionRequired(
            $this->getCountryId()
        )
        ) {
            $errors[] = __('Please enter the state/province.');
        }

        if (empty($errors) || $this->getShouldIgnoreValidation()) {
            return true;
        }
        return $errors;
    }

    /**
     * @return \Magento\Directory\Model\Region
     */
    protected function _createRegionInstance()
    {
        return $this->_regionFactory->create();
    }

    /**
     * @return \Magento\Directory\Model\Country
     */
    protected function _createCountryInstance()
    {
        return $this->_countryFactory->create();
    }
}
