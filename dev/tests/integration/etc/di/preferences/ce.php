<?php
/**
 * Preferences for classes like in di.xml (for integration tests)
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Db\ConnectionAdapter;
use Magento\TestFramework\ObjectManager\Config;
use Magento\TestFramework\ObjectManager\Configurator;

return [
    \Magento\Framework\Stdlib\CookieManagerInterface::class => \Magento\TestFramework\CookieManager::class,
    \Magento\Framework\ObjectManager\DynamicConfigInterface::class => Configurator::class,
    \Magento\Framework\App\RequestInterface::class => \Magento\TestFramework\Request::class,
    \Magento\Framework\App\Request\Http::class => \Magento\TestFramework\Request::class,
    \Magento\Framework\App\ResponseInterface::class => \Magento\TestFramework\Response::class,
    \Magento\Framework\App\Response\Http::class => \Magento\TestFramework\Response::class,
    \Magento\Framework\Interception\PluginListInterface::class => \Magento\TestFramework\Interception\PluginList::class,
    \Magento\Framework\Interception\ObjectManager\Config\Developer::class => Config::class,
    \Magento\Framework\View\LayoutInterface::class => \Magento\TestFramework\View\Layout::class,
    \Magento\Framework\App\ResourceConnection\ConnectionAdapterInterface::class => ConnectionAdapter::class,
    \Magento\Framework\Filesystem\DriverInterface::class => \Magento\Framework\Filesystem\Driver\File::class,
    \Magento\Framework\App\Config\ScopeConfigInterface::class => \Magento\TestFramework\App\Config::class
];
