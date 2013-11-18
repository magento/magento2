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
 * @category    Magento
 * @package     Magento_Widget
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * WYSIWYG widget options form
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */

namespace Magento\Widget\Block\Adminhtml\Widget;

class Chooser extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Data\Form\Element\Factory
     */
    protected $_elementFactory;

    /**
     * @param \Magento\Data\Form\Element\Factory $elementFactory
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Data\Form\Element\Factory $elementFactory,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Backend\Block\Template\Context $context,
        array $data = array()
    ) {
        $this->_elementFactory = $elementFactory;
        parent::__construct($coreData, $context, $data);
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
     * @return \Magento\Data\Form\Element\AbstractElement
     */
    public function getElement()
    {
        return $this->_getData('element');
    }

    /**
     * Convert Array config to Object
     *
     * @return \Magento\Object
     */
    public function getConfig()
    {
        if ($this->_getData('config') instanceof \Magento\Object) {
            return $this->_getData('config');
        }

        $configArray = $this->_getData('config');
        $config = new \Magento\Object();
        $this->setConfig($config);
        if (!is_array($configArray)) {
            return $this->_getData('config');
        }

        // define chooser label
        if (isset($configArray['label'])) {
            $config->setData('label', __($configArray['label']));
        }

        // chooser control buttons
        $buttons = array(
            'open'  => __('Choose...'),
            'close' => __('Close')
        );
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
        $element   = $this->getElement();
        /* @var $fieldset \Magento\Data\Form\Element\Fieldset */
        $fieldset  = $element->getForm()->getElement($this->getFieldsetId());
        $chooserId = $this->getUniqId();
        $config    = $this->getConfig();

        // add chooser element to fieldset
        $chooser = $fieldset->addField('chooser' . $element->getId(), 'note', array(
            'label'       => $config->getLabel() ? $config->getLabel() : '',
            'value_class' => 'value2',
        ));
        $hiddenHtml = '';
        if ($this->getHiddenEnabled()) {
            $hidden = $this->_elementFactory->create('hidden', array('attributes' => $element->getData()));
            $hidden->setId("{$chooserId}value")->setForm($element->getForm());
            if ($element->getRequired()) {
                $hidden->addClass('required-entry');
            }
            $hiddenHtml = $hidden->getElementHtml();
            $element->setValue('');
        }

        $buttons = $config->getButtons();
        $chooseButton = $this->getLayout()->createBlock('Magento\Adminhtml\Block\Widget\Button')
            ->setType('button')
            ->setId($chooserId . 'control')
            ->setClass('btn-chooser')
            ->setLabel($buttons['open'])
            ->setOnclick($chooserId.'.choose()')
            ->setDisabled($element->getReadonly());
        $chooser->setData('after_element_html', $hiddenHtml . $chooseButton->toHtml());

        // render label and chooser scripts
        $configJson = $this->_coreData->jsonEncode($config->getData());
        return '
            <label class="widget-option-label" id="' . $chooserId . 'label">'
            . ($this->getLabel() ? $this->getLabel() : __('Not Selected')) . '</label>
            <div id="' . $chooserId . 'advice-container" class="hidden"></div>
            <script type="text/javascript">//<![CDATA[
                (function() {
                    var instantiateChooser = function() {
                        window.' . $chooserId . ' = new WysiwygWidget.chooser(
                            "' . $chooserId . '",
                            "' . $this->getSourceUrl() . '",
                            ' . $configJson . '
                        );
                        if ($("' . $chooserId . 'value")) {
                            $("' . $chooserId . 'value").advaiceContainer = "' . $chooserId . 'advice-container";
                        }
                    }

                    if (document.loaded) { //allow load over ajax
                        instantiateChooser();
                    } else {
                        document.observe("dom:loaded", instantiateChooser);
                    }
                })();
            //]]></script>
        ';
    }
}
