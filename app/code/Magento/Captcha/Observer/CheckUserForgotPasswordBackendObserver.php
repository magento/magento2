<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Captcha\Observer;

use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer as Event;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\Plugin\AuthenticationException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Handle request for Forgot Password
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class CheckUserForgotPasswordBackendObserver implements ObserverInterface
{
    /**
     * @var CaptchaHelper
     */
    private $helper;

    /**
     * @var CaptchaStringResolver
     */
    private $captchaStringResolver;

    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * @var ActionFlag
     */
    private $actionFlag;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param CaptchaHelper $helper
     * @param CaptchaStringResolver $captchaStringResolver
     * @param SessionManagerInterface $session
     * @param ActionFlag $actionFlag
     * @param ManagerInterface $messageManager
     * @param RequestInterface $request
     */
    public function __construct(
        CaptchaHelper $helper,
        CaptchaStringResolver $captchaStringResolver,
        SessionManagerInterface $session,
        ActionFlag $actionFlag,
        ManagerInterface $messageManager,
        RequestInterface $request
    ) {
        $this->helper = $helper;
        $this->captchaStringResolver = $captchaStringResolver;
        $this->session = $session;
        $this->actionFlag = $actionFlag;
        $this->messageManager = $messageManager;
        $this->request = $request;
    }

    /**
     * Check Captcha On User Login Backend Page
     *
     * @param Event $observer
     *
     * @return $this
     * @throws AuthenticationException
     */
    public function execute(Event $observer)
    {
        $formId = 'backend_forgotpassword';
        $captchaModel = $this->helper->getCaptcha($formId);
        $controller = $observer->getControllerAction();
        $params = $this->request->getParams();
        $email = (string)$this->request->getParam('email');
        if (!empty($params)
            && !empty($email)
            && $captchaModel->isRequired()
            && !$captchaModel->isCorrect($this->captchaStringResolver->resolve($this->request, $formId))
        ) {
            $this->session->setEmail($email);
            $this->actionFlag->set('', ActionInterface::FLAG_NO_DISPATCH, true);
            $this->messageManager->addErrorMessage(__('Incorrect CAPTCHA'));
            $controller->getResponse()->setRedirect(
                $controller->getUrl('*/*/forgotpassword', ['_nosecret' => true])
            );
        }

        return $this;
    }
}
