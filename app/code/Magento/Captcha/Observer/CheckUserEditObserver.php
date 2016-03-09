<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Helper\AccountManagement as AccountManagementHelper;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckUserEditObserver implements ObserverInterface
{
    /**
     * Form ID
     */
    const FORM_ID = 'user_edit';

    /**
     * @var \Magento\Captcha\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $actionFlag;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var CaptchaStringResolver
     */
    protected $captchaStringResolver;

    /**
     * Account manager
     *
     * @var AccountManagementHelper
     */
    protected $accountManagementHelper;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @param \Magento\Captcha\Helper\Data $helper
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param CaptchaStringResolver $captchaStringResolver
     * @param AccountManagementHelper $accountManagementHelper
     * @param Session $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Captcha\Helper\Data $helper,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        CaptchaStringResolver $captchaStringResolver,
        AccountManagementHelper $accountManagementHelper,
        Session $customerSession,
        ScopeConfigInterface $scopeConfig,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->helper = $helper;
        $this->actionFlag = $actionFlag;
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
        $this->captchaStringResolver = $captchaStringResolver;
        $this->accountManagementHelper = $accountManagementHelper;
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Check Captcha On Forgot Password Page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
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
                try {
                    $customer = $this->customerRepository->getById($this->customerSession->getCustomerId());
                    $this->accountManagementHelper->processCustomerLockoutData($customer->getId());
                    $this->customerRepository->save($customer);
                } catch (NoSuchEntityException $e) {
                    //do nothing as customer existance is validated later in authenticate method
                }
                $this->workWithLock();
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

    /**
     * Logout a user if it is locked
     *
     * @throws \Magento\Framework\Exception\SessionException
     * @return void
     */
    protected function workWithLock()
    {
        $customerModel = $this->customerSession->getCustomer();
        if ($customerModel->isCustomerLocked()) {
            $this->customerSession->logout();
            $this->customerSession->start();
            $message = __(
                'The account is locked. Please wait and try again or contact %1.',
                $this->scopeConfig->getValue('contact/email/recipient_email')
            );
            $this->messageManager->addError($message);
        }
    }
}
