<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

use Magento\Framework\App\Cache\Type\Translate as TranslateCache;

class Currency extends \Zend_Currency implements CurrencyInterface
{
    /**
     * Creates a currency instance.
     *
     * @param TranslateCache $translateCache
     * @param string|array $options Options array or currency short name when string is given
     * @param string $locale Locale name
     */
    public function __construct(
        TranslateCache $translateCache,
        $options = null,
        $locale = null
    ) {
        // set Zend cache to low level frontend app cache
        $lowLevelFrontendCache = $translateCache->getLowLevelFrontend();
        \Zend_Currency::setCache($lowLevelFrontendCache);
        parent::__construct($options, $locale);
    }
}
