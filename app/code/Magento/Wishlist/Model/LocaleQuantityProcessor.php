<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Model;

/**
 * @api
 */
class LocaleQuantityProcessor
{
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var \Magento\Framework\Filter\LocalizedToNormalized
     */
    protected $localFilter;

    /**
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\Filter\LocalizedToNormalized $localFilter
     */
    public function __construct(
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Filter\LocalizedToNormalized $localFilter
    ) {
        $this->localeResolver = $localeResolver;
        $this->localFilter = $localFilter;
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
        $qty = $this->localFilter->filter((double)$qty);
        if ($qty < 0) {
            $qty = null;
        }
        return $qty;
    }
}
