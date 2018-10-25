<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\CheckCustomerAccount;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Newsletter\Model\SubscriberFactory;

/**
 * Customer is_subscribed field resolver
 */
class IsSubscribed implements ResolverInterface
{
    /**
     * @var CheckCustomerAccount
     */
    private $checkCustomerAccount;

    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * @param CheckCustomerAccount $checkCustomerAccount
     * @param SubscriberFactory $subscriberFactory
     */
    public function __construct(
        CheckCustomerAccount $checkCustomerAccount,
        SubscriberFactory $subscriberFactory
    ) {
        $this->checkCustomerAccount = $checkCustomerAccount;
        $this->subscriberFactory = $subscriberFactory;
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
        $currentUserId = $context->getUserId();
        $currentUserType = $context->getUserType();

        $this->checkCustomerAccount->execute($currentUserId, $currentUserType);

        $status = $this->subscriberFactory->create()->loadByCustomerId((int)$currentUserId)->isSubscribed();
        return (bool)$status;
    }
}
