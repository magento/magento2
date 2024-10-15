<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Currency\Data\Currency as CurrencyData;
use Magento\Framework\Currency\Exception\CurrencyException;

class Currency extends CurrencyData implements CurrencyInterface
{
    /**
     * Creates a currency instance.
     *
     * @param CacheInterface $appCache
     * @param array|string|null $options Options array or currency short name when string is given
     * @param string|null $locale Locale name
     * @throws CurrencyException
     */
    public function __construct(
        CacheInterface $appCache,
        $options = null,
        $locale = null
    ) {
        $lowLevelFrontendCache = $appCache->getFrontend()->getLowLevelFrontend();
        self::setCache($lowLevelFrontendCache);
        parent::__construct($options, $locale);
    }
}
