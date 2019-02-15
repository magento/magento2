<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Model\Coupon;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Api\Exception\CodeRequestLimitException;
use Magento\SalesRule\Model\Spi\CodeLimitManagerInterface;
use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\Captcha\Observer\CaptchaStringResolver as CaptchaResolver;
use Magento\Captcha\Model\DefaultModel as Captcha;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * @inheritDoc
 *
 * Implementation based on captcha.
 */
class CodeLimitManager implements CodeLimitManagerInterface
{
    /**
     * @var CouponRepositoryInterface
     */
    private $repository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;

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
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * Needed to avoid confusion in case of duplicate checks.
     *
     * Keys are codes, values are whether captcha was required the 1st time we checked the code.
     *
     * @var string[]
     */
    private $loggedFor = [];

    /**
     * Needed to avoid confusion in case of duplicate checks.
     *
     * @var bool[][]
     */
    private $checked = [];

    /**
     * @param CouponRepositoryInterface $repository
     * @param SearchCriteriaBuilder $criteriaBuilder
     * @param CaptchaHelper $captchaHelper
     * @param CaptchaResolver $captchaResolver
     * @param RequestInterface $request
     * @param CustomerSession $customerSession
     */
    public function __construct(
        CouponRepositoryInterface $repository,
        SearchCriteriaBuilder $criteriaBuilder,
        CaptchaHelper $captchaHelper,
        CaptchaResolver $captchaResolver,
        RequestInterface $request,
        CustomerSession $customerSession
    ) {
        $this->repository = $repository;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->captchaHelper = $captchaHelper;
        $this->captchaResolver = $captchaResolver;
        $this->request = $request;
        $this->customerSession = $customerSession;
    }

    /**
     * Check whether a valid code was requested.
     *
     * @param string $code
     * @return bool
     */
    private function checkCode(string $code): bool
    {
        $list = $this->repository->getList($this->criteriaBuilder->addFilter('code', $code)->create());

        return (bool)$list->getTotalCount();
    }

    /**
     * Get user's identifier.
     *
     * @return null|string
     */
    private function getLogin(): ?string
    {
        $login = null;
        if ($this->customerSession->isLoggedIn()) {
            $login = $this->customerSession->getCustomerData()->getEmail();
        }

        return $login;
    }

    /**
     * @inheritDoc
     */
    public function checkRequest(string $code): void
    {
        $formId = 'sales_rule_coupon_request';
        $login = $this->getLogin();
        /** @var Captcha $captcha */
        $captcha = $this->captchaHelper->getCaptcha($formId);
        //Avoid logging multiple times or recalculating $required when the same codes are checked.
        if (array_key_exists($code, $this->loggedFor)) {
            $required = $this->loggedFor[$code];
        } else {
            $required = $captcha->isRequired($login);
            if (!$this->checkCode($code)) {
                $captcha->logAttempt($login);
            }
            $this->loggedFor[$code] = $required;
        }

        $value = null;
        if ($required) {
            $valid = false;
            $value = $this->captchaResolver->resolve($this->request, $formId);
            if ($value) {
                if (array_key_exists($code, $this->checked) && array_key_exists($value, $this->checked[$code])) {
                    $valid = $this->checked[$code][$value];
                } else {
                    $valid = $captcha->isCorrect($value);
                    $this->checked[$code][$value] = $valid;
                }
            }
        } else {
            $valid = true;
        }

        if (!$valid) {
            if ($value) {
                $message = __('Incorrect CAPTCHA');
            } else {
                $message = __('Too many coupon code requests, please try again later.');
            }
            throw new CodeRequestLimitException($message);
        }
    }
}
