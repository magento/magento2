<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\ResourceModel\LockoutManagement;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Helper\AccountManagement as AccountManagementHelper;

class CheckUserLoginObserver implements ObserverInterface
{
    /**
     * @var \Magento\Captcha\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_session;

    /**
     * @var CaptchaStringResolver
     */
    protected $captchaStringResolver;

    /**
     * Customer data
     *
     * @var \Magento\Customer\Model\Url
     */
    protected $_customerUrl;

    /**
     * Lockout manager
     *
     * @var \Magento\Customer\Model\ResourceModel\LockoutManagement
     */
    protected $lockoutManager;

    /**
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * Account manager
     *
     * @var AccountManagementHelper
     */
    protected $accountManagementHelper;

    /**
     * @param \Magento\Captcha\Helper\Data $helper
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param CaptchaStringResolver $captchaStringResolver
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Customer\Model\ResourceModel\LockoutManagement $lockoutManager
     * @param \Magento\Customer\Model\CustomerRegistry $customerRegistry
     * @param AccountManagementHelper $accountManagementHelper
     */
    public function __construct(
        \Magento\Captcha\Helper\Data $helper,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Session\SessionManagerInterface $session,
        CaptchaStringResolver $captchaStringResolver,
        \Magento\Customer\Model\Url $customerUrl,
        LockoutManagement $lockoutManager,
        CustomerRegistry $customerRegistry,
        AccountManagementHelper $accountManagementHelper
    ) {
        $this->_helper = $helper;
        $this->_actionFlag = $actionFlag;
        $this->messageManager = $messageManager;
        $this->_session = $session;
        $this->captchaStringResolver = $captchaStringResolver;
        $this->_customerUrl = $customerUrl;
        $this->lockoutManager = $lockoutManager;
        $this->customerRegistry = $customerRegistry;
        $this->accountManagementHelper = $accountManagementHelper;
    }

    /**
     * Check Captcha On User Login Page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @throws NoSuchEntityException
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $formId = 'user_login';
        $captchaModel = $this->_helper->getCaptcha($formId);
        $controller = $observer->getControllerAction();
        $loginParams = $controller->getRequest()->getPost('login');
        $login = array_key_exists('username', $loginParams) ? $loginParams['username'] : null;
        if ($captchaModel->isRequired($login)) {
            $word = $this->captchaStringResolver->resolve($controller->getRequest(), $formId);
            if (!$captchaModel->isCorrect($word)) {
                try {
                    $customer = $this->customerRegistry->retrieveByEmail($login);
                    $this->lockoutManager->processLockout($customer);
                    $this->accountManagementHelper->reindexCustomer($customer->getId());
                } catch (NoSuchEntityException $e) {
                    $this->messageManager->addError(__('Invalid login or password.'));
                }
                $this->messageManager->addError(__('Incorrect CAPTCHA'));
                $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $this->_session->setUsername($login);
                $beforeUrl = $this->_session->getBeforeAuthUrl();
                $url = $beforeUrl ? $beforeUrl : $this->_customerUrl->getLoginUrl();
                $controller->getResponse()->setRedirect($url);
            }
        }
        $captchaModel->logAttempt($login);

        return $this;
    }
}
