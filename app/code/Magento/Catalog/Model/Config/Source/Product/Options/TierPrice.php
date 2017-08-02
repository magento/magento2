<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Config\Source\Product\Options;

use Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface;

/**
 * TierPrice types mode source.
 * @since 2.2.0
 */
class TierPrice implements ProductPriceOptionsInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     * @since 2.2.0
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::VALUE_FIXED, 'label' => __('Fixed')],
            ['value' => self::VALUE_PERCENT, 'label' => __('Discount')],
        ];
    }
}
