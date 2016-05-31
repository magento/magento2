<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * @var array
     */
    protected $formIds;

    /**
     * @param CaptchaHelper $helper
     * @param SessionManagerInterface $sessionManager
     * @param JsonFactory $resultJsonFactory
     * @param array $formIds
     */
    public function __construct(
        CaptchaHelper $helper,
        SessionManagerInterface $sessionManager,
        JsonFactory $resultJsonFactory,
        array $formIds
    ) {
        $this->helper = $helper;
        $this->sessionManager = $sessionManager;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->formIds = $formIds;
    }

    /**
     * @param \Magento\Customer\Controller\Ajax\Login $subject
     * @param \Closure $proceed
     * @return $this
     * @throws \Zend_Json_Exception
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function aroundExecute(
        \Magento\Customer\Controller\Ajax\Login $subject,
        \Closure $proceed
    ) {
        $captchaFormIdField = 'captcha_form_id';
        $captchaInputName = 'captcha_string';

        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = $subject->getRequest();

        $loginParams = [];
        $content = $request->getContent();
        if ($content) {
            $loginParams = \Zend_Json::decode($content);
        }
        $username = isset($loginParams['username']) ? $loginParams['username'] : null;
        $captchaString = isset($loginParams[$captchaInputName]) ? $loginParams[$captchaInputName] : null;
        $loginFormId = isset($loginParams[$captchaFormIdField]) ? $loginParams[$captchaFormIdField] : null;

        foreach ($this->formIds as $formId) {
            $captchaModel = $this->helper->getCaptcha($formId);
            if ($captchaModel->isRequired($username) && !in_array($loginFormId, $this->formIds)) {
                $resultJson = $this->resultJsonFactory->create();
                return $resultJson->setData(['errors' => true, 'message' => __('Provided form does not exist')]);
            }

            if ($formId == $loginFormId) {
                $captchaModel->logAttempt($username);
                if (!$captchaModel->isCorrect($captchaString)) {
                    $this->sessionManager->setUsername($username);
                    /** @var \Magento\Framework\Controller\Result\Json $resultJson */
                    $resultJson = $this->resultJsonFactory->create();
                    return $resultJson->setData(['errors' => true, 'message' => __('Incorrect CAPTCHA')]);
                }
            }
        }
        return $proceed();
    }
}
