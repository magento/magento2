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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Store / store view / website delete form container
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_System_Store_Delete extends Mage_Adminhtml_Block_Widget_Form_Container
{

    /**
     * Class constructor
     *
     */
    public function __construct()
    {
        $this->_objectId = 'item_id';
        $this->_mode = 'delete';
        $this->_controller = 'system_store';

        parent::__construct();

        $this->_removeButton('save');
        $this->_removeButton('reset');

        $this->_updateButton('delete', 'area', 'footer');
        $this->_updateButton('delete', 'onclick', 'editForm.submit();');

        $this->_addButton('cancel', array(
            'label'     => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Cancel'),
            'onclick'   => 'setLocation(\'' . $this->getBackUrl() . '\')',
        ), 2, 100, 'footer');

    }

    /**
     * Get edit form container header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return Mage::helper('Mage_Adminhtml_Helper_Data')->__("Delete %s '%s'", $this->getStoreTypeTitle(),
            $this->escapeHtml($this->getChild('form')->getDataObject()->getName()));
    }

    /**
     * Set store type title
     *
     * @param string $title
     * @return Mage_Adminhtml_Block_System_Store_Delete
     */
    public function setStoreTypeTitle($title)
    {
        $this->_updateButton('delete', 'label', Mage::helper('Mage_Adminhtml_Helper_Data')->__('Delete %s', $title));
        return $this->setData('store_type_title', $title);
    }

    /**
     * Set back URL for "Cancel" and "Back" buttons
     *
     * @param string $url
     * @return Mage_Adminhtml_Block_System_Store_Delete
     */
    public function setBackUrl($url)
    {
        $this->setData('back_url', $url);
        $this->_updateButton('cancel', 'onclick', "setLocation('" . $url . "')");
        $this->_updateButton('back', 'onclick', "setLocation('" . $url . "')");
        return $this;
    }

}
