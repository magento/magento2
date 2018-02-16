<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup;

use Magento\Framework\App\Response\HeaderProvider\XssProtection;
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
                $headers->addHeaderLine('X-Frame-Options: SAMEORIGIN');
                $headers->addHeaderLine('X-Content-Type-Options: nosniff');
                $xssHeaderValue = strpos($_SERVER['HTTP_USER_AGENT'], XssProtection::IE_8_USER_AGENT) === false
                    ? XssProtection::HEADER_ENABLED : XssProtection::HEADER_DISABLED;
                $headers->addHeaderLine('X-XSS-Protection: ' . $xssHeaderValue);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $result = array_merge_recursive(
            include __DIR__ . '/../../../config/module.config.php',
            include __DIR__ . '/../../../config/router.config.php',
            include __DIR__ . '/../../../config/di.config.php',
            include __DIR__ . '/../../../config/states.install.config.php',
            include __DIR__ . '/../../../config/states.update.config.php',
            include __DIR__ . '/../../../config/states.home.config.php',
            include __DIR__ . '/../../../config/states.extensionManager.config.php',
            include __DIR__ . '/../../../config/states.upgrade.config.php',
            include __DIR__ . '/../../../config/states.uninstall.config.php',
            include __DIR__ . '/../../../config/states.enable.config.php',
            include __DIR__ . '/../../../config/states.disable.config.php',
            include __DIR__ . '/../../../config/languages.config.php',
            include __DIR__ . '/../../../config/marketplace.config.php'
        );
        return $result;
    }
}
