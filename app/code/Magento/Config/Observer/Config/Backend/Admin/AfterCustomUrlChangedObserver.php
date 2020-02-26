<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Observer\Config\Backend\Admin;

use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;

/**
 * Class AfterCustomUrlChangedObserver redirects to new custom admin URL.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AfterCustomUrlChangedObserver implements ObserverInterface
{
    /**
     * Backend data
     *
     * @var Data
     */
    protected $_backendData;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var Session
     */
    protected $_authSession;

    /**
     * @var ResponseInterface
     */
    protected $_response;

    /**
     * @param Data $backendData
     * @param Registry $coreRegistry
     * @param Session $authSession
     * @param ResponseInterface $response
     */
    public function __construct(
        Data $backendData,
        Registry $coreRegistry,
        Session $authSession,
        ResponseInterface $response
    ) {
        $this->_backendData = $backendData;
        $this->_coreRegistry = $coreRegistry;
        $this->_authSession = $authSession;
        $this->_response = $response;
    }

    /**
     * Log out user and redirect to new admin custom url
     *
     * @param Observer $observer
     * @return $this|void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        if ($this->_coreRegistry->registry('custom_admin_path_redirect') === null) {
            return;
        }

        $this->_authSession->destroy();
        $adminUrl = $this->_backendData->getHomePageUrl();
        $this->_response->setRedirect($adminUrl)->sendResponse();

        return $this;
    }
}
