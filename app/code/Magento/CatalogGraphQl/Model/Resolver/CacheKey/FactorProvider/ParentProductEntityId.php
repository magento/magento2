<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\CacheKey\FactorProvider;

use Magento\Framework\Model\AbstractModel;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\ParentValueFactorProviderInterface;

/**
 * Provides product id from the model object in the parent resolved value
 * as a factor to use in the cache key for resolver cache
 */
class ParentProductEntityId implements ParentValueFactorProviderInterface
{
    /**
     * Factor name.
     */
    private const NAME = "PARENT_ENTITY_PRODUCT_ID";

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
        if (array_key_exists('model_info', $parentValue)
            && array_key_exists('model_id', $parentValue['model_info'])) {
            return (string)$parentValue['model_info']['model_id'];
        } elseif (array_key_exists('model', $parentValue) && $parentValue['model'] instanceof AbstractModel) {
            return (string)$parentValue['model']->getId();
        }
        throw new \InvalidArgumentException(__CLASS__ . " factor provider requires parent value " .
            "to contain product model id or product model.");
    }

    /**
     * @inheritDoc
     */
    public function isRequiredOrigData(): bool
    {
        return false;
    }
}
