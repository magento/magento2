<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver;

/**
 * IdentityInterface is responsible for generating the proper tags from a cache tag and resolved data.
 */
interface IdentityInterface
{

    /**
     * Get identity tags from resolved data.
     *
     * Example: identityTag, identityTag_UniqueId.
     *
     * @param array $resolvedData
     * @return string[]
     */
    public function getIdentities(array $resolvedData) : array;
}
