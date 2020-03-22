<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Captcha\Observer;

use Magento\Captcha\Helper\Data;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\Url;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Check captcha on user login page observer.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class CheckUserLoginObserver implements ObserverInterface
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var ActionFlag
     */
    private $actionFlag;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * @var CaptchaStringResolver
     */
    private $captchaStringResolver;

    /**
     * Customer data
     *
     * @var Url
     */
    private $customerUrl;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var AuthenticationInterface
     */
    private $authentication;

    /**
     * @param Data $helper
     * @param ActionFlag $actionFlag
     * @param ManagerInterface $messageManager
     * @param SessionManagerInterface $customerSession
     * @param CaptchaStringResolver $captchaStringResolver
     * @param Url $customerUrl
     * @param CustomerRepositoryInterface $customerRepository
     * @param AuthenticationInterface $authentication
     */
    public function __construct(
        Data $helper,
        ActionFlag $actionFlag,
        ManagerInterface $messageManager,
        SessionManagerInterface $customerSession,
        CaptchaStringResolver $captchaStringResolver,
        Url $customerUrl,
        CustomerRepositoryInterface $customerRepository,
        AuthenticationInterface $authentication
    ) {
        $this->helper = $helper;
        $this->actionFlag = $actionFlag;
        $this->messageManager = $messageManager;
        $this->session = $customerSession;
        $this->captchaStringResolver = $captchaStringResolver;
        $this->customerUrl = $customerUrl;
        $this->customerRepository = $customerRepository;
        $this->authentication = $authentication;
    }

    /**
     * Check captcha on user login page
     *
     * @param Observer $observer
     *
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        $formId = 'user_login';
        $captchaModel = $this->helper->getCaptcha($formId);
        $controller = $observer->getControllerAction();
        $loginParams = $controller->getRequest()->getPost('login');
        $login = (is_array($loginParams) && array_key_exists('username', $loginParams))
            ? $loginParams['username']
            : null;

        if ($captchaModel->isRequired($login)) {
            $word = $this->captchaStringResolver->resolve($controller->getRequest(), $formId);

            if (!$captchaModel->isCorrect($word)) {
                try {
                    $customer = $this->customerRepository->get($login);
                    $this->authentication->processAuthenticationFailure($customer->getId());
                    // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
                } catch (NoSuchEntityException $e) {
                    // do nothing as customer existence is validated later in authenticate method
                }
                $this->messageManager->addErrorMessage(__('Incorrect CAPTCHA'));
                $this->actionFlag->set('', ActionInterface::FLAG_NO_DISPATCH, true);
                $this->session->setUsername($login);
                $beforeUrl = $this->session->getBeforeAuthUrl();
                $url = $beforeUrl ?: $this->customerUrl->getLoginUrl();
                $controller->getResponse()->setRedirect($url);
            }
        }

        $captchaModel->logAttempt($login);

        return $this;
    }
}
