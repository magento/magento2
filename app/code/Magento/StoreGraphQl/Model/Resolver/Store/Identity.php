<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Model\Resolver\Store;

use Magento\Config\App\Config\Type\System;
use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

class Identity implements IdentityInterface
{
    /**
     * @var string
     */
    private $cacheTag = System::CACHE_TAG;

    /**
     * @inheritDoc
     */
    public function getIdentities(array $resolvedData): array
    {
        $ids =  empty($resolvedData) ?
            [] : [$this->cacheTag];
        return $ids;
    }
}
