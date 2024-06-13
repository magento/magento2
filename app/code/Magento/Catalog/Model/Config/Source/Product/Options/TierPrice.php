<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\Config\Source\Product\Options;

use Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface;

/**
 * TierPrice types mode source.
 */
class TierPrice implements ProductPriceOptionsInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::VALUE_FIXED, 'label' => __('Fixed')],
            ['value' => self::VALUE_PERCENT, 'label' => __('Discount')],
        ];
    }
}
