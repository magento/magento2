<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Model\Source;

/**
 * Google Data Api authorization types Source
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Authtype implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Retrieve option array with authentification types
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'authsub', 'label' => __('AuthSub')],
            ['value' => 'clientlogin', 'label' => __('ClientLogin')]
        ];
    }
}
