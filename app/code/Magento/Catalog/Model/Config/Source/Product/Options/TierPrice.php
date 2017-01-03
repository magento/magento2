<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
