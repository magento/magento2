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
 * @since 100.0.2
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
     */
    protected $_severityIconsUrl;

    /**
     * Authentication
     *
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * Critical messages collection
     *
     * @var \Magento\AdminNotification\Model\ResourceModel\Inbox\Collection
     */
    protected $_criticalCollection;

    /**
     * @var \Magento\Adminnotification\Model\Inbox
     */
    protected $_latestItem;

    /**
     * The property is used to define content-scope of block. Can be private or public.
     * If it isn't defined then application considers it as false.
     *
     * @var bool
     */
    protected $_isScopePrivate;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\AdminNotification\Model\ResourceModel\Inbox\Collection\Critical $criticalCollection
     * @param array $data
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
     */
    protected function _getLatestItem()
    {
        if ($this->_latestItem == null) {
            $items = array_values($this->_criticalCollection->getItems());
            $this->_latestItem = false;
            if (count($items)) {
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
    public function canShow()
    {
        return $this->_authSession->isFirstPageAfterLogin() && $this->_getLatestItem();
    }
}
