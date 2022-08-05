<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NewsletterGraphQl\Model\SubscribeEmailToNewsletter;

use Magento\Customer\Api\AccountManagementInterface as CustomerAccountManagement;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlAlreadyExistsException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Validator\EmailAddress as EmailValidator;
use Magento\Newsletter\Model\ResourceModel\Subscriber as SubscriberResourceModel;
use Magento\Newsletter\Model\Subscriber;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

/**
 * Validation class for the "subscribeEmailToNewsletter" mutation
 */
class Validation
{
    /**
     * @var CustomerAccountManagement
     */
    private $customerAccountManagement;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var EmailValidator
     */
    private $emailValidator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var SubscriberResourceModel
     */
    private $subscriberResource;

    /**
     * Validation constructor.
     *
     * @param CustomerAccountManagement $customerAccountManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param EmailValidator $emailValidator
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     * @param SubscriberResourceModel $subscriberResource
     */
    public function __construct(
        CustomerAccountManagement $customerAccountManagement,
        CustomerRepositoryInterface $customerRepository,
        EmailValidator $emailValidator,
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        SubscriberResourceModel $subscriberResource
    ) {
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerRepository = $customerRepository;
        $this->emailValidator = $emailValidator;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->subscriberResource = $subscriberResource;
    }

    /**
     * Validate the next cases:
     * - email format
     * - email address isn't being used by a different account
     * - if a guest user can be subscribed to a newsletter
     * - verify if email is already subscribed
     *
     * @param string $email
     * @param int $currentUserId
     * @param int $websiteId
     * @throws GraphQlAlreadyExistsException
     * @throws GraphQlInputException
     */
    public function execute(string $email = '', int $currentUserId = 0, int $websiteId = 1): void
    {
        $this->validateEmailFormat($email);

        if ($currentUserId > 0) {
            $this->validateEmailAvailable($email, $currentUserId, $websiteId);
        } else {
            $this->validateGuestSubscription();
        }

        $this->validateAlreadySubscribed($email, $websiteId);
    }

    /**
     * Validate the format of the email address
     *
     * @param string $email
     * @throws GraphQlInputException
     */
    private function validateEmailFormat(string $email): void
    {
        if (!$this->emailValidator->isValid($email)) {
            throw new GraphQlInputException(__('Enter a valid email address.'));
        }
    }

    /**
     * Validate that the email address isn't being used by a different account.
     *
     * @param string $email
     * @param int $currentUserId
     * @param int $websiteId
     * @throws GraphQlInputException
     */
    private function validateEmailAvailable(string $email, int $currentUserId, int $websiteId): void
    {
        try {
            $customer = $this->customerRepository->getById($currentUserId);
            $customerEmail = $customer->getEmail();
        } catch (LocalizedException $e) {
            $customerEmail = '';
        }

        try {
            $emailAvailable = $this->customerAccountManagement->isEmailAvailable($email, $websiteId);
        } catch (LocalizedException $e) {
            $emailAvailable = false;
        }

        if (!$emailAvailable && $customerEmail != $email) {
            $this->logger->error(
                __('This email address is already assigned to another user.')
            );

            throw new GraphQlInputException(
                __('Cannot create a newsletter subscription.')
            );
        }
    }

    /**
     * Validate if a guest user can be subscribed to a newsletter.
     *
     * @throws GraphQlInputException
     */
    private function validateGuestSubscription(): void
    {
        if (!$this->scopeConfig->getValue(
            Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG,
            ScopeInterface::SCOPE_STORE
        )) {
            throw new GraphQlInputException(
                __('Guests can not subscribe to the newsletter. You must create an account to subscribe.')
            );
        }
    }

    /**
     * Verify if email is already subscribed
     *
     * @param string $email
     * @param int $websiteId
     * @throws GraphQlAlreadyExistsException
     */
    private function validateAlreadySubscribed(string $email, int $websiteId): void
    {
        try {
            $subscriberData = $this->subscriberResource->loadBySubscriberEmail($email, $websiteId);
        } catch (LocalizedException $e) {
            $subscriberData = [];
        }

        if (isset($subscriberData['subscriber_status'])
            && (int)$subscriberData['subscriber_status'] === Subscriber::STATUS_SUBSCRIBED) {
            throw new GraphQlAlreadyExistsException(
                __('This email address is already subscribed.')
            );
        }
    }
}
