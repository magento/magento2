<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DirectoryGraphQl\Model\Cache\Tag\Strategy;

use Magento\DirectoryGraphQl\Model\Resolver\Currency\Identity;
use Magento\Framework\App\Cache\Tag\StrategyInterface;
use Magento\Framework\App\Config\ValueInterface;

/**
 * Produce cache tags for currency config.
 */
class CurrencyConfig implements StrategyInterface
{
    /**
     * @var string[]
     */
    private $currencyConfigPaths = [
        'currency/options/base',
        'currency/options/default',
        'currency/options/allow',
        'currency/options/customsymbol'
    ];

    /**
     * @inheritdoc
     */
    public function getTags($object): array
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException('Provided argument is not an object');
        }

        if ($object instanceof ValueInterface
            && in_array($object->getPath(), $this->currencyConfigPaths)
            && $object->isValueChanged()
        ) {
            return [Identity::CACHE_TAG];
        }

        return [];
    }
}
