<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SendFriend\Model;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Captcha\Helper\Data;
use Magento\Captcha\Model\DefaultModel;
use Magento\Captcha\Observer\CaptchaStringResolver;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Class CaptchaValidator. Performs captcha validation
 */
class CaptchaValidator
{
    /**
     * @var Data
     */
    private $captchaHelper;

    /**
     * @var CaptchaStringResolver
     */
    private $captchaStringResolver;

    /**
     * @var UserContextInterface
     */
    private $currentUser;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * CaptchaValidator constructor.
     *
     * @param Data $captchaHelper
     * @param CaptchaStringResolver $captchaStringResolver
     * @param UserContextInterface $currentUser
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Data $captchaHelper,
        CaptchaStringResolver $captchaStringResolver,
        UserContextInterface $currentUser,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->captchaHelper = $captchaHelper;
        $this->captchaStringResolver = $captchaStringResolver;
        $this->currentUser = $currentUser;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Entry point for captcha validation
     *
     * @param RequestInterface $request
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function validateSending(RequestInterface $request)
    {
        $this->validateCaptcha($request);
    }

    /**
     * Validates captcha and triggers log attempt
     *
     * @param RequestInterface $request
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function validateCaptcha(RequestInterface $request)
    {
        $captchaTargetFormName = 'product_sendtofriend_form';
        /** @var DefaultModel $captchaModel */
        $captchaModel = $this->captchaHelper->getCaptcha($captchaTargetFormName);

        if ($captchaModel->isRequired()) {
            $word = $this->captchaStringResolver->resolve(
                $request,
                $captchaTargetFormName
            );

            $isCorrectCaptcha = $captchaModel->isCorrect($word);

            if (!$isCorrectCaptcha) {
                $this->logCaptchaAttempt($captchaModel);
                throw new LocalizedException(__('Incorrect CAPTCHA'));
            }
        }

        $this->logCaptchaAttempt($captchaModel);
    }

    /**
     * Log captcha attempts
     *
     * @param DefaultModel $captchaModel
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function logCaptchaAttempt(DefaultModel $captchaModel)
    {
        $email = '';

        if ($this->currentUser->getUserType() == UserContextInterface::USER_TYPE_CUSTOMER) {
            $email = $this->customerRepository->getById($this->currentUser->getUserId())->getEmail();
        }

        $captchaModel->logAttempt($email);
    }
}
