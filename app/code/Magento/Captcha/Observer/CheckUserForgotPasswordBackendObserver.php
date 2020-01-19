<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Captcha\Observer;

use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer as Event;
use Magento\Framework\Event\ObserverInterface;
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
    protected $_helper;

    /**
     * @var CaptchaStringResolver
     */
    protected $captchaStringResolver;

    /**
     * @var SessionManagerInterface
     */
    protected $_session;

    /**
     * @var ActionFlag
     */
    protected $_actionFlag;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

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
     * @param RequestInterface|null $request
     */
    public function __construct(
        CaptchaHelper $helper,
        CaptchaStringResolver $captchaStringResolver,
        SessionManagerInterface $session,
        ActionFlag $actionFlag,
        ManagerInterface $messageManager,
        RequestInterface $request = null
    ) {
        $this->_helper = $helper;
        $this->captchaStringResolver = $captchaStringResolver;
        $this->_session = $session;
        $this->_actionFlag = $actionFlag;
        $this->messageManager = $messageManager;
        $this->request = $request ?? ObjectManager::getInstance()->get(RequestInterface::class);
    }

    /**
     * Check Captcha On User Login Backend Page
     *
     * @param Event $observer
     * @return $this
     * @throws \Magento\Framework\Exception\Plugin\AuthenticationException
     */
    public function execute(Event $observer)
    {
        $formId = 'backend_forgotpassword';
        $captchaModel = $this->_helper->getCaptcha($formId);
        $controller = $observer->getControllerAction();
        $params = $this->request->getParams();
        $email = (string)$this->request->getParam('email');
        if (!empty($params)
            && !empty($email)
            && $captchaModel->isRequired()
            && !$captchaModel->isCorrect($this->captchaStringResolver->resolve($this->request, $formId))
        ) {
            $this->_session->setEmail($email);
            $this->_actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
            $this->messageManager->addErrorMessage(__('Incorrect CAPTCHA'));
            $controller->getResponse()->setRedirect(
                $controller->getUrl('*/*/forgotpassword', ['_nosecret' => true])
            );
        }

        return $this;
    }
}
