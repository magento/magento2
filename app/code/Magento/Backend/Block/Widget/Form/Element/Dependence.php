<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Form element dependencies mapper
 * Assumes that one element may depend on other element values.
 * Will toggle as "enabled" only if all elements it depends from toggle as true.
 */
namespace Magento\Backend\Block\Widget\Form\Element;

class Dependence extends \Magento\Backend\Block\AbstractBlock
{
    /**
     * name => id mapper
     * @var array
     */
    protected $_fields = [];

    /**
     * Dependencies mapper (by names)
     * array(
     *     'dependent_name' => array(
     *         'depends_from_1_name' => 'mixed value',
     *         'depends_from_2_name' => 'some another value',
     *         ...
     *     )
     * )
     * @var array
     */
    protected $_depends = [];

    /**
     * Additional configuration options for the dependencies javascript controller
     *
     * @var array
     */
    protected $_configOptions = [];

    /**
     * @var \Magento\Backend\Model\Config\Structure\Element\Dependency\FieldFactory
     */
    protected $_fieldFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Config\Structure\Element\Dependency\FieldFactory $fieldFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Config\Structure\Element\Dependency\FieldFactory $fieldFactory,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_fieldFactory = $fieldFactory;
        parent::__construct($context, $data);
    }

    /**
     * Add name => id mapping
     *
     * @param string $fieldId - element ID in DOM
     * @param string $fieldName - element name in their fieldset/form namespace
     * @return \Magento\Backend\Block\Widget\Form\Element\Dependence
     */
    public function addFieldMap($fieldId, $fieldName)
    {
        $this->_fields[$fieldName] = $fieldId;
        return $this;
    }

    /**
     * Register field name dependence one from each other by specified values
     *
     * @param string $fieldName
     * @param string $fieldNameFrom
     * @param \Magento\Backend\Model\Config\Structure\Element\Dependency\Field|string $refField
     * @return \Magento\Backend\Block\Widget\Form\Element\Dependence
     */
    public function addFieldDependence($fieldName, $fieldNameFrom, $refField)
    {
        if (!is_object($refField)) {
            /** @var $refField \Magento\Backend\Model\Config\Structure\Element\Dependency\Field */
            $refField = $this->_fieldFactory->create(
                ['fieldData' => ['value' => (string)$refField], 'fieldPrefix' => '']
            );
        }
        $this->_depends[$fieldName][$fieldNameFrom] = $refField;
        return $this;
    }

    /**
     * Add misc configuration options to the javascript dependencies controller
     *
     * @param array $options
     * @return \Magento\Backend\Block\Widget\Form\Element\Dependence
     */
    public function addConfigOptions(array $options)
    {
        $this->_configOptions = array_merge($this->_configOptions, $options);
        return $this;
    }

    /**
     * HTML output getter
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_depends) {
            return '';
        }
        return '<script type="text/javascript">
            require(["mage/adminhtml/form"], function(){
        new FormElementDependenceController(' .
            $this->_getDependsJson() .
            ($this->_configOptions ? ', ' .
            $this->_jsonEncoder->encode(
                $this->_configOptions
            ) : '') . '); });</script>';
    }

    /**
     * Field dependences JSON map generator
     * @return string
     */
    protected function _getDependsJson()
    {
        $result = [];
        foreach ($this->_depends as $to => $row) {
            foreach ($row as $from => $field) {
                /** @var $field \Magento\Backend\Model\Config\Structure\Element\Dependency\Field */
                $result[$this->_fields[$to]][$this->_fields[$from]] = [
                    'values' => $field->getValues(),
                    'negative' => $field->isNegative(),
                ];
            }
        }
        return $this->_jsonEncoder->encode($result);
    }
}
