<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result\ValueProcessor\FlagGetter;

/**
 * Get flag from value.
 */
interface FlagGetterInterface
{
    /**
     * Get value processing flag.
     *
     * @param array $value
     * @return array|null
     */
    public function getFlagFromValue($value): ?array;
}
