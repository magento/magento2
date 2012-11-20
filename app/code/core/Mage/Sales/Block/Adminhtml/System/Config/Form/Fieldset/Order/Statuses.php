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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Sales_Block_Adminhtml_System_Config_Form_Fieldset_Order_Statuses
    extends Mage_Backend_Block_System_Config_Form_Fieldset
{
    /**
     * @var Varien_Object
     */
    protected $_dummyElement;

    /**
     * @var Mage_Backend_Block_System_Config_Form_Field
     */
    protected $_fieldRenderer;

    /**
     * @var array
     */
    protected $_values;

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '';

        $statuses = Mage::getResourceModel('Mage_Sales_Model_Resource_Order_Status_Collection')->load()->toOptionHash();

        foreach ($statuses as $id => $status) {
            $html.= $this->_getFieldHtml($element, $id, $status);
        }
        return $html;
    }

    /**
     * @return Varien_Object
     */
    protected function _getDummyElement()
    {
        if (empty($this->_dummyElement)) {
            $this->_dummyElement = new Varien_Object(array('showInDefault' => 1, 'showInWebsite' => 1));
        }
        return $this->_dummyElement;
    }

    /**
     * @return Mage_Backend_Block_System_Config_Form_Field
     */
    protected function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = Mage::getBlockSingleton('Mage_Backend_Block_System_Config_Form_Field');
        }
        return $this->_fieldRenderer;
    }

    /**
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param string $id
     * @param string $status
     * @return string
     */
    protected function _getFieldHtml($fieldset, $id, $status)
    {
        $configData = $this->getConfigData();
        $path = 'sales/order_statuses/status_'.$id; //TODO: move as property of form
        $data = isset($configData[$path]) ? $configData[$path] : array();

        $e = $this->_getDummyElement();

        $field = $fieldset->addField($id, 'text',
            array(
                'name'          => 'groups[order_statuses][fields][status_'.$id.'][value]',
                'label'         => $status,
                'value'         => isset($data['value']) ? $data['value'] : $status,
                'default_value' => isset($data['default_value']) ? $data['default_value'] : '',
                'old_value'     => isset($data['old_value']) ? $data['old_value'] : '',
                'inherit'       => isset($data['inherit']) ? $data['inherit'] : '',
                'can_use_default_value' => $this->getForm()->canUseDefaultValue($e),
                'can_use_website_value' => $this->getForm()->canUseWebsiteValue($e),
            ))->setRenderer($this->_getFieldRenderer());

        return $field->toHtml();
    }
}
