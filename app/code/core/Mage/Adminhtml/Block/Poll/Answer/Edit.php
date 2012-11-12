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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Admin poll answer edit block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Block_Poll_Answer_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    protected function _construct()
    {
        parent::_construct();

        $this->_objectId = 'id';
        $this->_controller = 'poll_answer';
        $answerData = Mage::getModel('Mage_Poll_Model_Poll_Answer');
        if( $this->getRequest()->getParam($this->_objectId) ) {
            $answerData = Mage::getModel('Mage_Poll_Model_Poll_Answer')
                ->load($this->getRequest()->getParam($this->_objectId));
            Mage::register('answer_data', $answerData);
        }

        $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('*/poll/edit', array('id' => $answerData->getPollId(), 'tab' => 'answers_section')) . '\');');
        $this->_updateButton('save', 'label', Mage::helper('Mage_Poll_Helper_Data')->__('Save Answer'));
        $this->_updateButton('delete', 'label', Mage::helper('Mage_Poll_Helper_Data')->__('Delete Answer'));
    }

    public function getHeaderText()
    {
        return Mage::helper('Mage_Poll_Helper_Data')->__("Edit Answer '%s'", $this->escapeHtml(Mage::registry('answer_data')->getAnswerTitle()));
    }

}
