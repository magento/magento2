<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Checkout\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Captcha\Model\DefaultModel as Captcha;
use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\Captcha\Observer\CaptchaStringResolver as CaptchaResolver;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Utilize CAPTCHA as a rate-limiting mechanism.
 */
class CaptchaRateLimiter
{
    /**
     * @var string
     */
    private $captchaId;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepo;

    /**
     * @var CaptchaHelper
     */
    private $captchaHelper;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CaptchaResolver
     */
    private $captchaResolver;

    /**
     * @param UserContextInterface $userContext
     * @param CustomerRepositoryInterface $customerRepo
     * @param CaptchaHelper $captchaHelper
     * @param RequestInterface $request
     * @param CaptchaResolver $captchaResolver
     * @param string $captchaId
     */
    public function __construct(
        UserContextInterface $userContext,
        CustomerRepositoryInterface $customerRepo,
        CaptchaHelper $captchaHelper,
        RequestInterface $request,
        CaptchaResolver $captchaResolver,
        string $captchaId
    ) {
        $this->userContext = $userContext;
        $this->customerRepo = $customerRepo;
        $this->captchaHelper = $captchaHelper;
        $this->request = $request;
        $this->captchaResolver = $captchaResolver;
        $this->captchaId = $captchaId;
    }

    /**
     * Validate CAPTCHA if necessary.
     *
     * @return void
     * @throws LocalizedException
     */
    public function limit(): void
    {
        if ($this->userContext->getUserType() !== UserContextInterface::USER_TYPE_GUEST
            && $this->userContext->getUserType() !== UserContextInterface::USER_TYPE_CUSTOMER
            && $this->userContext->getUserType() !== null
        ) {
            return;
        }

        $login = $this->retrieveLogin();
        /** @var Captcha $captcha */
        $captcha = $this->captchaHelper->getCaptcha($this->captchaId);
        /** @var LocalizedException|null $exception */
        $exception = null;
        if ($captcha->isRequired($login)) {
            $value = $this->captchaResolver->resolve($this->request, $this->captchaId);
            if ($value && !$captcha->isCorrect($value)) {
                $exception = new LocalizedException(__('Incorrect CAPTCHA'));
            } elseif (!$value) {
                $exception = new LocalizedException(
                    __('Please provide CAPTCHA code and try again')
                );
            }
        }

        $captcha->logAttempt($login);
        if ($exception) {
            throw $exception;
        }
    }

    /**
     * Retrieve current user login.
     *
     * @return string|null
     */
    private function retrieveLogin(): ?string
    {
        $login = null;
        if ($this->userContext->getUserId()) {
            $login = $this->customerRepo->getById($this->userContext->getUserId())->getEmail();
        }

        return $login;
    }
}
