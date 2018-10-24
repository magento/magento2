<?php
/**
 * Preferences for classes like in di.xml (for integration tests)
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    \Magento\Framework\Stdlib\CookieManagerInterface::class => \Magento\TestFramework\CookieManager::class,
    \Magento\Framework\ObjectManager\DynamicConfigInterface::class =>
        \Magento\TestFramework\ObjectManager\Configurator::class,
    \Magento\Framework\App\RequestInterface::class => \Magento\TestFramework\Request::class,
    \Magento\Framework\App\Request\Http::class => \Magento\TestFramework\Request::class,
    \Magento\Framework\App\ResponseInterface::class => \Magento\TestFramework\Response::class,
    \Magento\Framework\App\Response\Http::class => \Magento\TestFramework\Response::class,
    \Magento\Framework\Interception\PluginListInterface::class =>
        \Magento\TestFramework\Interception\PluginList::class,
    \Magento\Framework\Interception\ObjectManager\ConfigInterface::class =>
        \Magento\TestFramework\ObjectManager\Config::class,
    \Magento\Framework\Interception\ObjectManager\Config\Developer::class =>
        \Magento\TestFramework\ObjectManager\Config::class,
    \Magento\Framework\View\LayoutInterface::class => \Magento\TestFramework\View\Layout::class,
    \Magento\Framework\App\ResourceConnection\ConnectionAdapterInterface::class =>
        \Magento\TestFramework\Db\ConnectionAdapter::class,
    \Magento\Framework\Filesystem\DriverInterface::class => \Magento\Framework\Filesystem\Driver\File::class,
    \Magento\Framework\App\Config\ScopeConfigInterface::class => \Magento\TestFramework\App\Config::class,
    \Magento\Framework\App\ResourceConnection\ConfigInterface::class =>
        \Magento\Framework\App\ResourceConnection\Config::class,
];
