<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Captcha\Model\Customer\Plugin;

use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\Customer\Controller\Ajax\Login;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Magento\Framework\Phrase;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Around plugin for login action.
 */
class AjaxLogin
{
    /**
     * @var CaptchaHelper
     */
    private $helper;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var JsonSerializer
     */
    private $serializer;

    /**
     * @var array
     */
    private $formIds;

    /**
     * @param CaptchaHelper $helper
     * @param SessionManagerInterface $sessionManager
     * @param JsonFactory $resultJsonFactory
     * @param array $formIds
     * @param JsonSerializer $serializer
     */
    public function __construct(
        CaptchaHelper $helper,
        SessionManagerInterface $sessionManager,
        JsonFactory $resultJsonFactory,
        array $formIds,
        JsonSerializer $serializer
    ) {
        $this->helper = $helper;
        $this->sessionManager = $sessionManager;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->serializer = $serializer;
        $this->formIds = $formIds;
    }

    /**
     * Check captcha data on login action.
     *
     * @param Login $subject
     * @param \Closure $proceed
     *
     * @return AjaxLogin|JsonResult
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function aroundExecute(
        Login $subject,
        \Closure $proceed
    ) {
        $captchaFormIdField = 'captcha_form_id';
        $captchaInputName = 'captcha_string';

        /** @var RequestInterface $request */
        $request = $subject->getRequest();

        $loginParams = [];
        $content = $request->getContent();
        if ($content) {
            $loginParams = $this->serializer->unserialize($content);
        }
        $username = $loginParams['username'] ?? null;
        $captchaString = $loginParams[$captchaInputName] ?? null;
        $loginFormId = $loginParams[$captchaFormIdField] ?? null;

        if (!in_array($loginFormId, $this->formIds, false) &&
            $this->helper->getCaptcha($loginFormId)->isRequired($username)
        ) {
            return $this->returnJsonError(__('Provided form does not exist'));
        }

        foreach ($this->formIds as $formId) {
            if ($formId === $loginFormId) {
                $captchaModel = $this->helper->getCaptcha($formId);
                if ($captchaModel->isRequired($username) && !$captchaModel->isCorrect($captchaString)) {
                    $this->sessionManager->setUsername($username);
                    $captchaModel->logAttempt($username);
                    return $this->returnJsonError(__('Incorrect CAPTCHA'));
                }
                $captchaModel->logAttempt($username);
            }
        }
        return $proceed();
    }

    /**
     * Format JSON response.
     *
     * @param Phrase $phrase
     *
     * @return JsonResult
     */
    private function returnJsonError(Phrase $phrase): JsonResult
    {
        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData(['errors' => true, 'message' => $phrase]);
    }
}
