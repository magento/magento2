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
 * @category    Magento
 * @package     Magento_AdminNotification
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\AdminNotification\Helper;

/**
 * AdminNotification Data helper
 *
 * @category   Magento
 * @package    Magento_AdminNotification
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Data extends \Magento\App\Helper\AbstractHelper
{
    const XML_PATH_POPUP_URL = 'system/adminnotification/popup_url';

    /**
     * Widget Popup Notification Object URL
     *
     * @var string
     */
    protected $_popupUrl;

    /**
     * Is readable Popup Notification Object flag
     *
     * @var bool
     */
    protected $_popupReadable;

    /**
     * Last Notice object
     *
     * @var \Magento\AdminNotification\Model\Inbox
     */
    protected $_latestNotice;

    /**
     * Count of unread notes by type
     *
     * @var array
     */
    protected $_unreadNoticeCounts;

    /**
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @var \Magento\AdminNotification\Model\InboxFactory
     */
    protected $_inboxFactory;

    /**
     * @param \Magento\App\Helper\Context $context
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\AdminNotification\Model\InboxFactory $inboxFactory
     */
    public function __construct(
        \Magento\App\Helper\Context $context,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\AdminNotification\Model\InboxFactory $inboxFactory
    ) {
        parent::__construct($context);
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_inboxFactory = $inboxFactory;
    }

    /**
     * Retrieve latest notice model
     *
     * @return \Magento\AdminNotification\Model\Inbox
     */
    public function getLatestNotice()
    {
        if (is_null($this->_latestNotice)) {
            $this->_latestNotice = $this->_inboxFactory->create()->loadLatestNotice();
        }
        return $this->_latestNotice;
    }

    /**
     * Retrieve count of unread notes by type
     *
     * @param int $severity
     * @return int
     */
    public function getUnreadNoticeCount($severity)
    {
        if (is_null($this->_unreadNoticeCounts)) {
            $this->_unreadNoticeCounts = $this->_inboxFactory->create()->getNoticeStatus();
        }
        return isset($this->_unreadNoticeCounts[$severity]) ? $this->_unreadNoticeCounts[$severity] : 0;
    }
}
