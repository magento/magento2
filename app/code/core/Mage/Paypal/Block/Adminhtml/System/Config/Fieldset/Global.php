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
 * @category    Mage
 * @package     Mage_Paypal
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Fieldset renderer for PayPal global settings
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Paypal_Block_Adminhtml_System_Config_Fieldset_Global
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Associative array of PayPal product selection elements
     *
     * @var array
     */
    protected $_elements = array();

    /**
     * Custom template
     *
     * @var string
     */
    protected $_template = 'Mage_Paypal::system/config/fieldset/global.phtml';

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $fieldset
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $fieldset)
    {
        foreach ($fieldset->getSortedElements() as $element) {
            $htmlId = $element->getHtmlId();
            $this->_elements[$htmlId] = $element;
        }
        $originalData = $fieldset->getOriginalData();
        $this->addData(array(
            'fieldset_label' => $fieldset->getLegend(),
            'fieldset_help_url' => isset($originalData['help_url']) ? $originalData['help_url'] : '',
        ));
        return $this->toHtml();
    }

    /**
     * Get array of element objects
     *
     * @return array
     */
    public function getElements()
    {
        return $this->_elements;
    }

    /**
     * Get element by id
     *
     * @param string $elementId
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getElement($elementId)
    {
        if (isset($this->_elements[$elementId])) {
            return $this->_elements[$elementId];
        }
        return false;
    }

    /**
     * Return checkbox html with hidden field for correct config values
     *
     * @param string $elementId
     * @return string
     */
    public function getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $configValue = (string)$element->getValue();
        if ($configValue) {
            $element->setChecked(true);
        } else {
            $element->setValue('1');
        }
        if ($element->getCanUseDefaultValue() && $element->getInherit()) {
            $element->setDisabled(true);
        }

        $hidden = new Varien_Data_Form_Element_Hidden(array(
            'html_id' => $element->getHtmlId() . '_value',
            'name' => $element->getName(),
            'value' => '0'
        ));
        $hidden->setForm($element->getForm());
        return $hidden->getElementHtml() . $element->getElementHtml();
    }

    /**
     * Whether element should be rendered in "simplified" mode
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return bool
     */
    public function getIsElementSimplified(Varien_Data_Form_Element_Abstract $element)
    {
        $originalData = $element->getOriginalData();
        return isset($originalData['is_simplified']) && 1 == $originalData['is_simplified'];
    }

    /**
     * Getter for element label
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function getElementLabel(Varien_Data_Form_Element_Abstract $element)
    {
        return $element->getLabel();
    }

    /**
     * Getter for element comment
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function getElementComment(Varien_Data_Form_Element_Abstract $element)
    {
        return $element->getComment();
    }

    /**
     * Getter for element comment
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function getElementOriginalData(Varien_Data_Form_Element_Abstract $element, $key)
    {
        $data = $element->getOriginalData();
        return isset($data[$key]) ? $data[$key] : '';
    }

    /**
     * Check whether checkbox has "Use default" option or not
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return bool
     */
    public function hasInheritElement(Varien_Data_Form_Element_Abstract $element)
    {
        return (bool)$element->getCanUseDefaultValue();
    }

    /**
     * Return "Use default" checkbox html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function getInheritElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $elementId = $element->getHtmlId();
        $inheritCheckbox = new Varien_Data_Form_Element_Checkbox(array(
            'html_id' => $elementId . '_inherit',
            'name' => preg_replace('/\[value\](\[\])?$/', '[inherit]', $element->getName()),
            'value' => '1',
            'class' => 'checkbox config-inherit',
            'onclick' => 'toggleValueElements(this, $(\'' . $elementId . '\').up())'
        ));
        if ($element->getInherit()) {
            $inheritCheckbox->setChecked(true);
        }

        $inheritCheckbox->setForm($element->getForm());
        return $inheritCheckbox->getElementHtml();
    }

    /**
     * Return label for "Use default" checkbox
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function getInheritElementLabelHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return sprintf('<label for="%s" class="inherit" title="%s">%s</label>',
            $element->getHtmlId() . '_inherit',
            $element->getDefaultValue(),
            Mage::helper('Mage_Adminhtml_Helper_Data')->__('Use Default')
        );
    }

    /**
     * Return element label with tag SPAN
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function getElementLabelTextHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return sprintf('<span id="%s">%s</span>',
            $element->getHtmlId() . '_label_text',
            $this->escapeHtml($this->getElementLabel($element))
        );
    }

    /**
     * Return backend config for element like JSON
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function getElementBackendConfig(Varien_Data_Form_Element_Abstract $element)
    {
        return Mage::helper('Mage_Paypal_Helper_Data')->getElementBackendConfig($element);
    }
}
