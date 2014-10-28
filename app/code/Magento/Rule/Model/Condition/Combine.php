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
namespace Magento\Rule\Model\Condition;

class Combine extends AbstractCondition
{
    /**
     * Store all used condition models
     *
     * @var array
     */
    protected $_conditionModels = array();

    /**
     * @var \Magento\Rule\Model\ConditionFactory
     */
    protected $_conditionFactory;

    /**
     * @var \Magento\Framework\Logger
     */
    protected $_logger;

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(Context $context, array $data = array())
    {
        $this->_conditionFactory = $context->getConditionFactory();
        $this->_logger = $context->getLogger();

        parent::__construct($context, $data);
        $this->setType(
            'Magento\Rule\Model\Condition\Combine'
        )->setAggregator(
            'all'
        )->setValue(
            true
        )->setConditions(
            array()
        )->setActions(
            array()
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

    /**
     * Retrieve new object for each requested model.
     * If model is requested first time, store it at static array.
     *
     * It's made by performance reasons to avoid initialization of same models each time when rules are being processed.
     *
     * @param  string $modelClass
     * @return AbstractCondition|bool
     */
    protected function _getNewConditionModelInstance($modelClass)
    {
        if (empty($modelClass)) {
            return false;
        }

        if (!array_key_exists($modelClass, $this->_conditionModels)) {
            $model = $this->_conditionFactory->create($modelClass);
            $this->_conditionModels[$modelClass] = $model;
        } else {
            $model = $this->_conditionModels[$modelClass];
        }

        if (!$model) {
            return false;
        }

        $newModel = clone $model;
        return $newModel;
    }

    /* start aggregator methods */
    /**
     * @return $this
     */
    public function loadAggregatorOptions()
    {
        $this->setAggregatorOption(array('all' => __('ALL'), 'any' => __('ANY')));
        return $this;
    }

    /**
     * @return array
     */
    public function getAggregatorSelectOptions()
    {
        $opt = array();
        foreach ($this->getAggregatorOption() as $key => $value) {
            $opt[] = array('value' => $key, 'label' => $value);
        }
        return $opt;
    }

    /**
     * @return string
     */
    public function getAggregatorName()
    {
        return $this->getAggregatorOption($this->getAggregator());
    }

    /**
     * @return object
     */
    public function getAggregatorElement()
    {
        if (is_null($this->getAggregator())) {
            foreach (array_keys($this->getAggregatorOption()) as $key) {
                $this->setAggregator($key);
                break;
            }
        }
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__aggregator',
            'select',
            array(
                'name' => 'rule[' . $this->getPrefix() . '][' . $this->getId() . '][aggregator]',
                'values' => $this->getAggregatorSelectOptions(),
                'value' => $this->getAggregator(),
                'value_name' => $this->getAggregatorName()
            )
        )->setRenderer(
            $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
        );
    }

    /* end aggregator methods */

    /**
     * @return $this
     */
    public function loadValueOptions()
    {
        $this->setValueOption(array(1 => __('TRUE'), 0 => __('FALSE')));
        return $this;
    }

    /**
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
            $condition->setId($this->getId() . '--' . sizeof($conditions));
        }

        $this->setData($this->getPrefix(), $conditions);
        return $this;
    }

    /**
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
    public function asArray(array $arrAttributes = array())
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
     */
    public function loadArray($arr, $key = 'conditions')
    {
        $this->setAggregator(
            isset($arr['aggregator']) ? $arr['aggregator'] : (isset($arr['attribute']) ? $arr['attribute'] : null)
        )->setValue(
            isset($arr['value']) ? $arr['value'] : (isset($arr['operator']) ? $arr['operator'] : null)
        );

        if (!empty($arr[$key]) && is_array($arr[$key])) {
            foreach ($arr[$key] as $condArr) {
                try {
                    $cond = $this->_getNewConditionModelInstance($condArr['type']);
                    if ($cond) {
                        $this->addCondition($cond);
                        $cond->loadArray($condArr, $key);
                    }
                } catch (\Exception $e) {
                    $this->_logger->logException($e);
                }
            }
        }
        return $this;
    }

    /**
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
     * @return $this
     */
    public function getNewChildElement()
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__new_child',
            'select',
            array(
                'name' => 'rule[' . $this->getPrefix() . '][' . $this->getId() . '][new_child]',
                'values' => $this->getNewChildSelectOptions(),
                'value_name' => $this->getNewChildName()
            )
        )->setRenderer(
            $this->_layout->getBlockSingleton('Magento\Rule\Block\Newchild')
        );
    }

    /**
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
     * @param \Magento\Framework\Object $object
     * @return bool
     */
    public function validate(\Magento\Framework\Object $object)
    {
        return $this->_isValid($object);
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
     * @param int|\Magento\Framework\Object $entity
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
            if ($entity instanceof \Magento\Framework\Object) {
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
        return array('value' => $this->getType(), 'label' => __('Conditions Combination'));
    }
}
