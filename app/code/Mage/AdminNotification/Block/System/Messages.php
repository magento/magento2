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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_AdminNotification_Block_System_Messages extends Mage_Backend_Block_Template
{
    /**
     * Message list
     *
     * @var Mage_AdminNotification_Model_Resource_System_Message_Collection_Synchronized
     */
    protected $_messages;

    /**
     * @param Mage_Backend_Block_Template_Context $context
     * @param Mage_AdminNotification_Model_Resource_System_Message_Collection_Synchronized $messages
     * @param array $data
     */
    public function __construct(
        Mage_Backend_Block_Template_Context $context,
        Mage_AdminNotification_Model_Resource_System_Message_Collection_Synchronized $messages,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_messages = $messages;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (count($this->_messages->getItems())) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Retrieve message list
     *
     * @return Mage_AdminNotification_Model_System_MessageInterface[]
     */
    public function getLastCritical()
    {
        $items = array_values($this->_messages->getItems());
        if (isset($items[0]) && $items[0]->getSeverity()
            == Mage_AdminNotification_Model_System_MessageInterface::SEVERITY_CRITICAL
        ) {
            return $items[0];
        }
        return null;
    }

    /**
     * Retrieve number of critical messages
     *
     * @return int
     */
    public function getCriticalCount()
    {
        return $this->_messages->getCountBySeverity(
            Mage_AdminNotification_Model_System_MessageInterface::SEVERITY_CRITICAL
        );
    }

    /**
     * Retrieve number of major messages
     *
     * @return int
     */
    public function getMajorCount()
    {
        return $this->_messages->getCountBySeverity(
            Mage_AdminNotification_Model_System_MessageInterface::SEVERITY_MAJOR
        );
    }

    /**
     * Check whether system messages are present
     *
     * @return bool
     */
    public function hasMessages()
    {
        return (bool) count($this->_messages->getItems());
    }

    /**
     * Retrieve message list url
     *
     * @return string
     */
    protected function _getMessagesUrl()
    {
        return $this->getUrl('adminhtml/system_message/list');
    }

    /**
     * Initialize Syste,Message dialog widget
     *
     * @return string
     */
    public function getSystemMessageDialogJson()
    {
        return $this->helper('Mage_Core_Helper_Data')->jsonEncode(array(
            'systemMessageDialog' => array(
                'autoOpen' => false,
                'width' => 600,
                'ajaxUrl' => $this->_getMessagesUrl(),
            ),
        ));
    }
}
