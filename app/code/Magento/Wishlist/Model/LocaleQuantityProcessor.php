<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Model;

use Magento\Framework\Filter\LocalizedToNormalized;
use Magento\Framework\Locale\ResolverInterface;

/**
 * @api
 * @since 100.0.2
 */
class LocaleQuantityProcessor
{
    /**
     * @param ResolverInterface $localeResolver
     * @param LocalizedToNormalized $localFilter
     */
    public function __construct(
        protected readonly ResolverInterface $localeResolver,
        protected readonly LocalizedToNormalized $localFilter
    ) {
    }

    /**
     * Process localized quantity to internal format
     *
     * @param float $qty
     * @return array|string
     */
    public function process($qty)
    {
        $this->localFilter->setOptions(['locale' => $this->localeResolver->getLocale()]);
        $qty = $this->localFilter->filter((string)$qty);
        if ($qty < 0) {
            $qty = null;
        }

        return $qty;
    }
}
