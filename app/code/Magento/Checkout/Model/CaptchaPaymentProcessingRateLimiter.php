<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Checkout\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Checkout\Api\Exception\PaymentProcessingRateLimitExceededException;
use Magento\Checkout\Api\PaymentProcessingRateLimiterInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\Captcha\Observer\CaptchaStringResolver as CaptchaResolver;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Utilize CAPTCHA to limit payment processing requests.
 */
class CaptchaPaymentProcessingRateLimiter implements PaymentProcessingRateLimiterInterface
{
    public const CAPTCHA_FORM = 'payment_processing_request';

    /**
     * @var CaptchaRateLimiter
     */
    private $limiter;

    /**
     * CaptchaPaymentProcessingRateLimiter constructor.
     *
     * @param UserContextInterface $userContext
     * @param CustomerRepositoryInterface $customerRepo
     * @param CaptchaHelper $captchaHelper
     * @param RequestInterface $request
     * @param CaptchaResolver $captchaResolver
     * @param CaptchaRateLimiterFactory|null $limiterFactory
     */
    public function __construct(
        UserContextInterface $userContext,
        CustomerRepositoryInterface $customerRepo,
        CaptchaHelper $captchaHelper,
        RequestInterface $request,
        CaptchaResolver $captchaResolver,
        ?CaptchaRateLimiterFactory $limiterFactory
    ) {
        $limiterFactory = $limiterFactory ?? ObjectManager::getInstance()->get(CaptchaRateLimiterFactory::class);
        $this->limiter = $limiterFactory->create([
            'userContext' => $userContext,
            'customerRepo' => $customerRepo,
            'captchaHelper' => $captchaHelper,
            'captchaResolver' => $captchaResolver,
            'request' => $request,
            'captchaId' => self::CAPTCHA_FORM
        ]);
    }

    /**
     * @inheritDoc
     */
    public function limit(): void
    {
        try {
            $this->limiter->limit();
        } catch (LocalizedException $exception) {
            throw new PaymentProcessingRateLimitExceededException(__($exception->getMessage()), $exception);
        }
    }
}
