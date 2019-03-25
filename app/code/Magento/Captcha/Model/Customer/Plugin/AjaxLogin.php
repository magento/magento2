<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Captcha\Model\Customer\Plugin;

use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Around plugin for login action.
 */
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
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;

    /**
     * @var array
     */
    protected $formIds;

    /**
     * @param CaptchaHelper $helper
     * @param SessionManagerInterface $sessionManager
     * @param JsonFactory $resultJsonFactory
     * @param array $formIds
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @throws \RuntimeException
     */
    public function __construct(
        CaptchaHelper $helper,
        SessionManagerInterface $sessionManager,
        JsonFactory $resultJsonFactory,
        array $formIds,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->helper = $helper;
        $this->sessionManager = $sessionManager;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->formIds = $formIds;
    }

    /**
     * Check captcha data on login action.
     *
     * @param \Magento\Customer\Controller\Ajax\Login $subject
     * @param \Closure $proceed
     * @return $this
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
            $loginParams = $this->serializer->unserialize($content);
        }
        $username = $loginParams['username'] ?? null;
        $captchaString = $loginParams[$captchaInputName] ?? null;
        $loginFormId = $loginParams[$captchaFormIdField] ?? null;

        if (!in_array($loginFormId, $this->formIds) && $this->helper->getCaptcha($loginFormId)->isRequired($username)) {
            return $this->returnJsonError(__('Provided form does not exist'));
        }

        foreach ($this->formIds as $formId) {
            if ($formId === $loginFormId) {
                $captchaModel = $this->helper->getCaptcha($formId);
                if ($captchaModel->isRequired($username)) {
                    if (!$captchaModel->isCorrect($captchaString)) {
                        $this->sessionManager->setUsername($username);
                        $captchaModel->logAttempt($username);
                        return $this->returnJsonError(__('Incorrect CAPTCHA'));
                    }
                }
                $captchaModel->logAttempt($username);
            }
        }
        return $proceed();
    }

    /**
     * Format JSON response.
     *
     * @param \Magento\Framework\Phrase $phrase
     * @return \Magento\Framework\Controller\Result\Json
     */
    private function returnJsonError(\Magento\Framework\Phrase $phrase): \Magento\Framework\Controller\Result\Json
    {
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData(['errors' => true, 'message' => $phrase]);
    }
}
