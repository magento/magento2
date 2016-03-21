<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'Magento\Framework\Stdlib\CookieManagerInterface' => 'Magento\TestFramework\CookieManager',
    'Magento\Framework\ObjectManager\DynamicConfigInterface' =>
        '\Magento\TestFramework\ObjectManager\Configurator',
    'Magento\Framework\App\RequestInterface' => 'Magento\TestFramework\Request',
    'Magento\Framework\App\Request\Http' => 'Magento\TestFramework\Request',
    'Magento\Framework\App\ResponseInterface' => 'Magento\TestFramework\Response',
    'Magento\Framework\App\Response\Http' => 'Magento\TestFramework\Response',
    'Magento\Framework\Interception\PluginListInterface' =>
        'Magento\TestFramework\Interception\PluginList',
    'Magento\Framework\Interception\ObjectManager\Config\Developer' =>
        'Magento\TestFramework\ObjectManager\Config',
    'Magento\Framework\View\LayoutInterface' => 'Magento\TestFramework\View\Layout',
    'Magento\Framework\App\ResourceConnection\ConnectionAdapterInterface' =>
        'Magento\TestFramework\Db\ConnectionAdapter',
    'Magento\Framework\Filesystem\DriverInterface' => 'Magento\Framework\Filesystem\Driver\File'
];
