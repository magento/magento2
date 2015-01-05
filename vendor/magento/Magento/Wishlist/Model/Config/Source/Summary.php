<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Wishlist\Model\Config\Source;

class Summary implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Display number of items in wish list')],
            ['value' => 1, 'label' => __('Display item quantities')]
        ];
    }
}
