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
 * Extension edit page
 *
 * @category    Mage
 * @package     Mage_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Connect_Block_Adminhtml_Extension_Custom_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Constructor
     *
     * Initializes edit form container, adds necessary buttons
     */
    protected function _construct()
    {
        $this->_objectId    = 'id';
        $this->_blockGroup  = 'Mage_Connect';
        $this->_controller  = 'adminhtml_extension_custom';

        parent::_construct();

        $this->_removeButton('back');
        $this->_updateButton('reset', 'onclick', "resetPackage()");

        $this->_addButton('create', array(
            'label'     => Mage::helper('Mage_Connect_Helper_Data')->__('Save Data and Create Package'),
            'class'     => 'save',
            'onclick'   => "createPackage()",
            'data_attr'  => array(
                'widget-button' => array('event' => 'save', 'related' => '#edit_form'),
            ),
        ));
        $this->_addButton('save_as', array(
            'label'     => Mage::helper('Mage_Connect_Helper_Data')->__('Save As...'),
            'title'     => Mage::helper('Mage_Connect_Helper_Data')->__('Save package with custom package file name'),
            'onclick'   => 'saveAsPackage(event)',
            'data_attr'  => array(
                'widget-button' => array('event' => 'save', 'related' => '#edit_form'),
            ),
        ));
    }

    /**
    * Get header of page
    *
    * @return string
    */
    public function getHeaderText()
    {
        return Mage::helper('Mage_Connect_Helper_Data')->__('New Extension');
    }

    /*
     * Get form submit URL
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('*/*/save');
    }
}
