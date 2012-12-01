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
 * @package     Mage_Connect
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Block for release info
 *
 * @category    Mage
 * @package     Mage_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Connect_Block_Adminhtml_Extension_Custom_Edit_Tab_Release
    extends Mage_Connect_Block_Adminhtml_Extension_Custom_Edit_Tab_Abstract
{
    /**
     * Prepare Release Info Form before rendering HTML
     *
     * @return Mage_Connect_Block_Adminhtml_Extension_Custom_Edit_Tab_Release
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('_release');

        $fieldset = $form->addFieldset('release_fieldset', array(
            'legend'    => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Release')
        ));

        $stabilityOptions = Mage::getModel('Mage_Connect_Model_Extension')->getStabilityOptions();
        $fieldset->addField('version', 'text', array(
            'name'      => 'version',
            'label'     => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Release Version'),
            'required'  => true,
        ));

        $fieldset->addField('stability', 'select', array(
            'name'      => 'stability',
            'label'     => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Release Stability'),
            'options'   => $stabilityOptions,
        ));

        $fieldset->addField('notes', 'textarea', array(
            'name'      => 'notes',
            'label'     => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Notes'),
            'style'     => 'height:300px;',
            'required'  => true,
        ));

        $form->setValues($this->getData());
        $this->setForm($form);

        return $this;
    }

    /**
     * Get Tab Label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('Mage_Connect_Helper_Data')->__('Release Info');
    }

    /**
     * Get Tab Title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('Mage_Connect_Helper_Data')->__('Release Info');
    }
}
