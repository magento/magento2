<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Abstract Rule product condition data model
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Rule\Model\Condition\Product;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractProduct extends \Magento\Rule\Model\Condition\AbstractCondition
{
    /**
     * All attribute values as array in form:
     * array(
     *   [entity_id_1] => array(
     *          [store_id_1] => store_value_1,
     *          [store_id_2] => store_value_2,
     *          ...
     *          [store_id_n] => store_value_n
     *   ),
     *   ...
     * )
     *
     * Will be set only for not global scope attribute
     *
     * @var array
     */
    protected $_entityAttributeValues = null;

    /**
     * Attribute data key that indicates whether it should be used for rules
     *
     * @var string
     */
    protected $_isUsedForRuleProperty = 'is_used_for_promo_rules';

    /**
     * Adminhtml data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backendData;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_config;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $_productResource;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection
     */
    protected $_attrSetCollection;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $_localeFormat;

    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Backend\Helper\Data $backendData
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Eav\Model\Config $config,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        array $data = []
    ) {
        $this->_backendData = $backendData;
        $this->_config = $config;
        $this->_productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->_productResource = $productResource;
        $this->_attrSetCollection = $attrSetCollection;
        $this->_localeFormat = $localeFormat;
        parent::__construct($context, $data);
    }

    /**
     * Customize default operator input by type mapper for some types
     *
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        if (null === $this->_defaultOperatorInputByType) {
            parent::getDefaultOperatorInputByType();
            /*
             * '{}' and '!{}' are left for back-compatibility and equal to '==' and '!='
             */
            $this->_defaultOperatorInputByType['category'] = ['==', '!=', '{}', '!{}', '()', '!()'];
            $this->_arrayInputTypes[] = 'category';
        }
        return $this->_defaultOperatorInputByType;
    }

    /**
     * Retrieve attribute object
     *
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    public function getAttributeObject()
    {
        try {
            $obj = $this->_config->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $this->getAttribute());
        } catch (\Exception $e) {
            $obj = new \Magento\Framework\DataObject();
            $obj->setEntity($this->_productFactory->create())->setFrontendInput('text');
        }
        return $obj;
    }

    /**
     * Add special attributes
     *
     * @param array &$attributes
     * @return void
     */
    protected function _addSpecialAttributes(array &$attributes)
    {
        $attributes['attribute_set_id'] = __('Attribute Set');
        $attributes['category_ids'] = __('Category');
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $productAttributes = $this->_productResource->loadAllAttributes()->getAttributesByCode();

        $attributes = [];
        foreach ($productAttributes as $attribute) {
            /* @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
            if (!$attribute->isAllowedForRuleCondition() || !$attribute->getDataUsingMethod(
                $this->_isUsedForRuleProperty
            )
            ) {
                continue;
            }
            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }

        $this->_addSpecialAttributes($attributes);

        asort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * Prepares values options to be used as select options or hashed array
     * Result is stored in following keys:
     *  'value_select_options' - normal select array: array(array('value' => $value, 'label' => $label), ...)
     *  'value_option' - hashed array: array($value => $label, ...),
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _prepareValueOptions()
    {
        // Check that both keys exist. Maybe somehow only one was set not in this routine, but externally.
        $selectReady = $this->getData('value_select_options');
        $hashedReady = $this->getData('value_option');
        if ($selectReady && $hashedReady) {
            return $this;
        }

        // Get array of select options. It will be used as source for hashed options
        $selectOptions = null;
        if ($this->getAttribute() === 'attribute_set_id') {
            $entityTypeId = $this->_config->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getId();
            $selectOptions = $this->_attrSetCollection
                ->setEntityTypeFilter($entityTypeId)
                ->load()
                ->toOptionArray();
        } elseif ($this->getAttribute() === 'type_id') {
            foreach ($selectReady as $value => $label) {
                if (is_array($label) && isset($label['value'])) {
                    $selectOptions[] = $label;
                } else {
                    $selectOptions[] = ['value' => $value, 'label' => $label];
                }
            }
            $selectReady = null;
        } elseif (is_object($this->getAttributeObject())) {
            $attributeObject = $this->getAttributeObject();
            if ($attributeObject->usesSource()) {
                if ($attributeObject->getFrontendInput() == 'multiselect') {
                    $addEmptyOption = false;
                } else {
                    $addEmptyOption = true;
                }
                $selectOptions = $attributeObject->getSource()->getAllOptions($addEmptyOption);
            }
        }

        $this->_setSelectOptions($selectOptions, $selectReady, $hashedReady);

        return $this;
    }

    /**
     * Set new values only if we really got them
     *
     * @param array $selectOptions
     * @param array $selectReady
     * @param array $hashedReady
     * @return $this
     */
    protected function _setSelectOptions($selectOptions, $selectReady, $hashedReady)
    {
        if ($selectOptions !== null) {
            // Overwrite only not already existing values
            if (!$selectReady) {
                $this->setData('value_select_options', $selectOptions);
            }
            if (!$hashedReady) {
                $hashedOptions = [];
                foreach ($selectOptions as $option) {
                    if (is_array($option['value'])) {
                        continue; // We cannot use array as index
                    }
                    $hashedOptions[$option['value']] = $option['label'];
                }
                $this->setData('value_option', $hashedOptions);
            }
        }
        return $this;
    }

    /**
     * Retrieve value by option
     *
     * @param string|null $option
     * @return string
     */
    public function getValueOption($option = null)
    {
        $this->_prepareValueOptions();
        return $this->getData('value_option' . ($option !== null ? '/' . $option : ''));
    }

    /**
     * Retrieve select option values
     *
     * @return array
     */
    public function getValueSelectOptions()
    {
        $this->_prepareValueOptions();
        return $this->getData('value_select_options');
    }

    /**
     * Retrieve after element HTML
     *
     * @return string
     */
    public function getValueAfterElementHtml()
    {
        $html = '';

        switch ($this->getAttribute()) {
            case 'sku':
            case 'category_ids':
                $image = $this->_assetRepo->getUrl('images/rule_chooser_trigger.gif');
                break;
        }

        if (!empty($image)) {
            $html = '<a href="javascript:void(0)" class="rule-chooser-trigger"><img src="' .
                $image .
                '" alt="" class="v-middle rule-chooser-trigger" title="' .
                __(
                    'Open Chooser'
                ) . '" /></a>';
        }
        return $html;
    }

    /**
     * Retrieve attribute element
     *
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    /**
     * Collect validated attributes
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        $attribute = $this->getAttribute();
        if ('category_ids' != $attribute) {
            $productCollection->addAttributeToSelect($attribute, 'left');
            if ($this->getAttributeObject()->isScopeGlobal()) {
                $attributes = $this->getRule()->getCollectedAttributes();
                $attributes[$attribute] = true;
                $this->getRule()->setCollectedAttributes($attributes);
            } else {
                $this->_entityAttributeValues = $productCollection->getAllAttributeValues($attribute);
            }
        }

        return $this;
    }

    /**
     * Retrieve input type
     *
     * @return string
     */
    public function getInputType()
    {
        if ($this->getAttribute() === 'attribute_set_id') {
            return 'select';
        }
        if (!is_object($this->getAttributeObject())) {
            return 'string';
        }
        if ($this->getAttributeObject()->getAttributeCode() == 'category_ids') {
            return 'category';
        }
        switch ($this->getAttributeObject()->getFrontendInput()) {
            case 'select':
                return 'select';

            case 'multiselect':
                return 'multiselect';

            case 'date':
                return 'date';

            case 'boolean':
                return 'boolean';

            default:
                return 'string';
        }
    }

    /**
     * Retrieve value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        if ($this->getAttribute() === 'attribute_set_id') {
            return 'select';
        }
        if (!is_object($this->getAttributeObject())) {
            return 'text';
        }
        switch ($this->getAttributeObject()->getFrontendInput()) {
            case 'select':
            case 'boolean':
                return 'select';

            case 'multiselect':
                return 'multiselect';

            case 'date':
                return 'date';

            default:
                return 'text';
        }
    }

    /**
     * Retrieve value element chooser URL
     *
     * @return string
     */
    public function getValueElementChooserUrl()
    {
        $url = false;
        switch ($this->getAttribute()) {
            case 'sku':
            case 'category_ids':
                $url = 'catalog_rule/promo_widget/chooser/attribute/' . $this->getAttribute();
                if ($this->getJsFormObject()) {
                    $url .= '/form/' . $this->getJsFormObject();
                }
                break;
            default:
                break;
        }
        return $url !== false ? $this->_backendData->getUrl($url) : '';
    }

    /**
     * Retrieve Explicit Apply
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getExplicitApply()
    {
        switch ($this->getAttribute()) {
            case 'sku':
            case 'category_ids':
                return true;
            default:
                break;
        }
        if (is_object($this->getAttributeObject())) {
            switch ($this->getAttributeObject()->getFrontendInput()) {
                case 'date':
                    return true;
                default:
                    break;
            }
        }
        return false;
    }

    /**
     * Load array
     *
     * @param array $arr
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function loadArray($arr)
    {
        $this->setAttribute(isset($arr['attribute']) ? $arr['attribute'] : false);
        $attribute = $this->getAttributeObject();

        $isContainsOperator = !empty($arr['operator']) && in_array($arr['operator'], ['{}', '!{}']);
        if ($attribute && $attribute->getBackendType() == 'decimal' && !$isContainsOperator) {
            if (isset($arr['value'])) {
                if (!empty($arr['operator']) && in_array(
                    $arr['operator'],
                    ['!()', '()']
                ) && false !== strpos(
                    $arr['value'],
                    ','
                )
                ) {
                    $tmp = [];
                    foreach (explode(',', $arr['value']) as $value) {
                        $tmp[] = $this->_localeFormat->getNumber($value);
                    }
                    $arr['value'] = implode(',', $tmp);
                } else {
                    $arr['value'] = $this->_localeFormat->getNumber($arr['value']);
                }
            } else {
                $arr['value'] = false;
            }
            $arr['is_value_parsed'] = isset(
                $arr['is_value_parsed']
            ) ? $this->_localeFormat->getNumber(
                $arr['is_value_parsed']
            ) : false;
        }

        return parent::loadArray($arr);
    }

    /**
     * Validate product attribute value for condition
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $attrCode = $this->getAttribute();

        if ('category_ids' == $attrCode) {
            return $this->validateAttribute($model->getAvailableInCategories());
        } elseif (!isset($this->_entityAttributeValues[$model->getId()])) {
            if (!$model->getResource()) {
                return false;
            }
            $attr = $model->getResource()->getAttribute($attrCode);

            if ($attr && $attr->getBackendType() == 'datetime' && !is_int($this->getValue())) {
                $this->setValue(strtotime($this->getValue()));
                $value = strtotime($model->getData($attrCode));
                return $this->validateAttribute($value);
            }

            if ($attr && $attr->getFrontendInput() == 'multiselect') {
                $value = $model->getData($attrCode);
                $value = strlen($value) ? explode(',', $value) : [];
                return $this->validateAttribute($value);
            }

            return parent::validate($model);
        } else {
            $result = false;
            // any valid value will set it to TRUE
            // remember old attribute state
            $oldAttrValue = $model->hasData($attrCode) ? $model->getData($attrCode) : null;

            foreach ($this->_entityAttributeValues[$model->getId()] as $value) {
                $attr = $model->getResource()->getAttribute($attrCode);
                if ($attr && $attr->getBackendType() == 'datetime') {
                    $value = strtotime($value);
                } elseif ($attr && $attr->getFrontendInput() == 'multiselect') {
                    $value = strlen($value) ? explode(',', $value) : [];
                }

                $model->setData($attrCode, $value);
                $result |= parent::validate($model);

                if ($result) {
                    break;
                }
            }

            if ($oldAttrValue === null) {
                $model->unsetData($attrCode);
            } else {
                $model->setData($attrCode, $oldAttrValue);
            }

            return (bool)$result;
        }
    }

    /**
     * Get argument value to bind
     *
     * @return array|float|int|mixed|string|\Zend_Db_Expr
     */
    public function getBindArgumentValue()
    {
        if ($this->getAttribute() == 'category_ids') {
            return new \Zend_Db_Expr(
                $this->_productResource->getConnection()
                ->select()
                ->from(
                    $this->_productResource->getTable('catalog_category_product'),
                    ['product_id']
                )->where(
                    'category_id IN (?)',
                    $this->getValueParsed()
                )->__toString()
            );
        }
        return parent::getBindArgumentValue();
    }

    /**
     * Get mapped sql field
     *
     * @return string
     */
    public function getMappedSqlField()
    {
        if ($this->getAttribute() == 'sku') {
            $mappedSqlField = 'e.sku';
        } elseif (!$this->isAttributeSetOrCategory()) {
            $mappedSqlField = $this->getEavAttributeTableAlias() . '.value';
        } elseif ($this->getAttribute() == 'category_ids') {
            $mappedSqlField = 'e.entity_id';
        } else {
            $mappedSqlField = parent::getMappedSqlField();
        }
        return $mappedSqlField;
    }

    /**
     * Validate product by entity ID
     *
     * @param int $productId
     * @return bool
     */
    public function validateByEntityId($productId)
    {
        if ('category_ids' == $this->getAttribute()) {
            $result = $this->validateAttribute($this->_getAvailableInCategories($productId));
        } elseif ('attribute_set_id' == $this->getAttribute()) {
            $result = $this->validateAttribute($this->_getAttributeSetId($productId));
        } else {
            $product = $this->productRepository->getById($productId);
            $result = $this->validate($product);
            unset($product);
        }
        return $result;
    }

    /**
     * Retrieve category ids where product is available
     *
     * @param int $productId
     * @return array
     */
    protected function _getAvailableInCategories($productId)
    {
        return $this->_productResource->getConnection()
            ->fetchCol(
                $this->_productResource->getConnection()
                    ->select()
                    ->distinct()
                    ->from(
                        $this->_productResource->getTable('catalog_category_product'),
                        ['category_id']
                    )->where(
                        'product_id = ?',
                        $productId
                    )
            );
    }

    /**
     * Get attribute set id for product
     *
     * @param int $productId
     * @return string
     */
    protected function _getAttributeSetId($productId)
    {
        return $this->_productResource->getConnection()
            ->fetchOne(
                $this->_productResource->getConnection()
                    ->select()
                    ->distinct()
                    ->from(
                        $this->_productResource->getTable('catalog_product_entity'),
                        ['attribute_set_id']
                    )->where(
                        'entity_id = ?',
                        $productId
                    )
            );
    }

    /**
     * Correct '==' and '!=' operators
     * Categories can't be equal because product is included categories selected by administrator and in their parents
     *
     * @return string
     */
    public function getOperatorForValidate()
    {
        $operator = $this->getOperator();
        if ($this->getInputType() == 'category') {
            if ($operator == '==') {
                $operator = '{}';
            } elseif ($operator == '!=') {
                $operator = '!{}';
            }
        }

        return $operator;
    }

    /**
     * Check is attribute set or category
     *
     * @return bool
     */
    protected function isAttributeSetOrCategory()
    {
        return in_array($this->getAttribute(), ['attribute_set_id', 'category_ids']);
    }

    /**
     * Get eav attribute alias
     *
     * @return string
     */
    protected function getEavAttributeTableAlias()
    {
        $attribute = $this->getAttributeObject();

        return 'at_' . $attribute->getAttributeCode();
    }
}
