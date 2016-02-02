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

class CheckUserEditObserver implements ObserverInterface
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
        $this->_helper = $helper;
        $this->_actionFlag = $actionFlag;
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
        $this->captchaStringResolver = $captchaStringResolver;
        $this->accountManagementHelper = $accountManagementHelper;
        $this->session = $customerSession;
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
        $formId = 'user_edit';
        $captchaModel = $this->_helper->getCaptcha($formId);
        if ($captchaModel->isRequired()) {
            /** @var \Magento\Framework\App\Action\Action $controller */
            $controller = $observer->getControllerAction();
            if (!$captchaModel->isCorrect($this->captchaStringResolver->resolve($controller->getRequest(), $formId))) {
                try {
                    $customer = $this->customerRepository->getById($this->session->getCustomerId());
                    $this->accountManagementHelper->processCustomerLockoutData($customer->getId());
                    $this->customerRepository->save($customer);
                } catch (NoSuchEntityException $e) {
                    //do nothing as customer existance is validated later in authenticate method
                }
                $this->workWithLock();
                $this->messageManager->addError(__('Incorrect CAPTCHA'));
                $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $this->redirect->redirect($controller->getResponse(), '*/*/edit');
            }
        }

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
        $customerModel = $this->session->getCustomer();
        if ($customerModel->isCustomerLocked()) {
            $this->session->logout();
            $this->session->start();
            $message = __(
                'The account is locked. Please wait and try again or contact %1.',
                $this->scopeConfig->getValue('contact/email/recipient_email')
            );
            $this->messageManager->addError($message);
        }
    }
}
