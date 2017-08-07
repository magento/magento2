<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

use Magento\Framework\App\CacheInterface;

/**
 * Class \Magento\Framework\Currency
 *
 */
class Currency extends \Zend_Currency implements CurrencyInterface
{
    /**
     * Creates a currency instance.
     *
     * @param CacheInterface $appCache
     * @param string|array $options Options array or currency short name when string is given
     * @param string $locale Locale name
     * @since 2.1.0
     */
    public function __construct(
        CacheInterface $appCache,
        $options = null,
        $locale = null
    ) {
        // set Zend cache to low level frontend app cache
        $lowLevelFrontendCache = $appCache->getFrontend()->getLowLevelFrontend();
        \Zend_Currency::setCache($lowLevelFrontendCache);
        parent::__construct($options, $locale);
    }
}
