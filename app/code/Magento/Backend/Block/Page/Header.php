<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Block\Page;

/**
 * Adminhtml header block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Header extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'page/header.phtml';

    /**
     * Backend data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backendData = null;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Backend\Helper\Data $backendData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Backend\Helper\Data $backendData,
        array $data = []
    ) {
        $this->_backendData = $backendData;
        $this->_authSession = $authSession;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getHomeLink()
    {
        return $this->_backendData->getHomePageUrl();
    }

    /**
     * @return \Magento\User\Model\User|null
     */
    public function getUser()
    {
        return $this->_authSession->getUser();
    }

    /**
     * @return string
     */
    public function getLogoutLink()
    {
        return $this->getUrl('adminhtml/auth/logout');
    }

    /**
     * Check if noscript notice should be displayed
     *
     * @return boolean
     */
    public function displayNoscriptNotice()
    {
        return $this->_scopeConfig->getValue(
            'web/browser_capabilities/javascript',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
