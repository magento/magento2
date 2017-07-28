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
 * @since 2.0.0
 */
class Header extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'page/header.phtml';

    /**
     * Backend data
     *
     * @var \Magento\Backend\Helper\Data
     * @since 2.0.0
     */
    protected $_backendData = null;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     * @since 2.0.0
     */
    protected $_authSession;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Backend\Helper\Data $backendData
     * @param array $data
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getHomeLink()
    {
        return $this->_backendData->getHomePageUrl();
    }

    /**
     * @return \Magento\User\Model\User|null
     * @since 2.0.0
     */
    public function getUser()
    {
        return $this->_authSession->getUser();
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getLogoutLink()
    {
        return $this->getUrl('adminhtml/auth/logout');
    }

    /**
     * Check if noscript notice should be displayed
     *
     * @return boolean
     * @since 2.0.0
     */
    public function displayNoscriptNotice()
    {
        return $this->_scopeConfig->getValue(
            'web/browser_capabilities/javascript',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
