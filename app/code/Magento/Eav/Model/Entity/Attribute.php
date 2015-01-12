<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity;

use Magento\Eav\Exception;
use Magento\Framework\Api\AttributeDataBuilder;

/**
 * EAV Entity attribute model
 *
 * @method \Magento\Eav\Model\Entity\Attribute setOption($value)
 */
class Attribute extends \Magento\Eav\Model\Entity\Attribute\AbstractAttribute implements
    \Magento\Framework\Object\IdentityInterface
{
    /**
     * Attribute code max length
     */
    const ATTRIBUTE_CODE_MAX_LENGTH = 30;

    /**
     * Cache tag
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
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\MetadataServiceInterface $metadataService
     * @param AttributeDataBuilder $customAttributeBuilder
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param TypeFactory $eavTypeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\Resource\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Eav\Api\Data\AttributeOptionDataBuilder $optionDataBuilder
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Catalog\Model\Product\ReservedAttributeList $reservedAttributeList
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\MetadataServiceInterface $metadataService,
        AttributeDataBuilder $customAttributeBuilder,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Entity\TypeFactory $eavTypeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Resource\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Eav\Api\Data\AttributeOptionDataBuilder $optionDataBuilder,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Catalog\Model\Product\ReservedAttributeList $reservedAttributeList,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $metadataService,
            $customAttributeBuilder,
            $coreData,
            $eavConfig,
            $eavTypeFactory,
            $storeManager,
            $resourceHelper,
            $universalFactory,
            $optionDataBuilder,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_localeDate = $localeDate;
        $this->_localeResolver = $localeResolver;
        $this->reservedAttributeList = $reservedAttributeList;
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
                return 'Magento\Eav\Model\Entity\Attribute\Backend\Time\Created';

            case 'updated_at':
                return 'Magento\Eav\Model\Entity\Attribute\Backend\Time\Updated';

            case 'store_id':
                return 'Magento\Eav\Model\Entity\Attribute\Backend\Store';

            case 'increment_id':
                return 'Magento\Eav\Model\Entity\Attribute\Backend\Increment';

            default:
                break;
        }

        return parent::_getDefaultBackendModel();
    }

    /**
     * Retrieve default attribute frontend model
     *
     * @return string
     */
    protected function _getDefaultFrontendModel()
    {
        return parent::_getDefaultFrontendModel();
    }

    /**
     * Retrieve default attribute source model
     *
     * @return string
     */
    protected function _getDefaultSourceModel()
    {
        if ($this->getAttributeCode() == 'store_id') {
            return 'Magento\Eav\Model\Entity\Attribute\Source\Store';
        }
        return parent::_getDefaultSourceModel();
    }

    /**
     * Delete entity
     *
     * @return \Magento\Eav\Model\Resource\Entity\Attribute
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
     * @throws Exception
     */
    public function beforeSave()
    {
        // prevent overriding product data
        if (isset($this->_data['attribute_code']) && $this->reservedAttributeList->isReservedAttribute($this)) {
            throw new Exception(
                __(
                    'The attribute code \'%1\' is reserved by system. Please try another attribute code',
                    $this->_data['attribute_code']
                )
            );
        }

        /**
         * Check for maximum attribute_code length
         */
        if (isset(
            $this->_data['attribute_code']
        ) && !\Zend_Validate::is(
            $this->_data['attribute_code'],
            'StringLength',
            ['max' => self::ATTRIBUTE_CODE_MAX_LENGTH]
        )
        ) {
            throw new Exception(
                __('Maximum length of attribute code must be less than %1 symbols', self::ATTRIBUTE_CODE_MAX_LENGTH)
            );
        }

        $defaultValue = $this->getDefaultValue();
        $hasDefaultValue = (string)$defaultValue != '';

        if ($this->getBackendType() == 'decimal' && $hasDefaultValue) {
            if (!\Zend_Locale_Format::isNumber(
                $defaultValue,
                ['locale' => $this->_localeResolver->getLocaleCode()]
            )
            ) {
                throw new Exception(__('Invalid default decimal value'));
            }

            try {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->_localeResolver->getLocaleCode()]
                );
                $this->setDefaultValue($filter->filter($defaultValue));
            } catch (\Exception $e) {
                throw new Exception(__('Invalid default decimal value'));
            }
        }

        if ($this->getBackendType() == 'datetime') {
            if (!$this->getBackendModel()) {
                $this->setBackendModel('Magento\Eav\Model\Entity\Attribute\Backend\Datetime');
            }

            if (!$this->getFrontendModel()) {
                $this->setFrontendModel('Magento\Eav\Model\Entity\Attribute\Frontend\Datetime');
            }

            // save default date value as timestamp
            if ($hasDefaultValue) {
                $format = $this->_localeDate->getDateFormat(
                    \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT
                );
                try {
                    $defaultValue = $this->_localeDate->date($defaultValue, $format, null, false)->toValue();
                    $this->setDefaultValue($defaultValue);
                } catch (\Exception $e) {
                    throw new Exception(__('Invalid default date'));
                }
            }
        }

        if ($this->getBackendType() == 'gallery') {
            if (!$this->getBackendModel()) {
                $this->setBackendModel('Magento\Eav\Model\Entity\Attribute\Backend\DefaultBackend');
            }
        }

        return parent::beforeSave();
    }

    /**
     * Save additional data
     *
     * @return $this
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
                $field = 'default_value_textarea';
                break;

            case 'date':
                $field = 'default_value_date';
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
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
