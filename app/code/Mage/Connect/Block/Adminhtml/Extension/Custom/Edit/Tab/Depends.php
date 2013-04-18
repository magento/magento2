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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Block for Dependencies
 *
 * @category    Mage
 * @package     Mage_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Connect_Block_Adminhtml_Extension_Custom_Edit_Tab_Depends
    extends Mage_Connect_Block_Adminhtml_Extension_Custom_Edit_Tab_Abstract
{

    /**
     * Prepare Dependencies Form before rendering HTML
     *
     * @return Mage_Connect_Block_Adminhtml_Extension_Custom_Edit_Tab_Package
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('_depends');

        $fieldset = $form->addFieldset('depends_php_fieldset', array(
            'legend'    => Mage::helper('Mage_Connect_Helper_Data')->__('PHP Version')
        ));

        $fieldset->addField('depends_php_min', 'text', array(
            'name'      => 'depends_php_min',
            'label'     => Mage::helper('Mage_Connect_Helper_Data')->__('Minimum'),
            'required'  => true,
            'value'     => '5.2.0',
        ));

        $fieldset->addField('depends_php_max', 'text', array(
            'name'      => 'depends_php_max',
            'label'     => Mage::helper('Mage_Connect_Helper_Data')->__('Maximum'),
            'required'  => true,
            'value'     => '5.2.20',
        ));

        $form->setValues($this->getData());
        $this->setForm($form);

        return $this;
    }

    /**
     * Retrieve list of loaded PHP extensions
     *
     * @return array
     */
    public function getExtensions()
    {
        $extensions = array();
        foreach (get_loaded_extensions() as $ext) {
            $extensions[$ext] = $ext;
        }
        asort($extensions, SORT_STRING);
        return $extensions;
    }

    /**
     * Get Tab Label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('Mage_Connect_Helper_Data')->__('Dependencies');
    }

    /**
     * Get Tab Title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('Mage_Connect_Helper_Data')->__('Dependencies');
    }
}
