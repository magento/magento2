<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup;

use Magento\Setup\Mvc\Bootstrap\InitParamListener;
use Magento\Setup\Mvc\View\Http\InjectTemplateListener;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module implements
    BootstrapListenerInterface,
    ConfigProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function onBootstrap(EventInterface $e)
    {
        /** @var \Zend\Mvc\MvcEvent $e */
        /** @var \Zend\Mvc\Application $application */
        $application = $e->getApplication();
        /** @var \Zend\EventManager\EventManager $events */
        $events = $application->getEventManager();
        /** @var \Zend\EventManager\SharedEventManager $sharedEvents */
        $sharedEvents = $events->getSharedManager();

        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($events);

        // Override Zend\Mvc\View\Http\InjectTemplateListener
        // to process templates by Vendor/Module
        $injectTemplateListener = new InjectTemplateListener();
        $sharedEvents->attach(
            'Zend\Stdlib\DispatchableInterface',
            MvcEvent::EVENT_DISPATCH,
            [$injectTemplateListener, 'injectTemplate'],
            -89
        );
        $response = $e->getResponse();
        if ($response instanceof \Zend\Http\Response) {
            $headers = $response->getHeaders();
            if ($headers) {
                $headers->addHeaderLine('Cache-Control', 'no-cache, no-store, must-revalidate');
                $headers->addHeaderLine('Pragma', 'no-cache');
                $headers->addHeaderLine('Expires', '1970-01-01');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $result = array_merge(
            include __DIR__ . '/config/module.config.php',
            include __DIR__ . '/config/router.config.php',
            include __DIR__ . '/config/di.config.php',
            include __DIR__ . '/config/states.config.php',
            include __DIR__ . '/config/languages.config.php'
        );
        $result = InitParamListener::attachToConsoleRoutes($result);
        return $result;
    }
}
