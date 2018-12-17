<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Block\System\Messages;

use Magento\AdminNotification\Model\ResourceModel\System\Message\Collection\Synchronized;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\Phrase;

/**
 * Class UnreadMessagePopup
 *
 * @package Magento\AdminNotification\Block\System\Messages
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class UnreadMessagePopup extends Template
{
    /**
     * List of item classes per severity
     *
     * @var array
     */
    protected $_itemClasses = [ //phpcs:ignore
        MessageInterface::SEVERITY_CRITICAL => 'error',
        MessageInterface::SEVERITY_MAJOR => 'warning',
    ];

    /**
     * System Message list
     *
     * @var \Magento\AdminNotification\Model\ResourceModel\System\Message\Collection
     */
    protected $_messages; //phpcs:ignore

    /**
     * @param Context $context
     * @param Synchronized $messages
     * @param array $data
     */
    public function __construct(
        Context $context,
        Synchronized $messages,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_messages = $messages;
    }

    /**
     * Render block
     *
     * @return string
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _toHtml(): string //phpcs:ignore
    {
        if (!empty($this->_messages->getUnread())) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Retrieve list of unread messages
     *
     * @return MessageInterface[]
     */
    public function getUnreadMessages(): array
    {
        return $this->_messages->getUnread();
    }

    /**
     * Retrieve popup title
     *
     * @return Phrase
     */
    public function getPopupTitle(): Phrase
    {
        $messageCount = count($this->_messages->getUnread());
        if ($messageCount > 1) {
            return __('You have %1 new system messages', $messageCount);
        }
        return __('You have %1 new system message', $messageCount);
    }

    /**
     * Retrieve item class by severity
     *
     * @param MessageInterface $message
     * @return string
     */
    public function getItemClass(MessageInterface $message): string
    {
        return $this->_itemClasses[$message->getSeverity()];
    }
}
