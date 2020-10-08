<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity;

use Magento\Eav\Model\Validator\Attribute\Code as AttributeCodeValidator;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;

/**
 * EAV Entity attribute model
 *
 * @api
 * @method \Magento\Eav\Model\Entity\Attribute setOption($value)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Attribute extends \Magento\Eav\Model\Entity\Attribute\AbstractAttribute implements
    \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Attribute code max length.
     *
     * The value is defined as 60 because in the flat mode attribute code will be transformed into column name.
     * MySQL allows only 64 symbols in column name.
     */
    const ATTRIBUTE_CODE_MAX_LENGTH = 60;

    /**
     * Min accepted length of an attribute code.
     */
    const ATTRIBUTE_CODE_MIN_LENGTH = 1;

    /**
     * Tag to use for attributes caching.
     */
    const CACHE_TAG = 'EAV_ATTRIBUTE';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'eav_entity_attribute';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getAttribute() in this case
     *
     * @var string
     */
    protected $_eventObject = 'attribute';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Catalog\Model\Product\ReservedAttributeList
     */
    protected $reservedAttributeList;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var DateTimeFormatterInterface
     */
    protected $dateTimeFormatter;

    /**
     * @var AttributeCodeValidator|null
     */
    private $attributeCodeValidator;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param TypeFactory $eavTypeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionDataFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Catalog\Model\Product\ReservedAttributeList $reservedAttributeList
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param DateTimeFormatterInterface $dateTimeFormatter
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param AttributeCodeValidator|null $attributeCodeValidator
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Entity\TypeFactory $eavTypeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionDataFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Catalog\Model\Product\ReservedAttributeList $reservedAttributeList,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        DateTimeFormatterInterface $dateTimeFormatter,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        AttributeCodeValidator $attributeCodeValidator = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $eavConfig,
            $eavTypeFactory,
            $storeManager,
            $resourceHelper,
            $universalFactory,
            $optionDataFactory,
            $dataObjectProcessor,
            $dataObjectHelper,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_localeDate = $localeDate;
        $this->_localeResolver = $localeResolver;
        $this->reservedAttributeList = $reservedAttributeList;
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->attributeCodeValidator = $attributeCodeValidator ?: ObjectManager::getInstance()->get(
            AttributeCodeValidator::class
        );
    }

    /**
     * Retrieve default attribute backend model by attribute code
     *
     * @return string
     */
    protected function _getDefaultBackendModel()
    {
        switch ($this->getAttributeCode()) {
            case 'created_at':
                return \Magento\Eav\Model\Entity\Attribute\Backend\Time\Created::class;

            case 'updated_at':
                return \Magento\Eav\Model\Entity\Attribute\Backend\Time\Updated::class;

            case 'store_id':
                return \Magento\Eav\Model\Entity\Attribute\Backend\Store::class;

            case 'increment_id':
                return \Magento\Eav\Model\Entity\Attribute\Backend\Increment::class;

            default:
                break;
        }

        return parent::_getDefaultBackendModel();
    }

    /**
     * Retrieve default attribute source model
     *
     * @return string
     */
    protected function _getDefaultSourceModel()
    {
        if ($this->getAttributeCode() == 'store_id') {
            return \Magento\Eav\Model\Entity\Attribute\Source\Store::class;
        }
        return parent::_getDefaultSourceModel();
    }

    /**
     * Delete entity
     *
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute
     * @throws LocalizedException
     * @codeCoverageIgnore
     */
    public function deleteEntity()
    {
        return $this->_getResource()->deleteEntity($this);
    }

    /**
     * Load entity_attribute_id into $this by $this->attribute_set_id
     *
     * @return $this
     */
    public function loadEntityAttributeIdBySet()
    {
        // load attributes collection filtered by attribute_id and attribute_set_id

        $filteredAttributes = $this->getResourceCollection()->setAttributeSetFilter(
            $this->getAttributeSetId()
        )->addFieldToFilter(
            'entity_attribute.attribute_id',
            $this->getId()
        )->load();
        if (count($filteredAttributes) > 0) {
            // getFirstItem() can be used as we can have one or zero records in the collection
            $this->setEntityAttributeId($filteredAttributes->getFirstItem()->getEntityAttributeId());
        }
        return $this;
    }

    /**
     * Prepare data for save
     *
     * @return $this
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function beforeSave()
    {
        if (isset($this->_data['attribute_code'])
            && !$this->attributeCodeValidator->isValid($this->_data['attribute_code'])
        ) {
            $errorMessages = implode("\n", $this->attributeCodeValidator->getMessages());
            throw new LocalizedException(__($errorMessages));
        }

        // prevent overriding product data
        if (isset($this->_data['attribute_code']) && $this->reservedAttributeList->isReservedAttribute($this)) {
            throw new LocalizedException(
                __(
                    'The attribute code \'%1\' is reserved by system. Please try another attribute code',
                    $this->_data['attribute_code']
                )
            );
        }

        $this->validateEntityType();

        $defaultValue = $this->getDefaultValue();
        $hasDefaultValue = (string)$defaultValue != '';

        if ($this->getBackendType() == 'decimal' && $hasDefaultValue) {
            $numberFormatter = new \NumberFormatter($this->_localeResolver->getLocale(), \NumberFormatter::DECIMAL);
            $defaultValue = $numberFormatter->parse($defaultValue);
            if ($defaultValue === false) {
                throw new LocalizedException(
                    __('The default decimal value is invalid. Verify the value and try again.')
                );
            }
            $this->setDefaultValue($defaultValue);
        }

        if ($this->getBackendType() == 'datetime') {
            if (!$this->getBackendModel()) {
                $this->setBackendModel(\Magento\Eav\Model\Entity\Attribute\Backend\Datetime::class);
            }

            if (!$this->getFrontendModel()) {
                $this->setFrontendModel(\Magento\Eav\Model\Entity\Attribute\Frontend\Datetime::class);
            }

            // save default date value as timestamp
            if ($hasDefaultValue) {
                $defaultValue = $this->getUtcDateDefaultValue($defaultValue);
                $this->setDefaultValue($defaultValue);
            }
        }

        if ($this->getFrontendInput() == 'media_image') {
            if (!$this->getFrontendModel()) {
                $this->setFrontendModel(\Magento\Catalog\Model\Product\Attribute\Frontend\Image::class);
            }
        }

        if ($this->getBackendType() == 'gallery') {
            if (!$this->getBackendModel()) {
                $this->setBackendModel(\Magento\Eav\Model\Entity\Attribute\Backend\DefaultBackend::class);
            }
        }

        return parent::beforeSave();
    }

    /**
     * Convert localized date default value to UTC
     *
     * @param string $defaultValue
     * @return string
     * @throws LocalizedException
     */
    private function getUtcDateDefaultValue(string $defaultValue): string
    {
        $hasTime = $this->getFrontendInput() === 'datetime';
        try {
            $defaultValue = $this->_localeDate->date($defaultValue, null, $hasTime, $hasTime);
            if ($hasTime) {
                $defaultValue->setTimezone(new \DateTimeZone($this->_localeDate->getDefaultTimezone()));
            }
            $utcValue = $defaultValue->format(DateTime::DATETIME_PHP_FORMAT);
        } catch (\Exception $e) {
            throw new LocalizedException(__('The default date is invalid. Verify the date and try again.'));
        }

        return $utcValue;
    }

    /**
     * @inheritdoc
     *
     * @return $this
     * @throws LocalizedException
     */
    public function afterSave()
    {
        $this->_getResource()->saveInSetIncluding($this);
        return parent::afterSave();
    }

    /**
     * Detect backend storage type using frontend input type
     *
     * @param string $type frontend_input field value
     * @return string backend_type field value
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getBackendTypeByInput($type)
    {
        $field = null;
        switch ($type) {
            case 'text':
            case 'gallery':
            case 'media_image':
            case 'multiselect':
                $field = 'varchar';
                break;

            case 'image':
            case 'textarea':
                $field = 'text';
                break;

            case 'date':
            case 'datetime':
                $field = 'datetime';
                break;

            case 'select':
            case 'boolean':
                $field = 'int';
                break;

            case 'price':
            case 'weight':
                $field = 'decimal';
                break;

            default:
                break;
        }

        return $field;
    }

    /**
     * Detect default value using frontend input type
     *
     * @param string $type frontend_input field name
     * @return string default_value field value
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getDefaultValueByInput($type)
    {
        $field = '';
        switch ($type) {
            case 'select':
            case 'gallery':
            case 'media_image':
                break;
            case 'multiselect':
                $field = null;
                break;

            case 'text':
            case 'price':
            case 'image':
            case 'weight':
                $field = 'default_value_text';
                break;

            case 'textarea':
            case 'texteditor':
                $field = 'default_value_textarea';
                break;

            case 'date':
                $field = 'default_value_date';
                break;

            case 'datetime':
                $field = 'default_value_datetime';
                break;

            case 'boolean':
                $field = 'default_value_yesno';
                break;

            default:
                break;
        }

        return $field;
    }

    /**
     * Retrieve attribute codes by frontend type
     *
     * @param string $type
     * @return array
     * @codeCoverageIgnore
     */
    public function getAttributeCodesByFrontendType($type)
    {
        return $this->getResource()->getAttributeCodesByFrontendType($type);
    }

    /**
     * Return array of labels of stores
     *
     * @return string[]
     */
    public function getStoreLabels()
    {
        if (!$this->getData('store_labels')) {
            $storeLabel = $this->getResource()->getStoreLabelsByAttributeId($this->getId());
            $this->setData('store_labels', $storeLabel);
        }
        return $this->getData('store_labels');
    }

    /**
     * Return store label of attribute
     *
     * @param int|null $storeId
     * @return string
     */
    public function getStoreLabel($storeId = null)
    {
        if ($this->hasData('store_label')) {
            return $this->getData('store_label');
        }
        $store = $this->_storeManager->getStore($storeId);
        $labels = $this->getStoreLabels();
        if (isset($labels[$store->getId()])) {
            return $labels[$store->getId()];
        } else {
            return $this->getFrontendLabel();
        }
    }

    /**
     * Get attribute sort weight
     *
     * @param int $setId
     * @return float
     */
    public function getSortWeight($setId)
    {
        $groupSortWeight = isset($this->_data['attribute_set_info'][$setId]['group_sort'])
            ? (float) $this->_data['attribute_set_info'][$setId]['group_sort'] * 1000
            : 0.0;
        $sortWeight = isset($this->_data['attribute_set_info'][$setId]['sort'])
            ? (float) $this->_data['attribute_set_info'][$setId]['sort'] * 0.0001
            : 0.0;
        return $groupSortWeight + $sortWeight;
    }

    /**
     * Get identities
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @inheritdoc
     * @since 100.0.7
     */
    public function __sleep()
    {
        $this->unsetData('attribute_set_info');
        return array_diff(
            parent::__sleep(),
            ['_localeDate', '_localeResolver', 'reservedAttributeList', 'dateTimeFormatter']
        );
    }

    /**
     * @inheritdoc
     * @since 100.0.7
     */
    public function __wakeup()
    {
        parent::__wakeup();
        $objectManager = ObjectManager::getInstance();
        $this->_localeDate = $objectManager->get(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $this->_localeResolver = $objectManager->get(\Magento\Framework\Locale\ResolverInterface::class);
        $this->reservedAttributeList = $objectManager->get(\Magento\Catalog\Model\Product\ReservedAttributeList::class);
        $this->dateTimeFormatter = $objectManager->get(DateTimeFormatterInterface::class);
    }

    /**
     * Entity type for existing attribute shouldn't be changed.
     *
     * @return void
     * @throws LocalizedException
     */
    private function validateEntityType(): void
    {
        if ($this->getId() !== null) {
            $origEntityTypeId = $this->getOrigData('entity_type_id');

            if (($origEntityTypeId !== null) && ((int)$this->getEntityTypeId() !== (int)$origEntityTypeId)) {
                throw new LocalizedException(__('Do not change entity type.'));
            }
        }
    }
}
