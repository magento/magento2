<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * WYSIWYG widget options form
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Widget\Block\Adminhtml\Widget;

class Chooser extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $_elementFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_elementFactory = $elementFactory;
        parent::__construct($context, $data);
    }

    /**
     * Chooser source URL getter
     *
     * @return string
     */
    public function getSourceUrl()
    {
        return $this->_getData('source_url');
    }

    /**
     * Chooser form element getter
     *
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getElement()
    {
        return $this->_getData('element');
    }

    /**
     * Convert Array config to Object
     *
     * @return \Magento\Framework\DataObject
     */
    public function getConfig()
    {
        if ($this->_getData('config') instanceof \Magento\Framework\DataObject) {
            return $this->_getData('config');
        }

        $configArray = $this->_getData('config');
        $config = new \Magento\Framework\DataObject();
        $this->setConfig($config);
        if (!is_array($configArray)) {
            return $this->_getData('config');
        }

        // define chooser label
        if (isset($configArray['label'])) {
            $config->setData('label', __($configArray['label']));
        }

        // chooser control buttons
        $buttons = ['open' => __('Choose...'), 'close' => __('Close')];
        if (isset($configArray['button']) && is_array($configArray['button'])) {
            foreach ($configArray['button'] as $id => $label) {
                $buttons[$id] = __($label);
            }
        }
        $config->setButtons($buttons);

        return $this->_getData('config');
    }

    /**
     * Unique identifier for block that uses Chooser
     *
     * @return string
     */
    public function getUniqId()
    {
        return $this->_getData('uniq_id');
    }

    /**
     * Form element fieldset id getter for working with form in chooser
     *
     * @return string
     */
    public function getFieldsetId()
    {
        return $this->_getData('fieldset_id');
    }

    /**
     * Flag to indicate include hidden field before chooser or not
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getHiddenEnabled()
    {
        return $this->hasData('hidden_enabled') ? (bool)$this->_getData('hidden_enabled') : true;
    }

    /**
     * Return chooser HTML and init scripts
     *
     * @return string
     */
    protected function _toHtml()
    {
        $element = $this->getElement();
        /* @var $fieldset \Magento\Framework\Data\Form\Element\Fieldset */
        $fieldset = $element->getForm()->getElement($this->getFieldsetId());
        $chooserId = $this->getUniqId();
        $config = $this->getConfig();

        // add chooser element to fieldset
        $chooser = $fieldset->addField(
            'chooser' . $element->getId(),
            'note',
            ['label' => $config->getLabel() ? $config->getLabel() : '', 'value_class' => 'value2']
        );
        $hiddenHtml = '';
        if ($this->getHiddenEnabled()) {
            $hidden = $this->_elementFactory->create('hidden', ['data' => $element->getData()]);
            $hidden->setId("{$chooserId}value")->setForm($element->getForm());
            if ($element->getRequired()) {
                $hidden->addClass('required-entry');
            }
            $hiddenHtml = $hidden->getElementHtml();
            $element->setValue('');
        }

        $buttons = $config->getButtons();
        $chooseButton = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setType(
            'button'
        )->setId(
            $chooserId . 'control'
        )->setClass(
            'btn-chooser'
        )->setLabel(
            $buttons['open']
        )->setOnclick(
            $chooserId . '.choose()'
        )->setDisabled(
            $element->getReadonly()
        );
        $chooser->setData('after_element_html', $hiddenHtml . $chooseButton->toHtml());

        // render label and chooser scripts
        $configJson = $this->_jsonEncoder->encode($config->getData());
        return '
            <label class="widget-option-label" id="' .
            $chooserId .
            'label">' .
            ($this->getLabel() ? $this->getLabel() : __(
                'Not Selected'
            )) .
            '</label>
            <div id="' .
            $chooserId .
            'advice-container" class="hidden"></div>
            <script>
            require(["prototype", "mage/adminhtml/wysiwyg/widget"], function(){
            //<![CDATA[
                (function() {
                    var instantiateChooser = function() {
                        window.' .
            $chooserId .
            ' = new WysiwygWidget.chooser(
                            "' .
            $chooserId .
            '",
                            "' .
            $this->getSourceUrl() .
            '",
                            ' .
            $configJson .
            '
                        );
                        if ($("' .
            $chooserId .
            'value")) {
                            $("' .
            $chooserId .
            'value").advaiceContainer = "' .
            $chooserId .
            'advice-container";
                        }
                    }

                    jQuery(instantiateChooser);
                })();
            //]]>
            });
            </script>
        ';
    }
}
