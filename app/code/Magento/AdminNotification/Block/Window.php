<?php
/**
 * Critical notification window
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Block;

use Magento\AdminNotification\Model\Inbox;
use Magento\AdminNotification\Model\ResourceModel\Inbox\Collection\Critical;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Auth\Session;

/**
 * Admin notification window block
 *
 * @package Magento\AdminNotification\Block
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Window extends Template
{
    /**
     * XML path of Severity icons url
     */
    const XML_SEVERITY_ICONS_URL_PATH = 'system/adminnotification/severity_icons_url';

    /**
     * Authentication
     *
     * @var Session
     */
    protected $_authSession; //phpcs:ignore

    /**
     * Critical messages collection
     *
     * @var Critical
     */
    protected $_criticalCollection; //phpcs:ignore

    /**
     * @var Inbox|false
     */
    protected $_latestItem; //phpcs:ignore

    /**
     * The property is used to define content-scope of block. Can be private or public.
     * If it isn't defined then application considers it as false.
     *
     * @var bool
     */
    protected $_isScopePrivate; //phpcs:ignore

    /**
     * @param Context $context
     * @param Session $authSession
     * @param Critical $criticalCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $authSession,
        Critical $criticalCollection,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_authSession = $authSession;
        $this->_criticalCollection = $criticalCollection;
        $this->_isScopePrivate = true;
    }

    /**
     * Render block
     *
     * @return string
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _toHtml(): string //phpcs:ignore
    {
        if ($this->canShow()) {
            $latestItem = $this->_getLatestItem();
            if ($latestItem instanceof Inbox) {
                $this->setHeaderText($this->escapeHtml(__('Incoming Message')));
                $this->setCloseText($this->escapeHtml(__('close')));
                $this->setReadDetailsText($this->escapeHtml(__('Read Details')));
                $this->setNoticeMessageText($this->escapeHtml($latestItem->getTitle()));
                $this->setNoticeMessageUrl($this->escapeUrl($latestItem->getUrl()));
                $this->setSeverityText('critical');
                return parent::_toHtml();
            }
        }
        return '';
    }

    /**
     * Retrieve latest critical item
     *
     * @return Inbox|false
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _getLatestItem() //phpcs:ignore
    {
        if ($this->_latestItem == null) {
            $items = array_values($this->_criticalCollection->getItems());
            $this->_latestItem = false;
            if (!empty($items)) {
                $this->_latestItem = $items[0];
            }
        }
        return $this->_latestItem;
    }

    /**
     * Check whether block should be displayed
     *
     * @return bool
     */
    public function canShow(): bool
    {
        return $this->_authSession->isFirstPageAfterLogin() && $this->_getLatestItem();
    }
}
