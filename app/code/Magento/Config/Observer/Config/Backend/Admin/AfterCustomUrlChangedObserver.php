<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Observer\Config\Backend\Admin;

use Magento\Framework\Event\ObserverInterface;

class AfterCustomUrlChangedObserver implements ObserverInterface
{
    /**
     * Backend data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backendData;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Backend\Helper\Data $backendData
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_backendData = $backendData;
        $this->_coreRegistry = $coreRegistry;
        $this->_authSession = $authSession;
        $this->_response = $response;
        $this->_storeManager = $storeManager;
    }

    /**
     * Log out user and redirect him to new admin custom url
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_coreRegistry->registry('custom_admin_path_redirect') === null) {
            return;
        }

        $this->_authSession->destroy();

        $route = $this->_backendData->getAreaFrontName();

        $this->_response->setRedirect($this->_storeManager->getStore()->getBaseUrl() . $route)->sendResponse();
        exit(0);
    }
}
