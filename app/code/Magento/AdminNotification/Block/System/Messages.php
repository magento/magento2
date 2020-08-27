<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Block\System;

use Magento\AdminNotification\Model\ResourceModel\System\Message\Collection\Synchronized;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context as TemplateContext;
use Magento\Framework\Json\Helper\Data as JsonDataHelper;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

/**
 * AdminNotification Messages class
 */
class Messages extends Template
{
    /**
     * Synchronized Message collection
     *
     * @var Synchronized
     */
    protected $_messages;

    /**
     * @var JsonDataHelper
     * @deprecated 100.3.0
     */
    protected $jsonHelper;

    /**
     * @var JsonSerializer
     */
    private $serializer;

    /**
     * @param TemplateContext $context
     * @param Synchronized $messages
     * @param JsonDataHelper $jsonHelper
     * @param JsonSerializer $serializer
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        Synchronized $messages,
        JsonDataHelper $jsonHelper,
        JsonSerializer $serializer,
        array $data = []
    ) {
        $this->jsonHelper = $jsonHelper;
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
     * @return MessageInterface[]|null
     */
    public function getLastCritical()
    {
        $items = array_values($this->_messages->getItems());

        if (!empty($items) && current($items)->getSeverity() === MessageInterface::SEVERITY_CRITICAL) {
            return current($items);
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
        return $this->_messages->getCountBySeverity(MessageInterface::SEVERITY_CRITICAL);
    }

    /**
     * Retrieve number of major messages
     *
     * @return int
     */
    public function getMajorCount()
    {
        return $this->_messages->getCountBySeverity(MessageInterface::SEVERITY_MAJOR);
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
