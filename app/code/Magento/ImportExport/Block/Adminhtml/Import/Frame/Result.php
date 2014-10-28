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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\ImportExport\Block\Adminhtml\Import\Frame;

use Magento\Framework\View\Element\Template;

/**
 * Import frame result block.
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Result extends \Magento\Backend\Block\Template
{
    /**
     * JavaScript actions for response.
     *     'clear'           remove element from DOM
     *     'innerHTML'       set innerHTML property (use: elementID => new content)
     *     'value'           set value for form element (use: elementID => new value)
     *     'show'            show specified element
     *     'hide'            hide specified element
     *     'removeClassName' remove specified class name from element
     *     'addClassName'    add specified class name to element
     *
     * @var array
     */
    protected $_actions = array(
        'clear' => array(),
        'innerHTML' => array(),
        'value' => array(),
        'show' => array(),
        'hide' => array(),
        'removeClassName' => array(),
        'addClassName' => array()
    );

    /**
     * Validation messages.
     *
     * @var array
     */
    protected $_messages = array('error' => array(), 'success' => array(), 'notice' => array());

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = array()
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        parent::__construct($context, $data);
    }

    /**
     * Add action for response.
     *
     * @param string $actionName
     * @param string $elementId
     * @param mixed $value OPTIONAL
     * @return $this
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
     * @return $this
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
     * @param string[]|string $message Message text
     * @param bool $appendImportButton OPTIONAL Append import button to message?
     * @return $this
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
     * @param string[]|string $message Message text
     * @param bool $appendImportButton OPTIONAL Append import button to message?
     * @return $this
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
        return '&nbsp;&nbsp;<button onclick="varienImport.startImport(\'' .
            $this->getImportStartUrl() .
            '\', \'' .
            \Magento\ImportExport\Model\Import::FIELD_NAME_SOURCE_FILE .
            '\');" class="scalable save"' .
            ' type="button"><span><span><span>' .
            __(
                'Import'
            ) . '</span></span></span></button>';
    }

    /**
     * Import start action URL.
     *
     * @return string
     */
    public function getImportStartUrl()
    {
        return $this->getUrl('adminhtml/*/start');
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
        /** @var $messagesBlock \Magento\Framework\View\Element\Messages */
        $messagesBlock = $this->_layout->createBlock('Magento\Framework\View\Element\Messages');

        foreach ($this->_messages as $priority => $messages) {
            $method = "add{$priority}";

            foreach ($messages as $message) {
                $messagesBlock->{$method}($message);
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
        return $this->_jsonEncoder->encode($this->_actions);
    }
}
