<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rule\Model\Condition;

/**
 * Combine
 *
 * @api
 * @since 100.0.2
 */
class Combine extends AbstractCondition
{
    /**
     * @var \Magento\Rule\Model\ConditionFactory
     */
    protected $_conditionFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * Construct
     *
     * @param Context $context
     * @param array $data
     */
    public function __construct(Context $context, array $data = [])
    {
        $this->_conditionFactory = $context->getConditionFactory();
        $this->_logger = $context->getLogger();

        parent::__construct($context, $data);
        $this->setType(
            \Magento\Rule\Model\Condition\Combine::class
        )->setAggregator(
            'all'
        )->setValue(
            true
        )->setConditions(
            []
        )->setActions(
            []
        );

        $this->loadAggregatorOptions();
        $options = $this->getAggregatorOptions();
        if ($options) {
            reset($options);
            $this->setAggregator(key($options));
        }
    }

    /* start aggregator methods */

    /**
     * Load aggregation options
     *
     * @return $this
     */
    public function loadAggregatorOptions()
    {
        $this->setAggregatorOption(['all' => __('ALL'), 'any' => __('ANY')]);
        return $this;
    }

    /**
     * Return agregator selected options
     *
     * @return array
     */
    public function getAggregatorSelectOptions()
    {
        $opt = [];
        foreach ($this->getAggregatorOption() as $key => $value) {
            $opt[] = ['value' => $key, 'label' => $value];
        }
        return $opt;
    }

    /**
     * Get Agregator name
     *
     * @return string
     */
    public function getAggregatorName()
    {
        return $this->getAggregatorOption($this->getAggregator());
    }

    /**
     * Return agregator element
     *
     * @return object
     */
    public function getAggregatorElement()
    {
        if ($this->getAggregator() === null) {
            $options = $this->getAggregatorOption();
            if ($options) {
                reset($options);
                $this->setAggregator(key($options));
            }
        }
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__aggregator',
            'select',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][aggregator]',
                'values' => $this->getAggregatorSelectOptions(),
                'value' => $this->getAggregator(),
                'value_name' => $this->getAggregatorName(),
                'data-form-part' => $this->getFormName()
            ]
        )->setRenderer(
            $this->_layout->getBlockSingleton(\Magento\Rule\Block\Editable::class)
        );
    }

    /* end aggregator methods */

    /**
     * Load value options
     *
     * @return $this
     */
    public function loadValueOptions()
    {
        $this->setValueOption([1 => __('TRUE'), 0 => __('FALSE')]);
        return $this;
    }

    /**
     * Adds condition
     *
     * @param object $condition
     * @return $this
     */
    public function addCondition($condition)
    {
        $condition->setRule($this->getRule());
        $condition->setObject($this->getObject());
        $condition->setPrefix($this->getPrefix());

        $conditions = $this->getConditions();
        $conditions[] = $condition;

        if (!$condition->getId()) {
            $condition->setId($this->getId() . '--' . count($conditions));
        }

        $this->setData($this->getPrefix(), $conditions);
        return $this;
    }

    /**
     * Return value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'select';
    }

    /**
     * Returns array containing conditions in the collection
     *
     * Output example:
     * array(
     *   'type'=>'combine',
     *   'operator'=>'ALL',
     *   'value'=>'TRUE',
     *   'conditions'=>array(
     *     {condition::asArray},
     *     {combine::asArray},
     *     {quote_item_combine::asArray}
     *   )
     * )
     *
     * @param array $arrAttributes
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function asArray(array $arrAttributes = [])
    {
        $out = parent::asArray();
        $out['aggregator'] = $this->getAggregator();

        foreach ($this->getConditions() as $condition) {
            $out['conditions'][] = $condition->asArray();
        }

        return $out;
    }

    /**
     * As xml
     *
     * @param string $containerKey
     * @param string $itemKey
     * @return string
     */
    public function asXml($containerKey = 'conditions', $itemKey = 'condition')
    {
        $xml = "<aggregator>" .
            $this->getAggregator() .
            "</aggregator>" .
            "<value>" .
            $this->getValue() .
            "</value>" .
            "<{$containerKey}>";
        foreach ($this->getConditions() as $condition) {
            $xml .= "<{$itemKey}>" . $condition->asXml() . "</{$itemKey}>";
        }
        $xml .= "</{$containerKey}>";
        return $xml;
    }

    /**
     * Load array
     *
     * @param array $arr
     * @param string $key
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function loadArray($arr, $key = 'conditions')
    {
        $this->setAggregator(
            isset($arr['aggregator']) ? $arr['aggregator'] : (isset($arr['attribute']) ? $arr['attribute'] : null)
        )->setValue(
            isset($arr['value']) ? $arr['value'] : (isset($arr['operator']) ? $arr['operator'] : null)
        );

        if (!empty($arr[$key]) && is_array($arr[$key])) {
            foreach ($arr[$key] as $conditionArr) {
                try {
                    $condition = $this->_conditionFactory->create($conditionArr['type']);
                    $this->addCondition($condition);
                    $condition->loadArray($conditionArr, $key);
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                }
            }
        }
        return $this;
    }

    /**
     * Load xml
     *
     * @param array|string $xml
     * @return $this
     */
    public function loadXml($xml)
    {
        if (is_string($xml)) {
            $xml = simplexml_load_string($xml);
        }
        $arr = parent::loadXml($xml);
        foreach ($xml->conditions->children() as $condition) {
            $arr['conditions'] = parent::loadXml($condition);
        }
        $this->loadArray($arr);
        return $this;
    }

    /**
     * As html
     *
     * @return string
     */
    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml() . __(
            'If %1 of these conditions are %2:',
            $this->getAggregatorElement()->getHtml(),
            $this->getValueElement()->getHtml()
        );
        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }
        return $html;
    }

    /**
     * Get new child element
     *
     * @return $this
     */
    public function getNewChildElement()
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__new_child',
            'select',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][new_child]',
                'values' => $this->getNewChildSelectOptions(),
                'value_name' => $this->getNewChildName(),
                'data-form-part' => $this->getFormName()
            ]
        )->setRenderer(
            $this->_layout->getBlockSingleton(\Magento\Rule\Block\Newchild::class)
        );
    }

    /**
     * As html recursive
     *
     * @return string
     */
    public function asHtmlRecursive()
    {
        $html = $this->asHtml() .
            '<ul id="' .
            $this->getPrefix() .
            '__' .
            $this->getId() .
            '__children" class="rule-param-children">';
        foreach ($this->getConditions() as $cond) {
            $html .= '<li>' . $cond->asHtmlRecursive() . '</li>';
        }
        $html .= '<li>' . $this->getNewChildElement()->getHtml() . '</li></ul>';
        return $html;
    }

    /**
     * As string
     *
     * @param string $format
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function asString($format = '')
    {
        $str = __("If %1 of these conditions are %2:", $this->getAggregatorName(), $this->getValueName());
        return $str;
    }

    /**
     * As string recursive
     *
     * @param int $level
     * @return string
     */
    public function asStringRecursive($level = 0)
    {
        $str = parent::asStringRecursive($level);
        foreach ($this->getConditions() as $cond) {
            $str .= "\n" . $cond->asStringRecursive($level + 1);
        }
        return $str;
    }

    /**
     * Validate
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        return $this->_isValid($model);
    }

    /**
     * Validate by entity ID
     *
     * @param int $entityId
     * @return mixed
     */
    public function validateByEntityId($entityId)
    {
        return $this->_isValid($entityId);
    }

    /**
     * Is entity valid
     *
     * @param int|\Magento\Framework\Model\AbstractModel $entity
     * @return bool
     */
    protected function _isValid($entity)
    {
        if (!$this->getConditions()) {
            return true;
        }

        $all = $this->getAggregator() === 'all';
        $true = (bool)$this->getValue();

        foreach ($this->getConditions() as $cond) {
            if ($entity instanceof \Magento\Framework\Model\AbstractModel) {
                $validated = $cond->validate($entity);
            } else {
                $validated = $cond->validateByEntityId($entity);
            }
            if ($all && $validated !== $true) {
                return false;
            } elseif (!$all && $validated === $true) {
                return true;
            }
        }
        return $all ? true : false;
    }

    /**
     * Set js From object
     *
     * @param \Magento\Framework\Data\Form $form
     * @return $this
     */
    public function setJsFormObject($form)
    {
        $this->setData('js_form_object', $form);
        foreach ($this->getConditions() as $condition) {
            $condition->setJsFormObject($form);
        }
        return $this;
    }

    /**
     * Get conditions, if current prefix is undefined use 'conditions' key
     *
     * @return array
     */
    public function getConditions()
    {
        $key = $this->getPrefix() ? $this->getPrefix() : 'conditions';
        return $this->getData($key);
    }

    /**
     * Set conditions, if current prefix is undefined use 'conditions' key
     *
     * @param array $conditions
     * @return $this
     */
    public function setConditions($conditions)
    {
        $key = $this->getPrefix() ? $this->getPrefix() : 'conditions';
        return $this->setData($key, $conditions);
    }

    /**
     * Getter for "Conditions Combination" select option for recursive combines
     *
     * @return array
     */
    protected function _getRecursiveChildSelectOption()
    {
        return ['value' => $this->getType(), 'label' => __('Conditions Combination')];
    }
}
