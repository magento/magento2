<?php
/**
 * Preferences for classes like in di.xml (for integration tests)
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use \Magento\Framework\App;
use \Magento\Framework as MF;
use \Magento\TestFramework as TF;

return [
    MF\Stdlib\CookieManagerInterface::class => TF\CookieManager::class,
    MF\ObjectManager\DynamicConfigInterface::class => TF\ObjectManager\Configurator::class,
    App\RequestInterface::class => TF\Request::class,
    App\Request\Http::class => TF\Request::class,
    App\ResponseInterface::class => TF\Response::class,
    App\Response\Http::class => TF\Response::class,
    MF\Interception\PluginListInterface::class => TF\Interception\PluginList::class,
    MF\Interception\ObjectManager\ConfigInterface::class => TF\ObjectManager\Config::class,
    MF\Interception\ObjectManager\Config\Developer::class => TF\ObjectManager\Config::class,
    MF\View\LayoutInterface::class => TF\View\Layout::class,
    App\ResourceConnection\ConnectionAdapterInterface::class => TF\Db\ConnectionAdapter::class,
    MF\Filesystem\DriverInterface::class => MF\Filesystem\Driver\File::class,
    App\Config\ScopeConfigInterface::class => TF\App\Config::class,
    App\ResourceConnection\ConfigInterface::class => App\ResourceConnection\Config::class,
    MF\Lock\Backend\Database::class => TF\Lock\Backend\DummyLocker::class,
    MF\Session\SessionStartChecker::class => TF\Session\SessionStartChecker::class,
    MF\HTTP\AsyncClientInterface::class => TF\HTTP\AsyncClientInterfaceMock::class
];
