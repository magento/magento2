<?php
/**
 * Critical notification window
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Block;

/**
 * @api
 * @since 2.0.0
 */
class Window extends \Magento\Backend\Block\Template
{
    /**
     * XML path of Severity icons url
     */
    const XML_SEVERITY_ICONS_URL_PATH = 'system/adminnotification/severity_icons_url';

    /**
     * Severity icons url
     *
     * @var string
     * @since 2.0.0
     */
    protected $_severityIconsUrl;

    /**
     * Authentication
     *
     * @var \Magento\Backend\Model\Auth\Session
     * @since 2.0.0
     */
    protected $_authSession;

    /**
     * Critical messages collection
     *
     * @var \Magento\AdminNotification\Model\ResourceModel\Inbox\Collection
     * @since 2.0.0
     */
    protected $_criticalCollection;

    /**
     * @var \Magento\Adminnotification\Model\Inbox
     * @since 2.0.0
     */
    protected $_latestItem;

    /**
     * The property is used to define content-scope of block. Can be private or public.
     * If it isn't defined then application considers it as false.
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_isScopePrivate;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\AdminNotification\Model\ResourceModel\Inbox\Collection\Critical $criticalCollection
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\AdminNotification\Model\ResourceModel\Inbox\Collection\Critical $criticalCollection,
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
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        if ($this->canShow()) {
            $this->setHeaderText($this->escapeHtml(__('Incoming Message')));
            $this->setCloseText($this->escapeHtml(__('close')));
            $this->setReadDetailsText($this->escapeHtml(__('Read Details')));
            $this->setNoticeMessageText($this->escapeHtml($this->_getLatestItem()->getTitle()));
            $this->setNoticeMessageUrl($this->escapeUrl($this->_getLatestItem()->getUrl()));
            $this->setSeverityText('critical');
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Retrieve latest critical item
     *
     * @return bool|\Magento\Adminnotification\Model\Inbox
     * @since 2.0.0
     */
    protected function _getLatestItem()
    {
        if ($this->_latestItem == null) {
            $items = array_values($this->_criticalCollection->getItems());
            if (count($items)) {
                $this->_latestItem = $items[0];
            } else {
                $this->_latestItem = false;
            }
        }
        return $this->_latestItem;
    }

    /**
     * Check whether block should be displayed
     *
     * @return bool
     * @since 2.0.0
     */
    public function canShow()
    {
        return $this->_authSession->isFirstPageAfterLogin() && $this->_getLatestItem();
    }
}
