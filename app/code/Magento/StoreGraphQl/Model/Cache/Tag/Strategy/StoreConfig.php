<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Model\Cache\Tag\Strategy;

use Magento\Framework\App\Cache\Tag\StrategyInterface;
use Magento\Framework\App\Config\ValueInterface;
use Magento\StoreGraphQl\Model\Resolver\Store\ConfigIdentity;

/**
 * Produce cache tags for store config.
 */
class StoreConfig implements StrategyInterface
{
    /**
     * @inheritdoc
     */
    public function getTags($object): array
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException('Provided argument is not an object');
        }

        if ($object instanceof ValueInterface && $object->isValueChanged()) {
            return [ConfigIdentity::CACHE_TAG];
        }

        return [];
    }
}
