<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Config\Source\Product\Options;

/**
 * Price types mode source
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Price implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'fixed', 'label' => __('Fixed')],
            ['value' => 'percent', 'label' => __('Percent')]
        ];
    }
}
