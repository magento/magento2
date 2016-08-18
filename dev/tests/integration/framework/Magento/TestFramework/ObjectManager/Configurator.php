<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\ObjectManager;

class Configurator implements \Magento\Framework\ObjectManager\DynamicConfigInterface
{
    /**
     * Map application initialization params to Object Manager configuration format
     *
     * @return array
     */
    public function getConfiguration()
    {
        return [
            'preferences' => [
                \Magento\Framework\Stdlib\CookieManagerInterface::class => \Magento\TestFramework\CookieManager::class,
                \Magento\Store\Model\StoreManagerInterface::class => \Magento\TestFramework\Store\StoreManager::class,
            ]
        ];
    }
}
