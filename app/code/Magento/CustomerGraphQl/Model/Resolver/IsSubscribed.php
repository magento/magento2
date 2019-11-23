<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
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
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * @param SubscriberFactory $subscriberFactory
     */
    public function __construct(
        SubscriberFactory $subscriberFactory
    ) {
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
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var Customer $customer */
        $customer = $value['model'];

        $status = $this->subscriberFactory->create()->loadByCustomerId((int)$customer->getId())->isSubscribed();
        return (bool)$status;
    }
}
