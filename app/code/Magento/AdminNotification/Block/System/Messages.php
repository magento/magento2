<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Block\System;

class Messages extends \Magento\Backend\Block\Template
{
    /**
     * Message list
     *
     * @var \Magento\AdminNotification\Model\Resource\System\Message\Collection\Synchronized
     */
    protected $_messages;

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\AdminNotification\Model\Resource\System\Message\Collection\Synchronized $messages
     * @param \Magento\Core\Helper\Data $coreHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\AdminNotification\Model\Resource\System\Message\Collection\Synchronized $messages,
        \Magento\Core\Helper\Data $coreHelper,
        array $data = []
    ) {
        $this->_coreHelper = $coreHelper;
        parent::__construct($context, $data);
        $this->_messages = $messages;
    }

    /**
     * Prepare html output
     *
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
     * @return \Magento\Framework\Notification\MessageInterface[]
     */
    public function getLastCritical()
    {
        $items = array_values($this->_messages->getItems());
        if (isset(
            $items[0]
        ) && $items[0]->getSeverity() == \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL
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
            \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL
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
            \Magento\Framework\Notification\MessageInterface::SEVERITY_MAJOR
        );
    }

    /**
     * Check whether system messages are present
     *
     * @return bool
     */
    public function hasMessages()
    {
        return (bool)count($this->_messages->getItems());
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
     * Initialize system message dialog widget
     *
     * @return string
     */
    public function getSystemMessageDialogJson()
    {
        return $this->_coreHelper->jsonEncode(
            [
                'systemMessageDialog' => [
                    'autoOpen' => false,
                    'width' => 600,
                    'ajaxUrl' => $this->_getMessagesUrl(),
                ],
            ]
        );
    }
}
