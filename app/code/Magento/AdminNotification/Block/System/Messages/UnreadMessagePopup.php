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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\AdminNotification\Block\System\Messages;

use Magento\Framework\Notification\MessageInterface;

class UnreadMessagePopup extends \Magento\Backend\Block\Template
{
    /**
     * List of item classes per severity
     *
     * @var array
     */
    protected $_itemClasses = array(
        MessageInterface::SEVERITY_CRITICAL => 'error',
        MessageInterface::SEVERITY_MAJOR => 'warning'
    );

    /**
     * System Message list
     *
     * @var \Magento\AdminNotification\Model\Resource\System\Message\Collection
     */
    protected $_messages;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\AdminNotification\Model\Resource\System\Message\Collection\Synchronized $messages
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\AdminNotification\Model\Resource\System\Message\Collection\Synchronized $messages,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_messages = $messages;
    }

    /**
     * Render block
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (count($this->_messages->getUnread())) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Retrieve list of unread messages
     *
     * @return MessageInterface[]
     */
    public function getUnreadMessages()
    {
        return $this->_messages->getUnread();
    }

    /**
     * Retrieve popup title
     *
     * @return string
     */
    public function getPopupTitle()
    {
        $messageCount = count($this->_messages->getUnread());
        if ($messageCount > 1) {
            return __('You have %1 new system messages', $messageCount);
        } else {
            return __('You have %1 new system message', $messageCount);
        }
    }

    /**
     * Retrieve item class by severity
     *
     * @param MessageInterface $message
     * @return string
     */
    public function getItemClass(MessageInterface $message)
    {
        return $this->_itemClasses[$message->getSeverity()];
    }
}
