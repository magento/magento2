<?php
/**
 * Critical notification window
 *
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\AdminNotification\Block;

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
     * @var \Magento\AdminNotification\Model\Resource\Inbox\Collection
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
     * @param \Magento\AdminNotification\Model\Resource\Inbox\Collection\Critical $criticalCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\AdminNotification\Model\Resource\Inbox\Collection\Critical $criticalCollection,
        array $data = array()
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
            $this->setReadDetailsText($this->escapeHtml(__('Read details')));
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
     */
    public function canShow()
    {
        return $this->_authSession->isFirstPageAfterLogin() && $this->_getLatestItem();
    }
}
