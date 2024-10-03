<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NewsletterGraphQl\Model\Resolver;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\EnumLookup;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\NewsletterGraphQl\Model\SubscribeEmailToNewsletter\Validation;
use Psr\Log\LoggerInterface;

/**
 * Resolver class for the `subscribeEmailToNewsletter` mutation. Adds an email into a newsletter subscription.
 */
class SubscribeEmailToNewsletter implements ResolverInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var EnumLookup
     */
    private $enumLookup;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SubscriptionManagerInterface
     */
    private $subscriptionManager;

    /**
     * @var Validation
     */
    private $validator;

    /**
     * SubscribeEmailToNewsletter constructor.
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param EnumLookup $enumLookup
     * @param LoggerInterface $logger
     * @param SubscriptionManagerInterface $subscriptionManager
     * @param Validation $validator
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        EnumLookup $enumLookup,
        LoggerInterface $logger,
        SubscriptionManagerInterface $subscriptionManager,
        Validation $validator
    ) {
        $this->customerRepository = $customerRepository;
        $this->enumLookup = $enumLookup;
        $this->logger = $logger;
        $this->subscriptionManager = $subscriptionManager;
        $this->validator = $validator;
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $email = trim($args['email'] ?? '');

        if (empty($email)) {
            throw new GraphQlInputException(
                __('You must specify an email address to subscribe to a newsletter.')
            );
        }

        $currentUserId = (int)$context->getUserId();
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $websiteId = (int)$context->getExtensionAttributes()->getStore()->getWebsiteId();

        $this->validator->execute($email, $currentUserId, $websiteId);

        try {
            $subscriber = $this->isCustomerSubscription($email, $currentUserId)
                ? $this->subscriptionManager->subscribeCustomer($currentUserId, $storeId)
                : $this->subscriptionManager->subscribe($email, $storeId);

            $status = $this->enumLookup->getEnumValueFromField(
                'SubscriptionStatusesEnum',
                (string)$subscriber->getSubscriberStatus()
            );
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());

            throw new GraphQlInputException(
                __('Cannot create a newsletter subscription.')
            );
        }

        return [
            'status' => $status
        ];
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
