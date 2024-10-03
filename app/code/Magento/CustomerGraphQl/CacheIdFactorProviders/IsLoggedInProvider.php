<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\CacheIdFactorProviders;

use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQlCache\Model\CacheId\CacheIdFactorProviderInterface;

/**
 * Provides logged-in status as a factor to use in the cache id
 */
class IsLoggedInProvider implements CacheIdFactorProviderInterface
{
    const NAME = "IS_LOGGED_IN";

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
        return $context->getExtensionAttributes()->getIsCustomer() ? "true" : "false";
    }
}
