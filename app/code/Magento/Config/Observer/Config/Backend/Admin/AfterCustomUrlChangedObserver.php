<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Observer\Config\Backend\Admin;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\Config\Observer\Config\Backend\Admin\AfterCustomUrlChangedObserver
 *
 * @since 2.0.0
 */
class AfterCustomUrlChangedObserver implements ObserverInterface
{
    /**
     * Backend data
     *
     * @var \Magento\Backend\Helper\Data
     * @since 2.0.0
     */
    protected $_backendData;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     * @since 2.0.0
     */
    protected $_authSession;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     * @since 2.0.0
     */
    protected $_response;

    /**
     * @param \Magento\Backend\Helper\Data $backendData
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\App\ResponseInterface $response
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $this->_backendData = $backendData;
        $this->_coreRegistry = $coreRegistry;
        $this->_authSession = $authSession;
        $this->_response = $response;
    }

    /**
     * Log out user and redirect to new admin custom url
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.ExitExpression)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_coreRegistry->registry('custom_admin_path_redirect') === null) {
            return;
        }

        $this->_authSession->destroy();
        $adminUrl = $this->_backendData->getHomePageUrl();
        $this->_response->setRedirect($adminUrl)->sendResponse();
        exit(0);
    }
}
