<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Observer\Backend;

use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\Session as BackendSession;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\User\Model\Backend\Config\ObserverConfig;

/**
 * User backend observer model for passwords
 */
class ForceAdminPasswordChangeObserver implements ObserverInterface
{
    /**
     * @param AuthorizationInterface $authorization
     * @param ObserverConfig $observerConfig
     * @param UrlInterface $url
     * @param BackendSession $session
     * @param Session $authSession
     * @param ActionFlag $actionFlag
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        protected readonly AuthorizationInterface $authorization,
        protected readonly ObserverConfig $observerConfig,
        protected readonly UrlInterface $url,
        protected readonly BackendSession $session,
        protected readonly Session $authSession,
        protected readonly ActionFlag $actionFlag,
        protected readonly ManagerInterface $messageManager
    ) {
    }

    /**
     * Force admin to change password
     *
     * @param EventObserver $observer
     * @return void
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
            'mui_index_render'
        ];
        /** @var Action $controller */
        $controller = $observer->getEvent()->getControllerAction();
        /** @var RequestInterface $request */
        $request = $observer->getEvent()->getRequest();

        if ($this->authSession->getPciAdminUserIsPasswordExpired()) {
            if (!in_array($request->getFullActionName(), $actionList)) {
                if ($this->authorization->isAllowed('Magento_Backend::myaccount')) {
                    $controller->getResponse()->setRedirect($this->url->getUrl('adminhtml/system_account/'));
                    $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
                    $this->actionFlag->set('', Action::FLAG_NO_POST_DISPATCH, true);
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
