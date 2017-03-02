<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Observer;

use Magento\Framework\Event\ObserverInterface;

class CheckUserForgotPasswordBackendObserver implements ObserverInterface
{
    /**
     * @var \Magento\Captcha\Helper\Data
     */
    protected $_helper;

    /**
     * @var CaptchaStringResolver
     */
    protected $captchaStringResolver;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_session;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param \Magento\Captcha\Helper\Data $helper
     * @param CaptchaStringResolver $captchaStringResolver
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Captcha\Helper\Data $helper,
        CaptchaStringResolver $captchaStringResolver,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_helper = $helper;
        $this->captchaStringResolver = $captchaStringResolver;
        $this->_session = $session;
        $this->_actionFlag = $actionFlag;
        $this->messageManager = $messageManager;
    }

    /**
     * Check Captcha On User Login Backend Page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\Plugin\AuthenticationException
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $formId = 'backend_forgotpassword';
        $captchaModel = $this->_helper->getCaptcha($formId);
        $controller = $observer->getControllerAction();
        $email = (string)$observer->getControllerAction()->getRequest()->getParam('email');
        $params = $observer->getControllerAction()->getRequest()->getParams();
        if (!empty($email) && !empty($params)) {
            if ($captchaModel->isRequired()) {
                if (
                    !$captchaModel->isCorrect($this->captchaStringResolver->resolve($controller->getRequest(), $formId))
                ) {
                    $this->_session->setEmail((string)$controller->getRequest()->getPost('email'));
                    $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                    $this->messageManager->addError(__('Incorrect CAPTCHA'));
                    $controller->getResponse()->setRedirect(
                        $controller->getUrl('*/*/forgotpassword', ['_nosecret' => true])
                    );
                }
            }
        }

        return $this;
    }
}
