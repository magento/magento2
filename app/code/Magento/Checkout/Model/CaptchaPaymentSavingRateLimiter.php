<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Checkout\Model;

use Magento\Checkout\Api\Exception\PaymentProcessingRateLimitExceededException;
use Magento\Checkout\Api\PaymentSavingRateLimiterInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Utilize CAPTCHA to limit save payment requests.
 */
class CaptchaPaymentSavingRateLimiter implements PaymentSavingRateLimiterInterface
{
    public const CAPTCHA_FORM = 'payment_saving_request';

    /**
     * @var CaptchaRateLimiter
     */
    private $limiter;

    /**
     * CaptchaPaymentProcessingRateLimiter constructor.
     *
     * @param CaptchaRateLimiterFactory $limiterFactory
     */
    public function __construct(
        CaptchaRateLimiterFactory $limiterFactory
    ) {
        $this->limiter = $limiterFactory->create(['captchaId' => self::CAPTCHA_FORM]);
    }

    /**
     * @inheritDoc
     */
    public function limit(): void
    {
        try {
            $this->limiter->limit();
        } catch (LocalizedException $exception) {
            throw new PaymentProcessingRateLimitExceededException(
                __(
                    'Could not store billing/shipping information at the moment'
                    .' but you can proceed with the checkout'
                ),
                $exception
            );
        }
    }
}
