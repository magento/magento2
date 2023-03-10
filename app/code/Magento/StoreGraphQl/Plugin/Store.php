<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Plugin;

use Magento\Store\Model\Store as ModelStore;
use Magento\StoreGraphQl\Model\Resolver\Store\ConfigIdentity;

/**
 * Store plugin
 */
class Store
{
    /**
     * Add graphql store config tag to the store cache identities.
     *
     * @param ModelStore $subject
     * @param array $result
     * @return array
     */
    public function afterGetIdentities(ModelStore $subject, array $result): array
    {
        return array_merge($result, [sprintf('%s_%s', ConfigIdentity::CACHE_TAG, $subject->getId())]);
    }
}
