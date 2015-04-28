<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Model\Customer\Plugin;

use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\Captcha\Model\Checkout\ConfigProvider;

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
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @param CaptchaHelper $helper
     * @param \Magento\Framework\Session\SessionManagerInterface $sessionManager
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
        \Magento\Captcha\Helper\Data $helper,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ){
        $this->helper = $helper;
        $this->sessionManager = $sessionManager;
        $this->resultRawFactory = $resultRawFactory;
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
        $httpUnauthorizedCode = 401;
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
                $resultRaw = $this->resultRawFactory->create();
                return $resultRaw->setHttpResponseCode($httpUnauthorizedCode);
            }
        }
        return $proceed();
    }
}
