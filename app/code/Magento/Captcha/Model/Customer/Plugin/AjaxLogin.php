<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Model\Customer\Plugin;

use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class AjaxLogin
{
    /**
     * @var \Magento\Captcha\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $sessionManager;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param CaptchaHelper $helper
     * @param SessionManagerInterface $sessionManager
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        CaptchaHelper $helper,
        SessionManagerInterface $sessionManager,
        JsonFactory $resultJsonFactory
    ) {
        $this->helper = $helper;
        $this->sessionManager = $sessionManager;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * @param \Magento\Customer\Controller\Ajax\Login $subject
     * @param callable $proceed
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Zend_Json_Exception
     */
    public function aroundExecute(
        \Magento\Customer\Controller\Ajax\Login $subject,
        \Closure $proceed
    ) {
        $loginFormId = 'user_login';
        $captchaInputName = 'captcha_string';

        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = $subject->getRequest();

        /** @var \Magento\Captcha\Model\ModelInterface $captchaModel */
        $captchaModel = $this->helper->getCaptcha($loginFormId);

        $loginParams = \Zend_Json::decode($request->getContent());
        $username = isset($loginParams['username']) ? $loginParams['username'] : null;
        $captchaString = isset($loginParams[$captchaInputName])
            ? $loginParams[$captchaInputName]
            : null;

        if ($captchaModel->isRequired($username)) {
            $captchaModel->logAttempt($username);
            if (!$captchaModel->isCorrect($captchaString)) {
                $this->sessionManager->setUsername($username);
                /** @var \Magento\Framework\Controller\Result\Json $resultJson */
                $resultJson = $this->resultJsonFactory->create();
                return $resultJson->setData(['errors' => true, 'message' => __('Incorrect CAPTCHA')]);
            }
        }
        return $proceed();
    }
}
