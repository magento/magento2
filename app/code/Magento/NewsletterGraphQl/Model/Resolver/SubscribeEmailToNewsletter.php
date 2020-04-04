<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NewsletterGraphQl\Model\Resolver;

use Magento\Customer\Api\AccountManagementInterface as CustomerAccountManagement;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAlreadyExistsException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\EnumLookup;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Validator\EmailAddress as EmailValidator;
use Magento\Newsletter\Model\ResourceModel\Subscriber as SubscriberResourceModel;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Resolver class for the `subscribeEmailToNewsletter` mutation. Adds an email into a newsletter subscription.
 */
class SubscribeEmailToNewsletter implements ResolverInterface
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
     * @var EnumLookup
     */
    private $enumLookup;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var SubscriberResourceModel
     */
    private $subscriberResource;

    /**
     * @var SubscriptionManagerInterface
     */
    private $subscriptionManager;

    /**
     * SubscribeEmailToNewsletter constructor.
     *
     * @param CustomerAccountManagement $customerAccountManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param EmailValidator $emailValidator
     * @param EnumLookup $enumLookup
     * @param ScopeConfigInterface $scopeConfig
     * @param SubscriberResourceModel $subscriberResource
     * @param SubscriptionManagerInterface $subscriptionManager
     */
    public function __construct(
        CustomerAccountManagement $customerAccountManagement,
        CustomerRepositoryInterface $customerRepository,
        EmailValidator $emailValidator,
        EnumLookup $enumLookup,
        ScopeConfigInterface $scopeConfig,
        SubscriberResourceModel $subscriberResource,
        SubscriptionManagerInterface $subscriptionManager
    ) {
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerRepository = $customerRepository;
        $this->emailValidator = $emailValidator;
        $this->enumLookup = $enumLookup;
        $this->scopeConfig = $scopeConfig;
        $this->subscriberResource = $subscriberResource;
        $this->subscriptionManager = $subscriptionManager;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $email = trim($args['email']);

        if (empty($email)) {
            throw new GraphQlInputException(__('You must specify an email address to subscribe to a newsletter.'));
        }

        try {
            $currentUserId = (int)$context->getUserId();
            $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
            $websiteId = (int)$context->getExtensionAttributes()->getStore()->getWebsiteId();

            $this->validateEmailFormat($email);
            $this->validateGuestSubscription($context);
            $this->validateEmailAvailable($email, $currentUserId, $websiteId);
            $this->validateAlreadySubscribed($email, $websiteId);

            $subscriber = $this->isCustomerSubscription($email, $currentUserId)
                ? $this->subscriptionManager->subscribeCustomer($currentUserId, $storeId)
                : $this->subscriptionManager->subscribe($email, $storeId);

            $status = $this->enumLookup->getEnumValueFromField(
                'SubscriptionStatusesEnum',
                (string)$subscriber->getSubscriberStatus()
            );
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        return [
            'status' => $status
        ];
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
     * Validate if a guest user can be subscribed to a newsletter.
     *
     * @param ContextInterface $context
     * @throws GraphQlInputException
     */
    private function validateGuestSubscription(ContextInterface $context): void
    {
        if (false === $context->getExtensionAttributes()->getIsCustomer()
            && !$this->scopeConfig->getValue(
                Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG,
                ScopeInterface::SCOPE_STORE
            )
        ) {
            throw new GraphQlInputException(
                __('Guests can not subscribe to the newsletter. You must create an account to subscribe.')
            );
        }
    }

    /**
     * Validates that the email address isn't being used by a different account.
     *
     * @param string $email
     * @param int $currentUserId
     * @param int $websiteId
     * @throws GraphQlInputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function validateEmailAvailable(string $email, int $currentUserId, int $websiteId): void
    {
        if ($currentUserId > 0) {
            $customer = $this->customerRepository->getById($currentUserId);

            if ($customer->getEmail() != $email
                && !$this->customerAccountManagement->isEmailAvailable($email, $websiteId)) {
                throw new GraphQlInputException(
                    __('This email address is already assigned to another user.')
                );
            }
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
        $subscriberData = $this->subscriberResource->loadBySubscriberEmail($email, $websiteId);

        if (isset($subscriberData['subscriber_status'])
            && (int)$subscriberData['subscriber_status'] === Subscriber::STATUS_SUBSCRIBED) {
            throw new GraphQlAlreadyExistsException(
                __('This email address is already subscribed.')
            );
        }
    }

    /**
     * Returns true if a provided email equals to a current customer one
     *
     * @param string $email
     * @param int $currentUserId
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function isCustomerSubscription(string $email, int $currentUserId): bool
    {
        if ($currentUserId > 0) {
            $customer = $this->customerRepository->getById($currentUserId);

            if ($customer->getEmail() == $email) {
                return true;
            }
        }

        return false;
    }
}
