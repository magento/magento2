<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Observer\Backend;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * User backend observer model for passwords
 * @since 2.0.0
 */
class ForceAdminPasswordChangeObserver implements ObserverInterface
{
    /**
     * Backend configuration interface
     *
     * @var \Magento\User\Model\Backend\Config\ObserverConfig
     * @since 2.0.0
     */
    protected $observerConfig;

    /**
     * Authorization interface
     *
     * @var \Magento\Framework\AuthorizationInterface
     * @since 2.0.0
     */
    protected $authorization;

    /**
     * Backend url interface
     *
     * @var \Magento\Backend\Model\UrlInterface
     * @since 2.0.0
     */
    protected $url;

    /**
     * Backend session
     *
     * @var \Magento\Backend\Model\Session
     * @since 2.0.0
     */
    protected $session;

    /**
     * Backend authorization session
     *
     * @var \Magento\Backend\Model\Auth\Session
     * @since 2.0.0
     */
    protected $authSession;

    /**
     * Action flag
     *
     * @var \Magento\Framework\App\ActionFlag
     * @since 2.0.0
     */
    protected $actionFlag;

    /**
     * Message manager interface
     *
     * @var \Magento\Framework\Message\ManagerInterface
     * @since 2.0.0
     */
    protected $messageManager;

    /**
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param \Magento\User\Model\Backend\Config\ObserverConfig $observerConfig
     * @param \Magento\Backend\Model\UrlInterface $url
     * @param \Magento\Backend\Model\Session $session
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\User\Model\Backend\Config\ObserverConfig $observerConfig,
        \Magento\Backend\Model\UrlInterface $url,
        \Magento\Backend\Model\Session $session,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->authorization = $authorization;
        $this->observerConfig = $observerConfig;
        $this->url = $url;
        $this->session = $session;
        $this->authSession = $authSession;
        $this->actionFlag = $actionFlag;
        $this->messageManager = $messageManager;
    }

    /**
     * Force admin to change password
     *
     * @param EventObserver $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->observerConfig->isPasswordChangeForced()) {
            return;
        }
        if (!$this->authSession->isLoggedIn()) {
            return;
        }
        $actionList = [
            'adminhtml_system_account_index',
            'adminhtml_system_account_save',
            'adminhtml_auth_logout',
        ];
        /** @var \Magento\Framework\App\Action\Action $controller */
        $controller = $observer->getEvent()->getControllerAction();
        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = $observer->getEvent()->getRequest();

        if ($this->authSession->getPciAdminUserIsPasswordExpired()) {
            if (!in_array($request->getFullActionName(), $actionList)) {
                if ($this->authorization->isAllowed('Magento_Backend::myaccount')) {
                    $controller->getResponse()->setRedirect($this->url->getUrl('adminhtml/system_account/'));
                    $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                    $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_POST_DISPATCH, true);
                } else {
                    /*
                     * if admin password is expired and access to 'My Account' page is denied
                     * than we need to do force logout with error message
                     */
                    $this->authSession->clearStorage();
                    $this->session->clearStorage();
                    $this->messageManager->addErrorMessage(
                        __('Your password has expired; please contact your administrator.')
                    );
                    $controller->getRequest()->setDispatched(false);
                }
            }
        }
    }
}
