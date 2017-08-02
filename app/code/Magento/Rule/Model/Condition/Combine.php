<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rule\Model\Condition;

/**
 * @api
 * @since 2.0.0
 */
class Combine extends AbstractCondition
{
    /**
     * @var \Magento\Rule\Model\ConditionFactory
     * @since 2.0.0
     */
    protected $_conditionFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     * @since 2.0.0
     */
    protected $_logger;

    /**
     * @param Context $context
     * @param array $data
     * @since 2.0.0
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
            foreach (array_keys($options) as $aggregator) {
                $this->setAggregator($aggregator);
                break;
            }
        }
    }

    /* start aggregator methods */

    /**
     * @return $this
     * @since 2.0.0
     */
    public function loadAggregatorOptions()
    {
        $this->setAggregatorOption(['all' => __('ALL'), 'any' => __('ANY')]);
        return $this;
    }

    /**
     * @return array
     * @since 2.0.0
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
     * @return string
     * @since 2.0.0
     */
    public function getAggregatorName()
    {
        return $this->getAggregatorOption($this->getAggregator());
    }

    /**
     * @return object
     * @since 2.0.0
     */
    public function getAggregatorElement()
    {
        if ($this->getAggregator() === null) {
            foreach (array_keys($this->getAggregatorOption()) as $key) {
                $this->setAggregator($key);
                break;
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
     * @return $this
     * @since 2.0.0
     */
    public function loadValueOptions()
    {
        $this->setValueOption([1 => __('TRUE'), 0 => __('FALSE')]);
        return $this;
    }

    /**
     * @param object $condition
     * @return $this
     * @since 2.0.0
     */
    public function addCondition($condition)
    {
        $condition->setRule($this->getRule());
        $condition->setObject($this->getObject());
        $condition->setPrefix($this->getPrefix());

        $conditions = $this->getConditions();
        $conditions[] = $condition;

        if (!$condition->getId()) {
            $condition->setId($this->getId() . '--' . sizeof($conditions));
        }

        $this->setData($this->getPrefix(), $conditions);
        return $this;
    }

    /**
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @param string $containerKey
     * @param string $itemKey
     * @return string
     * @since 2.0.0
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
     * @param array $arr
     * @param string $key
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
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
     * @param array|string $xml
     * @return $this
     * @since 2.0.0
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
     * @return string
     * @since 2.0.0
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
     * @return $this
     * @since 2.0.0
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
     * @return string
     * @since 2.0.0
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
     * @param string $format
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function asString($format = '')
    {
        $str = __("If %1 of these conditions are %2:", $this->getAggregatorName(), $this->getValueName());
        return $str;
    }

    /**
     * @param int $level
     * @return string
     * @since 2.0.0
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
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @param \Magento\Framework\Data\Form $form
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function _getRecursiveChildSelectOption()
    {
        return ['value' => $this->getType(), 'label' => __('Conditions Combination')];
    }
}
