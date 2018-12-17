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
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Messages
 *
 * @package Magento\AdminNotification\Block\System
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Messages extends Template
{
    /**
     * Message list
     *
     * @var Synchronized
     */
    protected $_messages; //phpcs:ignore

    /**
     * @var Data
     * @deprecated
     */
    protected $jsonHelper;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param Context $context
     * @param Synchronized $messages
     * @param Data $jsonHelper
     * @param array $data
     * @param Json|null $serializer
     */
    public function __construct(
        Context $context,
        Synchronized $messages,
        Data $jsonHelper,
        array $data = [],
        Json $serializer = null
    ) {
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
        $this->_messages = $messages;
        $this->serializer = $serializer ?: ObjectManager::getInstance()
            ->get(Json::class);
    }

    /**
     * Prepare html output
     *
     * @return string
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _toHtml(): string //phpcs:ignore
    {
        if (!empty($this->_messages->getItems())) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Retrieve message list
     *
     * @return MessageInterface|null
     */
    public function getLastCritical(): ?MessageInterface
    {
        $items = array_values($this->_messages->getItems());
        if (isset($items[0]) && $items[0]->getSeverity() == MessageInterface::SEVERITY_CRITICAL) {
            return $items[0];
        }
        return null;
    }

    /**
     * Retrieve number of critical messages
     *
     * @return int
     */
    public function getCriticalCount(): int
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
    public function getMajorCount(): int
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
    public function hasMessages(): bool
    {
        return (bool)count($this->_messages->getItems());
    }

    /**
     * Retrieve message list url
     *
     * @return string
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _getMessagesUrl(): string //phpcs:ignore
    {
        return $this->getUrl('adminhtml/system_message/list');
    }

    /**
     * Initialize system message dialog widget
     *
     * @return string
     */
    public function getSystemMessageDialogJson(): string
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
