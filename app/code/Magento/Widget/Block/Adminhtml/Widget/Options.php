<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Block\Adminhtml\Widget;

/**
 * WYSIWYG widget options form
 *
 * @api
 * @since 100.0.2
 */
class Options extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Element type used by default if configuration is omitted
     * @var string
     */
    protected $_defaultElementType = 'text';

    /**
     * @var \Magento\Widget\Model\Widget
     */
    protected $_widget;

    /**
     * @var \Magento\Framework\Option\ArrayPool
     */
    protected $_sourceModelPool;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Option\ArrayPool $sourceModelPool
     * @param \Magento\Widget\Model\Widget $widget
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Option\ArrayPool $sourceModelPool,
        \Magento\Widget\Model\Widget $widget,
        array $data = []
    ) {
        $this->_sourceModelPool = $sourceModelPool;
        $this->_widget = $widget;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare Widget Options Form and values according to specified type
     *
     * The widget_type must be set in data before
     * widget_values may be set before to render element values
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $this->getForm()->setUseContainer(false);
        $this->addFields();
        return $this;
    }

    /**
     * Form getter/instantiation
     *
     * @return \Magento\Framework\Data\Form
     */
    public function getForm()
    {
        if ($this->_form instanceof \Magento\Framework\Data\Form) {
            return $this->_form;
        }
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $this->setForm($form);
        return $form;
    }

    /**
     * Fieldset getter/instantiation
     *
     * @return \Magento\Framework\Data\Form\Element\Fieldset
     */
    public function getMainFieldset()
    {
        if ($this->_getData('main_fieldset') instanceof \Magento\Framework\Data\Form\Element\Fieldset) {
            return $this->_getData('main_fieldset');
        }
        $mainFieldsetHtmlId = 'options_fieldset' . md5($this->getWidgetType());
        $this->setMainFieldsetHtmlId($mainFieldsetHtmlId);
        $fieldset = $this->getForm()->addFieldset(
            $mainFieldsetHtmlId,
            ['legend' => __('Widget Options'), 'class' => 'fieldset-wide fieldset-widget-options']
        );
        $this->setData('main_fieldset', $fieldset);

        // add dependence javascript block
        $block = $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Form\Element\Dependence::class);
        $this->setChild('form_after', $block);

        return $fieldset;
    }

    /**
     * Add fields to main fieldset based on specified widget type
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return $this
     */
    public function addFields()
    {
        // get configuration node and translation helper
        if (!$this->getWidgetType()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please specify a Widget Type.'));
        }
        $config = $this->_widget->getConfigAsObject($this->getWidgetType());
        if (!$config->getParameters()) {
            return $this;
        }
        foreach ($config->getParameters() as $parameter) {
            $this->_addField($parameter);
        }

        return $this;
    }

    /**
     * Add field to Options form based on parameter configuration
     *
     * @param \Magento\Framework\DataObject $parameter
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _addField($parameter)
    {
        $form = $this->getForm();
        $fieldset = $this->getMainFieldset();
        //$form->getElement('options_fieldset');

        // prepare element data with values (either from request of from default values)
        $fieldName = $parameter->getKey();
        $data = [
            'name' => $form->addSuffixToName($fieldName, 'parameters'),
            'label' => __($parameter->getLabel()),
            'required' => $parameter->getRequired(),
            'class' => 'widget-option',
            'note' => __($parameter->getDescription()),
        ];

        if ($values = $this->getWidgetValues()) {
            $data['value'] = isset($values[$fieldName]) ? $values[$fieldName] : '';
        } else {
            $data['value'] = $parameter->getValue();
        }

        //prepare unique id value
        if ($fieldName == 'unique_id' && $data['value'] == '') {
            $data['value'] = hash('sha256', microtime(1));
        }

        if (is_array($data['value'])) {
            foreach ($data['value'] as &$value) {
                $value = html_entity_decode($value);
            }
        } else {
            $data['value'] = html_entity_decode($data['value']);
        }

        // prepare element dropdown values
        if ($values = $parameter->getValues()) {
            // dropdown options are specified in configuration
            $data['values'] = [];
            foreach ($values as $option) {
                $data['values'][] = ['label' => __($option['label']), 'value' => $option['value']];
            }
            // otherwise, a source model is specified
        } elseif ($sourceModel = $parameter->getSourceModel()) {
            $data['values'] = $this->_sourceModelPool->get($sourceModel)->toOptionArray();
        }

        // prepare field type or renderer
        $fieldRenderer = null;
        $fieldType = $parameter->getType();
        // hidden element
        if (!$parameter->getVisible()) {
            $fieldType = 'hidden';
            // just an element renderer
        } elseif ($fieldType && $this->_isClassName($fieldType)) {
            $fieldRenderer = $this->getLayout()->createBlock($fieldType);
            $fieldType = $this->_defaultElementType;
        }

        // instantiate field and render html
        $field = $fieldset->addField($this->getMainFieldsetHtmlId() . '_' . $fieldName, $fieldType, $data);
        if ($fieldRenderer) {
            $field->setRenderer($fieldRenderer);
        }

        // extra html preparations
        if ($helper = $parameter->getHelperBlock()) {
            $helperBlock = $this->getLayout()->createBlock(
                $helper->getType(),
                '',
                ['data' => $helper->getData()]
            );
            if ($helperBlock instanceof \Magento\Framework\DataObject) {
                $helperBlock->setConfig(
                    $helper->getData()
                )->setFieldsetId(
                    $fieldset->getId()
                )->prepareElementHtml(
                    $field
                );
            }
        }

        // dependencies from other fields
        $dependenceBlock = $this->getChildBlock('form_after');
        $dependenceBlock->addFieldMap($field->getId(), $fieldName);
        if ($parameter->getDepends()) {
            foreach ($parameter->getDepends() as $from => $row) {
                $values = isset($row['values']) ? array_values($row['values']) : (string)$row['value'];
                $dependenceBlock->addFieldDependence($fieldName, $from, $values);
            }
        }

        return $field;
    }

    /**
     * Checks whether $fieldType is a class name of custom renderer, and not just a type of input element
     *
     * @param string $fieldType
     * @return bool
     */
    protected function _isClassName($fieldType)
    {
        return preg_match('/[A-Z]/', $fieldType) > 0;
    }
}
