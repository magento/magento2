<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\CacheIdFactorProviders;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQlCache\Model\CacheId\CacheIdFactorProviderInterface;

/**
 * Provides customer group as a factor to use in the cache id
 */
class CustomerGroupProvider implements CacheIdFactorProviderInterface
{
    const NAME = "CUSTOMER_GROUP";

    /**
     * @inheritdoc
     */
    public function getFactorName(): string
    {
        return static::NAME;
    }

    /**
     * @inheritdoc
     */
    public function getFactorValue(ContextInterface $context): string
    {
        $customerGroupId = $context->getExtensionAttributes()->getCustomerGroupId() ?? GroupInterface::NOT_LOGGED_IN_ID;
        return (string)$customerGroupId;
    }
}
