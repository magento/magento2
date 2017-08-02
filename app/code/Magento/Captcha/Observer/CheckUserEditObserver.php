<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Observer;

use Magento\Customer\Model\AuthenticationInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.1.0
 */
class CheckUserEditObserver implements ObserverInterface
{
    /**
     * Form ID
     */
    const FORM_ID = 'user_edit';

    /**
     * @var \Magento\Captcha\Helper\Data
     * @since 2.1.0
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\ActionFlag
     * @since 2.1.0
     */
    protected $actionFlag;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     * @since 2.1.0
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     * @since 2.1.0
     */
    protected $redirect;

    /**
     * @var CaptchaStringResolver
     * @since 2.1.0
     */
    protected $captchaStringResolver;

    /**
     * Authentication
     *
     * @var AuthenticationInterface
     * @since 2.1.0
     */
    protected $authentication;

    /**
     * @var Session
     * @since 2.1.0
     */
    protected $customerSession;

    /**
     * @var ScopeConfigInterface
     * @since 2.1.0
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Captcha\Helper\Data $helper
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param CaptchaStringResolver $captchaStringResolver
     * @param AuthenticationInterface $authentication
     * @param Session $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @since 2.1.0
     */
    public function __construct(
        \Magento\Captcha\Helper\Data $helper,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        CaptchaStringResolver $captchaStringResolver,
        AuthenticationInterface $authentication,
        Session $customerSession,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->helper = $helper;
        $this->actionFlag = $actionFlag;
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
        $this->captchaStringResolver = $captchaStringResolver;
        $this->authentication = $authentication;
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check Captcha On Forgot Password Page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @since 2.1.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $captchaModel = $this->helper->getCaptcha(self::FORM_ID);
        if ($captchaModel->isRequired()) {
            /** @var \Magento\Framework\App\Action\Action $controller */
            $controller = $observer->getControllerAction();
            if (!$captchaModel->isCorrect(
                $this->captchaStringResolver->resolve(
                    $controller->getRequest(),
                    self::FORM_ID
                )
            )) {
                $customerId = $this->customerSession->getCustomerId();
                $this->authentication->processAuthenticationFailure($customerId);
                if ($this->authentication->isLocked($customerId)) {
                    $this->customerSession->logout();
                    $this->customerSession->start();
                    $message = __(
                        'The account is locked. Please wait and try again or contact %1.',
                        $this->scopeConfig->getValue('contact/email/recipient_email')
                    );
                    $this->messageManager->addError($message);
                }
                $this->messageManager->addError(__('Incorrect CAPTCHA'));
                $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $this->redirect->redirect($controller->getResponse(), '*/*/edit');
            }
        }

        $customer = $this->customerSession->getCustomer();
        $login = $customer->getEmail();
        $captchaModel->logAttempt($login);

        return $this;
    }
}
