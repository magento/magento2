<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Observer;

use Magento\Framework\Event\ObserverInterface;

class PreventExpressCheckoutObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * Url model
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * Persistent session
     *
     * @var \Magento\Persistent\Helper\Session
     */
    protected $_persistentSession = null;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Checkout\Helper\ExpressRedirect
     */
    protected $_expressRedirectHelper;

    /**
     * @param \Magento\Persistent\Helper\Session $persistentSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Checkout\Helper\ExpressRedirect $expressRedirectHelper
     */
    public function __construct(
        \Magento\Persistent\Helper\Session $persistentSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Helper\ExpressRedirect $expressRedirectHelper
    ) {
        $this->_persistentSession = $persistentSession;
        $this->_customerSession = $customerSession;
        $this->_url = $url;
        $this->messageManager = $messageManager;
        $this->_expressRedirectHelper = $expressRedirectHelper;
    }

    /**
     * Prevent express checkout
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!($this->_persistentSession->isPersistent() && !$this->_customerSession->isLoggedIn())) {
            return;
        }

        /** @var $controllerAction \Magento\Checkout\Controller\Express\RedirectLoginInterface*/
        $controllerAction = $observer->getEvent()->getControllerAction();
        if (!$controllerAction ||
            !$controllerAction instanceof \Magento\Checkout\Controller\Express\RedirectLoginInterface ||
            $controllerAction->getRedirectActionName() != $controllerAction->getRequest()->getActionName()
        ) {
            return;
        }

        $this->messageManager->addNotice(__('To check out, please sign in using your email address.'));
        $customerBeforeAuthUrl = $this->_url->getUrl('persistent/index/expressCheckout');

        $this->_expressRedirectHelper->redirectLogin($controllerAction, $customerBeforeAuthUrl);
    }
}
