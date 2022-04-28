<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Model\Resolver\Store;

use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;
use Magento\Framework\App\Config;

class Identity implements IdentityInterface
{
    /**
     * @var string
     */
    private $cacheTag = Config::CACHE_TAG;

    /**
     * @inheritDoc
     */
    public function getIdentities(array $resolvedData): array
    {
        $data["id"] =  empty($resolvedData) ? [] : $resolvedData["id"];
        $ids =  empty($resolvedData) ?
            [] : array_merge([$this->cacheTag], array_map(function ($key) {
                return sprintf('%s_%s', $this->cacheTag, $key);
            }, $data));
        return $ids;
    }
}
