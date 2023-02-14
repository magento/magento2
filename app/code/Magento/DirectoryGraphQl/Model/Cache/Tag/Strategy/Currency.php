<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DirectoryGraphQl\Model\Cache\Tag\Strategy;

use Magento\Directory\Model\Currency as CurrencyModel;
use Magento\DirectoryGraphQl\Model\Resolver\Currency\Identity;
use Magento\Framework\App\Cache\Tag\StrategyInterface;

/**
 * Produce cache tags for currency object.
 */
class Currency implements StrategyInterface
{
    /**
     * @inheritdoc
     */
    public function getTags($object): array
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException('Provided argument is not an object');
        }

        if ($object instanceof CurrencyModel) {
            return [Identity::CACHE_TAG];
        }

        return [];
    }
}
