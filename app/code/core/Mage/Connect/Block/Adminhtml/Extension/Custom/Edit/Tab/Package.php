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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class block for package
 *
 * @category    Mage
 * @package     Mage_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Connect_Block_Adminhtml_Extension_Custom_Edit_Tab_Package
    extends Mage_Connect_Block_Adminhtml_Extension_Custom_Edit_Tab_Abstract
{
    /**
     * Prepare Package Info Form before rendering HTML
     *
     * @return Mage_Connect_Block_Adminhtml_Extension_Custom_Edit_Tab_Package
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('_package');

        $fieldset = $form->addFieldset('package_fieldset', array(
            'legend'    => Mage::helper('Mage_Connect_Helper_Data')->__('Package')
        ));

        if ($this->getData('name') != $this->getData('file_name')) {
            $this->setData('file_name_disabled', $this->getData('file_name'));
            $fieldset->addField('file_name_disabled', 'text', array(
                'name'      => 'file_name_disabled',
                'label'     => Mage::helper('Mage_Connect_Helper_Data')->__('Package File Name'),
                'disabled'  => 'disabled',
            ));
        }

        $fieldset->addField('file_name', 'hidden', array(
            'name'      => 'file_name',
        ));

        $fieldset->addField('name', 'text', array(
            'name'      => 'name',
            'label'     => Mage::helper('Mage_Connect_Helper_Data')->__('Name'),
            'required'  => true,
        ));

        $fieldset->addField('channel', 'text', array(
            'name'      => 'channel',
            'label'     => Mage::helper('Mage_Connect_Helper_Data')->__('Channel'),
            'required'  => true,
        ));

        $versionsInfo = array(
            array(
                'label' => Mage::helper('Mage_Connect_Helper_Data')->__('1.5.0.0 & later'),
                'value' => Mage_Connect_Package::PACKAGE_VERSION_2X
            ),
            array(
                'label' => Mage::helper('Mage_Connect_Helper_Data')->__('Pre-1.5.0.0'),
                'value' => Mage_Connect_Package::PACKAGE_VERSION_1X
            )
        );
        $fieldset->addField('version_ids','multiselect',array(
                'name'     => 'version_ids',
                'required' => true,
                'label'    => Mage::helper('Mage_Connect_Helper_Data')->__('Supported releases'),
                'style'    => 'height: 45px;',
                'values'   => $versionsInfo
        ));

        $fieldset->addField('summary', 'textarea', array(
            'name'      => 'summary',
            'label'     => Mage::helper('Mage_Connect_Helper_Data')->__('Summary'),
            'style'     => 'height:50px;',
            'required'  => true,
        ));

        $fieldset->addField('description', 'textarea', array(
            'name'      => 'description',
            'label'     => Mage::helper('Mage_Connect_Helper_Data')->__('Description'),
            'style'     => 'height:200px;',
            'required'  => true,
        ));

        $fieldset->addField('license', 'text', array(
            'name'      => 'license',
            'label'     => Mage::helper('Mage_Connect_Helper_Data')->__('License'),
            'required'  => true,
            'value'     => 'Open Software License (OSL 3.0)',
        ));

        $fieldset->addField('license_uri', 'text', array(
            'name'      => 'license_uri',
            'label'     => Mage::helper('Mage_Connect_Helper_Data')->__('License URI'),
            'value'     => 'http://opensource.org/licenses/osl-3.0.php',
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
        return Mage::helper('Mage_Connect_Helper_Data')->__('Package Info');
    }

    /**
     * Get Tab Title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('Mage_Connect_Helper_Data')->__('Package Info');
    }
}
