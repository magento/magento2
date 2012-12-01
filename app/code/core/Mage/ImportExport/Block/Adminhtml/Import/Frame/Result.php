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
 * @package     Mage_ImportExport
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Import frame result block.
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_ImportExport_Block_Adminhtml_Import_Frame_Result extends Mage_Adminhtml_Block_Template
{
    /**
     * JavaScript actions for response.
     *
     * @var array
     */
    protected $_actions = array(
        'clear'           => array(), // remove element from DOM
        'innerHTML'       => array(), // set innerHTML property (use: elementID => new content)
        'value'           => array(), // set value for form element (use: elementID => new value)
        'show'            => array(), // show specified element
        'hide'            => array(), // hide specified element
        'removeClassName' => array(), // remove specified class name from element
        'addClassName'    => array()  // add specified class name to element
    );

    /**
     * Validation messages.
     *
     * @var array
     */
    protected $_messages = array(
        'error'   => array(),
        'success' => array(),
        'notice'  => array()
    );

    /**
     * Add action for response.
     *
     * @param string $actionName
     * @param string $elementId
     * @param mixed $value OPTIONAL
     * @return Mage_ImportExport_Block_Adminhtml_Import_Frame_Result
     */
    public function addAction($actionName, $elementId, $value = null)
    {
        if (isset($this->_actions[$actionName])) {
            if (null === $value) {
                if (is_array($elementId)) {
                    foreach ($elementId as $oneId) {
                        $this->_actions[$actionName][] = $oneId;
                    }
                } else {
                    $this->_actions[$actionName][] = $elementId;
                }
            } else {
                $this->_actions[$actionName][$elementId] = $value;
            }
        }
        return $this;
    }

    /**
     * Add error message.
     *
     * @param string $message Error message
     * @return Mage_ImportExport_Block_Adminhtml_Import_Frame_Result
     */
    public function addError($message)
    {
        if (is_array($message)) {
            foreach ($message as $row) {
                $this->addError($row);
            }
        } else {
            $this->_messages['error'][] = $message;
        }
        return $this;
    }

    /**
     * Add notice message.
     *
     * @param mixed $message Message text
     * @param boolean $appendImportButton OPTIONAL Append import button to message?
     * @return Mage_ImportExport_Block_Adminhtml_Import_Frame_Result
     */
    public function addNotice($message, $appendImportButton = false)
    {
        if (is_array($message)) {
            foreach ($message as $row) {
                $this->addNotice($row);
            }
        } else {
            $this->_messages['notice'][] = $message . ($appendImportButton ? $this->getImportButtonHtml() : '');
        }
        return $this;
    }

    /**
     * Add success message.
     *
     * @param mixed $message Message text
     * @param boolean $appendImportButton OPTIONAL Append import button to message?
     * @return Mage_ImportExport_Block_Adminhtml_Import_Frame_Result
     */
    public function addSuccess($message, $appendImportButton = false)
    {
        if (is_array($message)) {
            foreach ($message as $row) {
                $this->addSuccess($row);
            }
        } else {
            $this->_messages['success'][] = $message . ($appendImportButton ? $this->getImportButtonHtml() : '');
        }
        return $this;
    }

    /**
     * Import button HTML for append to message.
     *
     * @return string
     */
    public function getImportButtonHtml()
    {
        return '&nbsp;&nbsp;<button onclick="varienImport.startImport(\'' . $this->getImportStartUrl()
            . '\', \'' . Mage_ImportExport_Model_Import::FIELD_NAME_SOURCE_FILE . '\');" class="scalable save"'
            . ' type="button"><span><span><span>' . $this->__('Import') . '</span></span></span></button>';
    }

    /**
     * Import start action URL.
     *
     * @return string
     */
    public function getImportStartUrl()
    {
        return $this->getUrl('*/*/start');
    }

    /**
     * Messages getter.
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }

    /**
     * Messages rendered HTML getter.
     *
     * @return string
     */
    public function getMessagesHtml()
    {
        /** @var $messagesBlock Mage_Core_Block_Messages */
        $messagesBlock = $this->_layout->createBlock('Mage_Core_Block_Messages');

        foreach ($this->_messages as $priority => $messages) {
            $method = "add{$priority}";

            foreach ($messages as $message) {
                $messagesBlock->$method($message);
            }
        }
        return $messagesBlock->toHtml();
    }

    /**
     * Return response as JSON.
     *
     * @return string
     */
    public function getResponseJson()
    {
        // add messages HTML if it is not already specified
        if (!isset($this->_actions['import_validation_messages'])) {
            $this->addAction('innerHTML', 'import_validation_messages', $this->getMessagesHtml());
        }
        return Mage::helper('Mage_Core_Helper_Data')->jsonEncode($this->_actions);
    }
}
