<?php
/**
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
namespace Magento\Persistent\Model\Observer;

class PreventExpressCheckout
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

        $this->messageManager->addNotice(__('To check out, please log in using your email address.'));
        $customerBeforeAuthUrl = $this->_url->getUrl('persistent/index/expressCheckout');

        $this->_expressRedirectHelper->redirectLogin($controllerAction, $customerBeforeAuthUrl);
    }
}
