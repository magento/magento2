<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Rule\Model\Condition;

use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Abstract Rule condition data model
 *
 * @method string getOperator()
 * @method string getFormName()
 * @method setFormName()
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @api
 * @since 100.0.2
 */
abstract class AbstractCondition extends \Magento\Framework\DataObject implements ConditionInterface
{
    /**
     * Defines which operators will be available for this condition
     * @var string
     */
    protected $_inputType = null;

    /**
     * Default values for possible operator options
     * @var array
     */
    protected $_defaultOperatorOptions = null;

    /**
     * Default combinations of operator options, depending on input type
     * @var array
     */
    protected $_defaultOperatorInputByType = null;

    /**
     * List of input types for values which should be array
     * @var string[]
     */
    protected $_arrayInputTypes = [];

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * Base name for hidden elements.
     *
     * @var string
     */
    protected $elementName = 'rule';

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(Context $context, array $data = [])
    {
        $this->_assetRepo = $context->getAssetRepository();
        $this->_localeDate = $context->getLocaleDate();
        $this->_layout = $context->getLayout();

        parent::__construct($data);

        $this->loadAttributeOptions()->loadOperatorOptions()->loadValueOptions();

        $options = $this->getAttributeOptions();
        if ($options) {
            reset($options);
            $this->setAttribute(key($options));
        }
        $options = $this->getOperatorOptions();
        if ($options) {
            reset($options);
            $this->setOperator(key($options));
        }
    }

    /**
     * Default operator input by type map getter
     *
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        if (null === $this->_defaultOperatorInputByType) {
            $this->_defaultOperatorInputByType = [
                'string' => ['==', '!=', '>=', '>', '<=', '<', '{}', '!{}', '()', '!()'],
                'numeric' => ['==', '!=', '>=', '>', '<=', '<', '()', '!()'],
                'date' => ['==', '>=', '<='],
                'select' => ['==', '!=', '<=>'],
                'boolean' => ['==', '!=', '<=>'],
                'multiselect' => ['{}', '!{}', '()', '!()'],
                'grid' => ['()', '!()'],
            ];
            $this->_arrayInputTypes = ['multiselect', 'grid'];
        }
        return $this->_defaultOperatorInputByType;
    }

    /**
     * Default operator options getter.
     *
     * Provides all possible operator options.
     *
     * @return array
     */
    public function getDefaultOperatorOptions()
    {
        if (null === $this->_defaultOperatorOptions) {
            $this->_defaultOperatorOptions = [
                '==' => __('is'),
                '!=' => __('is not'),
                '>=' => __('equals or greater than'),
                '<=' => __('equals or less than'),
                '>' => __('greater than'),
                '<' => __('less than'),
                '{}' => __('contains'),
                '!{}' => __('does not contain'),
                '()' => __('is one of'),
                '!()' => __('is not one of'),
                '<=>' => __('is undefined'),
            ];
        }
        return $this->_defaultOperatorOptions;
    }

    /**
     * Get rule form.
     *
     * @return Form
     */
    public function getForm()
    {
        return $this->getRule()->getForm();
    }

    /**
     * Get condition as array.
     *
     * @param array $arrAttributes
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function asArray(array $arrAttributes = [])
    {
        return [
            'type' => $this->getType(),
            'attribute' => $this->getAttribute(),
            'operator' => $this->getOperator(),
            'value' => $this->getValue(),
            'is_value_processed' => $this->getIsValueParsed(),
        ];
    }

    /**
     * Get tables to join
     *
     * @return array
     */
    public function getTablesToJoin()
    {
        return [];
    }

    /**
     * Get value to bind
     *
     * @return array|float|int|mixed|string
     */
    public function getBindArgumentValue()
    {
        return $this->getValueParsed();
    }

    /**
     * Get field by attribute
     *
     * @return string
     */
    public function getMappedSqlField()
    {
        return $this->getAttribute();
    }

    /**
     * Get condition as xml.
     *
     * @return string
     */
    public function asXml()
    {
        return "<type>" .
            $this->getType() .
            "</type>" .
            "<attribute>" .
            $this->getAttribute() .
            "</attribute>" .
            "<operator>" .
            $this->getOperator() .
            "</operator>" .
            "<value>" .
            $this->getValue() .
            "</value>";
    }

    /**
     * Load condition from array.
     *
     * @param array $arr
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function loadArray($arr)
    {
        $this->setType($arr['type']);
        $this->setAttribute(isset($arr['attribute']) ? $arr['attribute'] : false);
        $this->setOperator(isset($arr['operator']) ? $arr['operator'] : false);
        $this->setValue(isset($arr['value']) ? $arr['value'] : false);
        $this->setIsValueParsed(isset($arr['is_value_parsed']) ? $arr['is_value_parsed'] : false);
        return $this;
    }

    /**
     * Load condition from xml.
     *
     * @param string|array $xml
     * @return $this
     */
    public function loadXml($xml)
    {
        if (is_string($xml)) {
            $xml = simplexml_load_string($xml);
        }
        $this->loadArray((array)$xml);
        return $this;
    }

    /**
     * Load attribute options.
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        return $this;
    }

    /**
     * Get attribute options.
     *
     * @return array
     */
    public function getAttributeOptions()
    {
        return [];
    }

    /**
     * Get attribute select options.
     *
     * @return array
     */
    public function getAttributeSelectOptions()
    {
        $opt = [];
        foreach ($this->getAttributeOption() as $key => $value) {
            $opt[] = ['value' => $key, 'label' => $value];
        }
        return $opt;
    }

    /**
     * Get attribute name.
     *
     * @return string
     */
    public function getAttributeName()
    {
        return $this->getAttributeOption($this->getAttribute());
    }

    /**
     * Load operator options.
     *
     * @return $this
     */
    public function loadOperatorOptions()
    {
        $this->setOperatorOption($this->getDefaultOperatorOptions());
        $this->setOperatorByInputType($this->getDefaultOperatorInputByType());
        return $this;
    }

    /**
     * This value will define which operators will be available for this condition.
     *
     * Possible values are: string, numeric, date, select, multiselect, grid, boolean
     *
     * @return string
     */
    public function getInputType()
    {
        return null === $this->_inputType ? 'string' : $this->_inputType;
    }

    /**
     * Get operator select options.
     *
     * @return array
     */
    public function getOperatorSelectOptions()
    {
        $type = $this->getInputType();
        $opt = [];
        $operatorByType = $this->getOperatorByInputType();
        foreach ($this->getOperatorOption() as $key => $value) {
            if (!$operatorByType || in_array($key, $operatorByType[$type])) {
                $opt[] = ['value' => $key, 'label' => $value];
            }
        }
        return $opt;
    }

    /**
     * Get operator name.
     *
     * @return array
     */
    public function getOperatorName()
    {
        return $this->getOperatorOption($this->getOperator());
    }

    /**
     * Load value options.
     *
     * @return $this
     */
    public function loadValueOptions()
    {
        $this->setValueOption([]);
        return $this;
    }

    /**
     * Get value select options.
     *
     * @return array
     */
    public function getValueSelectOptions()
    {
        $opt = [];
        if ($this->hasValueOption()) {
            foreach ((array)$this->getValueOption() as $key => $value) {
                $opt[] = ['value' => $key, 'label' => $value];
            }
        }
        return $opt;
    }

    /**
     * Retrieve parsed value
     *
     * @return array|string|int|float
     */
    public function getValueParsed()
    {
        if (!$this->hasValueParsed()) {
            $value = $this->getData('value');
            if (is_array($value) && count($value) === 1) {
                $value = reset($value);
            }
            if (!is_array($value) && $this->isArrayOperatorType() && $value) {
                $value = preg_split('#\s*[,;]\s*#', $value, null, PREG_SPLIT_NO_EMPTY);
            }
            $this->setValueParsed($value);
        }
        return $this->getData('value_parsed');
    }

    /**
     * Check if value should be array
     *
     * Depends on operator input type
     *
     * @return bool
     */
    public function isArrayOperatorType()
    {
        $operator = $this->getOperator();
        return $operator === '()' || $operator === '!()' || in_array($this->getInputType(), $this->_arrayInputTypes);
    }

    /**
     * Get value.
     *
     * @return mixed
     */
    public function getValue()
    {
        if ($this->getInputType() == 'date' && !$this->getIsValueParsed()) {
            // date format intentionally hard-coded
            $this->setValue(
                (new \DateTime($this->getData('value')))->format('Y-m-d H:i:s')
            );
            $this->setIsValueParsed(true);
        }
        return $this->getData('value');
    }

    /**
     * Get value name.
     *
     * @return array|string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getValueName()
    {
        $value = $this->getValue();
        if ($value === null || '' === $value) {
            return '...';
        }

        $options = $this->getValueSelectOptions();
        $valueArr = [];
        if (!empty($options)) {
            foreach ($options as $option) {
                if (is_array($value)) {
                    if (in_array($option['value'], $value)) {
                        $valueArr[] = $option['label'];
                    }
                } elseif (isset($option['value'])) {
                    if (is_array($option['value'])) {
                        foreach ($option['value'] as $optionValue) {
                            if ($optionValue['value'] == $value) {
                                return $optionValue['label'];
                            }
                        }
                    }
                    if ($option['value'] == $value) {
                        return $option['label'];
                    }
                }
            }
        }
        if (!empty($valueArr)) {
            $value = implode(', ', $valueArr);
        } elseif (is_array($value)) {
            $value = implode(', ', $value);
        }
        return $value;
    }

    /**
     * Get inherited conditions selectors
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        return [['value' => '', 'label' => __('Please choose a condition to add.')]];
    }

    /**
     * Get new child name.
     *
     * @return string
     */
    public function getNewChildName()
    {
        return $this->getAddLinkHtml();
    }

    /**
     * Get this condition as html.
     *
     * @return string
     */
    public function asHtml()
    {
        return $this->getTypeElementHtml() .
            $this->getAttributeElementHtml() .
            $this->getOperatorElementHtml() .
            $this->getValueElementHtml() .
            $this->getRemoveLinkHtml() .
            $this->getChooserContainerHtml();
    }

    /**
     * Get this condition with subconditions as html.
     *
     * @return string
     */
    public function asHtmlRecursive()
    {
        return $this->asHtml();
    }

    /**
     * Get type element.
     *
     * @return AbstractElement
     */
    public function getTypeElement()
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__type',
            'hidden',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][type]',
                'value' => $this->getType(),
                'no_span' => true,
                'class' => 'hidden',
                'data-form-part' => $this->getFormName()
            ]
        );
    }

    /**
     * Get type element html.
     *
     * @return string
     */
    public function getTypeElementHtml()
    {
        return $this->getTypeElement()->getHtml();
    }

    /**
     * Get attribute element.
     *
     * @return $this
     */
    public function getAttributeElement()
    {
        if (null === $this->getAttribute()) {
            $options = $this->getAttributeOption();
            if ($options) {
                reset($options);
                $this->setAttribute(key($options));
            }
        }
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__attribute',
            'select',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][attribute]',
                'values' => $this->getAttributeSelectOptions(),
                'value' => $this->getAttribute(),
                'value_name' => $this->getAttributeName(),
                'data-form-part' => $this->getFormName()
            ]
        )->setRenderer(
            $this->_layout->getBlockSingleton(\Magento\Rule\Block\Editable::class)
        );
    }

    /**
     * Get attribute element html.
     *
     * @return string
     */
    public function getAttributeElementHtml()
    {
        return $this->getAttributeElement()->getHtml();
    }

    /**
     * Retrieve Condition Operator element Instance.
     *
     * If the operator value is empty - define first available operator value as default.
     *
     * @return \Magento\Framework\Data\Form\Element\Select
     */
    public function getOperatorElement()
    {
        $options = $this->getOperatorSelectOptions();
        if ($this->getOperator() === null) {
            $option = reset($options);
            $this->setOperator($option['value']);
        }

        $elementId = sprintf('%s__%s__operator', $this->getPrefix(), $this->getId());
        $elementName = sprintf($this->elementName . '[%s][%s][operator]', $this->getPrefix(), $this->getId());
        $element = $this->getForm()->addField(
            $elementId,
            'select',
            [
                'name' => $elementName,
                'values' => $options,
                'value' => $this->getOperator(),
                'value_name' => $this->getOperatorName(),
                'data-form-part' => $this->getFormName()
            ]
        );
        $element->setRenderer($this->_layout->getBlockSingleton(\Magento\Rule\Block\Editable::class));

        return $element;
    }

    /**
     * Get operator element html.
     *
     * @return string
     */
    public function getOperatorElementHtml()
    {
        return $this->getOperatorElement()->getHtml();
    }

    /**
     * Value element type will define renderer for condition value element
     *
     * @see \Magento\Framework\Data\Form\Element
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }

    /**
     * Get value element renderer.
     *
     * @return \Magento\Rule\Block\Editable
     */
    public function getValueElementRenderer()
    {
        if (strpos($this->getValueElementType(), '/') !== false) {
            return $this->_layout->getBlockSingleton($this->getValueElementType());
        }
        return $this->_layout->getBlockSingleton(\Magento\Rule\Block\Editable::class);
    }

    /**
     * Get value element.
     *
     * @return $this
     */
    public function getValueElement()
    {
        $elementParams = [
            'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][value]',
            'value' => $this->getValue(),
            'values' => $this->getValueSelectOptions(),
            'value_name' => $this->getValueName(),
            'after_element_html' => $this->getValueAfterElementHtml(),
            'explicit_apply' => $this->getExplicitApply(),
            'data-form-part' => $this->getFormName()
        ];
        if ($this->getInputType() == 'date') {
            // date format intentionally hard-coded
            $elementParams['input_format'] = \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT;
            $elementParams['date_format'] = \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT;
            $elementParams['placeholder'] = \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT;
            $elementParams['autocomplete'] = 'off';
            $elementParams['readonly'] = 'true';
        }
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__value',
            $this->getValueElementType(),
            $elementParams
        )->setRenderer(
            $this->getValueElementRenderer()
        );
    }

    /**
     * Get value element html.
     *
     * @return string
     */
    public function getValueElementHtml()
    {
        return $this->getValueElement()->getHtml();
    }

    /**
     * Get add link html.
     *
     * @return string
     */
    public function getAddLinkHtml()
    {
        $src = $this->_assetRepo->getUrl('images/rule_component_add.gif');
        return '<img src="' . $src . '" class="rule-param-add v-middle" alt="" title="' . __('Add') . '"/>';
    }

    /**
     * Get remove link html.
     *
     * @return string
     */
    public function getRemoveLinkHtml()
    {
        $src = $this->_assetRepo->getUrl('images/rule_component_remove.gif');
        $html = ' <span class="rule-param"><a href="javascript:void(0)" class="rule-param-remove" title="' . __(
            'Remove'
        ) . '"><img src="' . $src . '"  alt="" class="v-middle" /></a></span>';
        return $html;
    }

    /**
     * Get chooser container html.
     *
     * @return string
     */
    public function getChooserContainerHtml()
    {
        $url = $this->getValueElementChooserUrl();
        return $url ? '<div class="rule-chooser" url="' . $url . '"></div>' : '';
    }

    /**
     * Get this condition as string.
     *
     * @param string $format
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function asString($format = '')
    {
        return $this->getAttributeName() . ' ' . $this->getOperatorName() . ' ' . $this->getValueName();
    }

    /**
     * Get this condition with subconditions as string.
     *
     * @param int $level
     * @return string
     */
    public function asStringRecursive($level = 0)
    {
        return str_pad('', $level * 3, ' ', STR_PAD_LEFT) . $this->asString();
    }

    /**
     * Validate product attribute value for condition
     *
     * @param   object|array|int|string|float|bool $validatedValue product attribute value
     * @return  bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function validateAttribute($validatedValue)
    {
        if (is_object($validatedValue)) {
            return false;
        }

        /**
         * Condition attribute value
         */
        $value = $this->getValueParsed();

        /**
         * Comparison operator
         */
        $option = $this->getOperatorForValidate();

        // if operator requires array and it is not, or on opposite, return false
        if ($this->isArrayOperatorType() xor is_array($value)) {
            return false;
        }

        $result = false;

        switch ($option) {
            case '==':
            case '!=':
                if (is_array($value)) {
                    if (!is_array($validatedValue)) {
                        return false;
                    }
                    $result = !empty(array_intersect($value, $validatedValue));
                } else {
                    if (is_array($validatedValue)) {
                        $result = count($validatedValue) == 1 && array_shift($validatedValue) == $value;
                    } else {
                        $result = $this->_compareValues($validatedValue, $value);
                    }
                }
                break;

            case '<=':
            case '>':
                if (!is_scalar($validatedValue)) {
                    return false;
                }
                $result = $validatedValue <= $value;
                break;

            case '>=':
            case '<':
                if (!is_scalar($validatedValue)) {
                    return false;
                }
                $result = $validatedValue >= $value;
                break;

            case '{}':
            case '!{}':
                if (is_scalar($validatedValue) && is_array($value)) {
                    foreach ($value as $item) {
                        if (stripos($validatedValue, (string)$item) !== false) {
                            $result = true;
                            break;
                        }
                    }
                } elseif (is_array($value)) {
                    if (!is_array($validatedValue)) {
                        return false;
                    }
                    $result = array_intersect($value, $validatedValue);
                    $result = !empty($result);
                } else {
                    if (is_array($validatedValue)) {
                        $result = in_array($value, $validatedValue);
                    } else {
                        $result = $this->_compareValues($value, $validatedValue, false);
                    }
                }
                break;

            case '()':
            case '!()':
                if (is_array($validatedValue)) {
                    $result = count(array_intersect($validatedValue, (array)$value)) > 0;
                } else {
                    $value = (array)$value;
                    foreach ($value as $item) {
                        if ($this->_compareValues($validatedValue, $item)) {
                            $result = true;
                            break;
                        }
                    }
                }
                break;
        }

        if ('!=' == $option || '>' == $option || '<' == $option || '!{}' == $option || '!()' == $option) {
            $result = !$result;
        }

        return $result;
    }

    /**
     * Case and type insensitive comparison of values
     *
     * @param string|int|float $validatedValue
     * @param string|int|float $value
     * @param bool $strict
     * @return bool
     */
    protected function _compareValues($validatedValue, $value, $strict = true)
    {
        if ($strict && is_numeric($validatedValue) && is_numeric($value)) {
            return $validatedValue == $value;
        }

        $validatePattern = preg_quote($validatedValue, '~');
        if ($strict) {
            $validatePattern = '^' . $validatePattern . '$';
        }
        return (bool)preg_match('~' . $validatePattern . '~iu', $value);
    }

    /**
     * Validate model.
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        if (!$model->hasData($this->getAttribute())) {
            $model->load($model->getId());
        }
        $attributeValue = $model->getData($this->getAttribute());

        return $this->validateAttribute($attributeValue);
    }

    /**
     * Retrieve operator for php validation
     *
     * @return string
     */
    public function getOperatorForValidate()
    {
        return $this->getOperator();
    }
}
