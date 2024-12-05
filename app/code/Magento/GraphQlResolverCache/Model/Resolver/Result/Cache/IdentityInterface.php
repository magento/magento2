<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result\Cache;

/**
 * Resolver cache identity interface.
 */
interface IdentityInterface
{

    /**
     * Get identity tags from resolved and parent resolver result data.
     *
     * Example: identityTag, identityTag_UniqueId.
     *
     * @param mixed $resolvedData
     * @param array|null $parentResolvedData
     * @return string[]
     */
    public function getIdentities($resolvedData, ?array $parentResolvedData = null): array;
}
