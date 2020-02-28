<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminNotification\Block\System;

use Magento\AdminNotification\Model\ResourceModel\System\Message\Collection\Synchronized;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Admin System Messages Block
 */
class Messages extends Template
{
    /**
     * Message list
     *
     * @var Synchronized
     */
    protected $_messages;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param Context $context
     * @param Synchronized $messages
     * @param Json $serializer
     * @param array $data
     */
    public function __construct(
        Context $context,
        Synchronized $messages,
        Json $serializer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_messages = $messages;
        $this->serializer = $serializer;
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
     * @return MessageInterface[]
     */
    public function getLastCritical()
    {
        $items = array_values($this->_messages->getItems());
        if (isset($items[0]) && (int)$items[0]->getSeverity() === MessageInterface::SEVERITY_CRITICAL) {
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
            MessageInterface::SEVERITY_CRITICAL
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
            MessageInterface::SEVERITY_MAJOR
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
        return $this->serializer->serialize(
            [
                'systemMessageDialog' => [
                    'buttons' => [],
                    'modalClass' => 'ui-dialog-active ui-popup-message modal-system-messages',
                    'ajaxUrl' => $this->_getMessagesUrl()
                ],
            ]
        );
    }
}
