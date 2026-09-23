<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Captcha\Model;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\InputException;
use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\Captcha\Observer\CaptchaStringResolver as CaptchaResolver;

/**
 * Captcha validation class
 */
class CaptchaValidator
{
    /**
     * @var CaptchaHelper
     */
    private $captchaHelper;

    /**
     * @var CaptchaResolver
     */
    private $captchaResolver;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param CaptchaHelper $captchaHelper
     * @param CaptchaResolver $captchaResolver
     * @param RequestInterface $request
     */
    public function __construct(
        CaptchaHelper $captchaHelper,
        CaptchaResolver $captchaResolver,
        RequestInterface $request
    ) {
        $this->captchaHelper = $captchaHelper;
        $this->captchaResolver = $captchaResolver;
        $this->request = $request;
    }

    /**
     * Validate captcha for user creation
     *
     * @param string $formId
     *
     * @return void
     * @throws InputException
     */
    public function validate(string $formId): void
    {
        $captcha = $this->captchaHelper->getCaptcha($formId);
        if (!$captcha->isRequired($formId)) {
            return;
        }

        $value = $this->captchaResolver->resolve($this->request, $formId);
        if (!$value) {
            throw new InputException(__('CAPTCHA is required.'));
        }

        if (!$captcha->isCorrect($value)) {
            throw new InputException(__('Incorrect CAPTCHA'));
        }
    }
}
