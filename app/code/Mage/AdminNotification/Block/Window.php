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
 * @category    Mage
 * @package     Mage_AdminNotification
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_AdminNotification_Block_Window extends Mage_Backend_Block_Template
{
    /**
     * XML path of Severity icons url
     */
    const XML_SEVERITY_ICONS_URL_PATH  = 'system/adminnotification/severity_icons_url';

    /**
     * Severity icons url
     *
     * @var string
     */
    protected $_severityIconsUrl;

    /**
     * Authentication
     *
     * @var Mage_Backend_Model_Auth_Session
     */
    protected $_authSession;

    /**
     * Critical messages collection
     *
     * @var Mage_AdminNotification_Model_Resource_Inbox_Collection
     */
    protected $_criticalCollection;

    /**
     * @var Mage_Adminnotification_Model_Inbox
     */
    protected $_latestItem;

    /**
     * @param Mage_Backend_Block_Template_Context $context
     * @param Mage_Backend_Model_Auth_Session $authSession
     * @param Mage_AdminNotification_Model_Resource_Inbox_Collection_Critical $criticalCollection
     * @param array $data
     */
    public function __construct(
        Mage_Backend_Block_Template_Context $context,
        Mage_Backend_Model_Auth_Session $authSession,
        Mage_AdminNotification_Model_Resource_Inbox_Collection_Critical $criticalCollection,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_authSession = $authSession;
        $this->_criticalCollection = $criticalCollection;
    }

    /**
     * Render block
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->canShow()) {
            $this->setHeaderText($this->escapeHtml($this->__('Incoming Message')));
            $this->setCloseText($this->escapeHtml($this->__('close')));
            $this->setReadDetailsText($this->escapeHtml($this->__('Read details')));
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
     * @return bool|Mage_Adminnotification_Model_Inbox
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
