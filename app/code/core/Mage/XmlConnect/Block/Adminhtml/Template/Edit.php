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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Xmlconnect template edit block
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Adminhtml_Template_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_objectId    = 'id';
        $this->_controller  = 'adminhtml_template';
        $this->_blockGroup  = 'Mage_XmlConnect';
        parent::_construct();

        $this->_updateButton('delete', 'onclick', 'deleteConfirm(\''
            . $this->__('Warning: All related AirMail messages will be deleted!') . PHP_EOL
            . $this->__('Are you sure you want to do this?') .'\', \'' . $this->getDeleteUrl() . '\')'
        );
        $this->_updateButton('save', 'label', $this->__('Save'));
        $this->_updateButton('save', 'onclick', 'if (editForm.submit()) {disableElements(\'save\')}');
        $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('*/*/template') . '\')');
    }

    /**
     * Return delete url for customer group
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/deletetemplate', array($this->_objectId => $this->getRequest()->getParam($this->_objectId)));
    }

    /**
     * Get text for header
     *
     * @return string
     */
    public function getHeaderText()
    {
        $template = Mage::registry('current_template');
        if ($template && $template->getId()) {
            return $this->__('Edit Template "%s"', $this->escapeHtml($template->getName()));
        } else {
            return $this->__('New Template');
        }
    }
}
