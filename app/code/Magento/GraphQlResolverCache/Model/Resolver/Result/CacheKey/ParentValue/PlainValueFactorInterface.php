<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\ParentValue;

use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\GenericFactorInterface;

/**
 * Interface for key factors that are used to calculate the resolver cache key.
 */
interface PlainValueFactorInterface extends GenericFactorInterface
{
    /**
     * Returns the runtime value that should be used as factor.
     *
     * @param ContextInterface $context
     * @param array|null $plainParentValue
     * @return string
     */
    public function getFactorValue(ContextInterface $context, array $plainParentValue = null): string;
}
