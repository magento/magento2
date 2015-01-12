<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Model\Source;

/**
 * Google Data Api account types Source
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Accounttype implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Retrieve option array with account types
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'HOSTED_OR_GOOGLE', 'label' => __('Hosted or Google')],
            ['value' => 'GOOGLE', 'label' => __('Google')],
            ['value' => 'HOSTED', 'label' => __('Hosted')]
        ];
    }
}
