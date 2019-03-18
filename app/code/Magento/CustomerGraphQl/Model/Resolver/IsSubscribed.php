<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
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
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * @param GetCustomer $getCustomer
     * @param SubscriberFactory $subscriberFactory
     */
    public function __construct(
        GetCustomer $getCustomer,
        SubscriberFactory $subscriberFactory
    ) {
        $this->getCustomer = $getCustomer;
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
        $customer = $this->getCustomer->execute($context);

        $status = $this->subscriberFactory->create()->loadByCustomerId((int)$customer->getId())->isSubscribed();
        return (bool)$status;
    }
}
