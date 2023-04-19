<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Cache\KeyFactorProvider;

use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQlCache\Model\CacheId\CacheIdFactorProviderInterface;

/**
 * Id factor provider interface for resolver cache that depends on parent resolver data.
 */
interface ParentResolverResultFactoredInterface extends CacheIdFactorProviderInterface
{
    /**
     * Provides factor value based on query context and parent resolver data.
     *
     * @param ContextInterface $context
     * @param array|null $parentResolverData
     * @return string
     */
    public function getFactorValueForParentResolvedData(ContextInterface $context, ?array $parentResolverData): string;
}
