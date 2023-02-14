<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DirectoryGraphQl\Model\Cache\Tag\Strategy;

use Magento\CurrencySymbol\Model\System\Currencysymbol;
use Magento\Directory\Model\Currency;
use Magento\DirectoryGraphQl\Model\Resolver\Currency\Identity;
use Magento\Framework\App\Cache\Tag\StrategyInterface;
use Magento\Framework\App\Config\ValueInterface;

/**
 * Produce cache tags for currency config.
 */
class CurrencyConfig implements StrategyInterface
{
    private $currencyConfigPaths = [
        Currency::XML_PATH_CURRENCY_BASE,
        Currency::XML_PATH_CURRENCY_DEFAULT,
        Currency::XML_PATH_CURRENCY_ALLOW,
        Currencysymbol::XML_PATH_CUSTOM_CURRENCY_SYMBOL
    ];

    /**
     * @inheritdoc
     */
    public function getTags($object): array
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException('Provided argument is not an object');
        }

        if (
            $object instanceof ValueInterface
            && in_array($object->getPath(), $this->currencyConfigPaths)
            && $object->isValueChanged()
        ) {
            return [Identity::CACHE_TAG];
        }

        return [];
    }
}
