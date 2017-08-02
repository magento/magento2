<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Observer;

use Magento\Customer\Model\AuthenticationInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class CheckUserLoginObserver implements ObserverInterface
{
    /**
     * @var \Magento\Captcha\Helper\Data
     * @since 2.0.0
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\App\ActionFlag
     * @since 2.0.0
     */
    protected $_actionFlag;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     * @since 2.0.0
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     * @since 2.0.0
     */
    protected $_session;

    /**
     * @var CaptchaStringResolver
     * @since 2.0.0
     */
    protected $captchaStringResolver;

    /**
     * Customer data
     *
     * @var \Magento\Customer\Model\Url
     * @since 2.0.0
     */
    protected $_customerUrl;

    /**
     * @var CustomerRepositoryInterface
     * @since 2.1.0
     */
    protected $customerRepository;

    /**
     * Authentication
     *
     * @var AuthenticationInterface
     * @since 2.1.0
     */
    protected $authentication;

    /**
     * @param \Magento\Captcha\Helper\Data $helper
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Session\SessionManagerInterface $customerSession
     * @param CaptchaStringResolver $captchaStringResolver
     * @param \Magento\Customer\Model\Url $customerUrl
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Captcha\Helper\Data $helper,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Session\SessionManagerInterface $customerSession,
        CaptchaStringResolver $captchaStringResolver,
        \Magento\Customer\Model\Url $customerUrl
    ) {
        $this->_helper = $helper;
        $this->_actionFlag = $actionFlag;
        $this->messageManager = $messageManager;
        $this->_session = $customerSession;
        $this->captchaStringResolver = $captchaStringResolver;
        $this->_customerUrl = $customerUrl;
    }

    /**
     * Get customer repository
     *
     * @return \Magento\Customer\Api\CustomerRepositoryInterface
     * @since 2.1.0
     */
    private function getCustomerRepository()
    {

        if (!($this->customerRepository instanceof \Magento\Customer\Api\CustomerRepositoryInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Customer\Api\CustomerRepositoryInterface::class
            );
        } else {
            return $this->customerRepository;
        }
    }

    /**
     * Get authentication
     *
     * @return AuthenticationInterface
     * @since 2.1.0
     */
    private function getAuthentication()
    {

        if (!($this->authentication instanceof AuthenticationInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                AuthenticationInterface::class
            );
        } else {
            return $this->authentication;
        }
    }

    /**
     * Check captcha on user login page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @throws NoSuchEntityException
     * @return $this
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $formId = 'user_login';
        $captchaModel = $this->_helper->getCaptcha($formId);
        $controller = $observer->getControllerAction();
        $loginParams = $controller->getRequest()->getPost('login');
        $login = (is_array($loginParams) && array_key_exists('username', $loginParams))
            ? $loginParams['username']
            : null;
        if ($captchaModel->isRequired($login)) {
            $word = $this->captchaStringResolver->resolve($controller->getRequest(), $formId);
            if (!$captchaModel->isCorrect($word)) {
                try {
                    $customer = $this->getCustomerRepository()->get($login);
                    $this->getAuthentication()->processAuthenticationFailure($customer->getId());
                } catch (NoSuchEntityException $e) {
                    //do nothing as customer existance is validated later in authenticate method
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
