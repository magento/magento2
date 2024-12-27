<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NewsletterGraphQl\Model\Resolver\CacheKey\FactorProvider;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\ParentValueFactorProviderInterface;

/**
 * Provides customer id from the parent resolved value as a factor to use in the cache key for resolver cache.
 */
class ParentCustomerEntityId implements ParentValueFactorProviderInterface
{
    /**
     * Factor name.
     */
    private const NAME = "PARENT_ENTITY_CUSTOMER_ID";

    /**
     * @inheritdoc
     */
    public function getFactorName(): string
    {
        return static::NAME;
    }

    /**
     * @inheritDoc
     */
    public function getFactorValue(ContextInterface $context, array $parentValue): string
    {
        if (isset($parentValue['model_id'])) {
            return (string)$parentValue['model_id'];
        } elseif (isset($parentValue['model']) && $parentValue['model'] instanceof CustomerInterface) {
            return (string)$parentValue['model']->getId();
        }
        throw new \InvalidArgumentException(__CLASS__ . " factor provider requires parent value " .
            "to contain customer model id or customer model.");
    }

    /**
     * @inheritDoc
     */
    public function isRequiredOrigData(): bool
    {
        return false;
    }
}
