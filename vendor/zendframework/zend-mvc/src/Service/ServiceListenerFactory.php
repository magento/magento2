<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Service;

use Zend\ModuleManager\Listener\ServiceListener;
use Zend\ModuleManager\Listener\ServiceListenerInterface;
use Zend\Mvc\Exception\InvalidArgumentException;
use Zend\Mvc\Exception\RuntimeException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ServiceListenerFactory implements FactoryInterface
{
    /**
     * @var string
     */
    const MISSING_KEY_ERROR = 'Invalid service listener options detected, %s array must contain %s key.';

    /**
     * @var string
     */
    const VALUE_TYPE_ERROR = 'Invalid service listener options detected, %s must be a string, %s given.';

    /**
     * Default mvc-related service configuration -- can be overridden by modules.
     *
     * @var array
     */
    protected $defaultServiceConfig = array(
        'invokables' => array(
            'DispatchListener'     => 'Zend\Mvc\DispatchListener',
            'RouteListener'        => 'Zend\Mvc\RouteListener',
            'SendResponseListener' => 'Zend\Mvc\SendResponseListener',
            'ViewJsonRenderer'     => 'Zend\View\Renderer\JsonRenderer',
            'ViewFeedRenderer'     => 'Zend\View\Renderer\FeedRenderer',
        ),
        'factories' => array(
            'Application'                    => 'Zend\Mvc\Service\ApplicationFactory',
            'Config'                         => 'Zend\Mvc\Service\ConfigFactory',
            'ControllerLoader'               => 'Zend\Mvc\Service\ControllerLoaderFactory',
            'ControllerPluginManager'        => 'Zend\Mvc\Service\ControllerPluginManagerFactory',
            'ConsoleAdapter'                 => 'Zend\Mvc\Service\ConsoleAdapterFactory',
            'ConsoleRouter'                  => 'Zend\Mvc\Service\RouterFactory',
            'ConsoleViewManager'             => 'Zend\Mvc\Service\ConsoleViewManagerFactory',
            'DependencyInjector'             => 'Zend\Mvc\Service\DiFactory',
            'DiAbstractServiceFactory'       => 'Zend\Mvc\Service\DiAbstractServiceFactoryFactory',
            'DiServiceInitializer'           => 'Zend\Mvc\Service\DiServiceInitializerFactory',
            'DiStrictAbstractServiceFactory' => 'Zend\Mvc\Service\DiStrictAbstractServiceFactoryFactory',
            'FilterManager'                  => 'Zend\Mvc\Service\FilterManagerFactory',
            'FormAnnotationBuilder'          => 'Zend\Mvc\Service\FormAnnotationBuilderFactory',
            'FormElementManager'             => 'Zend\Mvc\Service\FormElementManagerFactory',
            'HttpRouter'                     => 'Zend\Mvc\Service\RouterFactory',
            'HttpMethodListener'             => 'Zend\Mvc\Service\HttpMethodListenerFactory',
            'HttpViewManager'                => 'Zend\Mvc\Service\HttpViewManagerFactory',
            'HydratorManager'                => 'Zend\Mvc\Service\HydratorManagerFactory',
            'InjectTemplateListener'         => 'Zend\Mvc\Service\InjectTemplateListenerFactory',
            'InputFilterManager'             => 'Zend\Mvc\Service\InputFilterManagerFactory',
            'LogProcessorManager'            => 'Zend\Mvc\Service\LogProcessorManagerFactory',
            'LogWriterManager'               => 'Zend\Mvc\Service\LogWriterManagerFactory',
            'MvcTranslator'                  => 'Zend\Mvc\Service\TranslatorServiceFactory',
            'PaginatorPluginManager'         => 'Zend\Mvc\Service\PaginatorPluginManagerFactory',
            'Request'                        => 'Zend\Mvc\Service\RequestFactory',
            'Response'                       => 'Zend\Mvc\Service\ResponseFactory',
            'Router'                         => 'Zend\Mvc\Service\RouterFactory',
            'RoutePluginManager'             => 'Zend\Mvc\Service\RoutePluginManagerFactory',
            'SerializerAdapterManager'       => 'Zend\Mvc\Service\SerializerAdapterPluginManagerFactory',
            'TranslatorPluginManager'        => 'Zend\Mvc\Service\TranslatorPluginManagerFactory',
            'ValidatorManager'               => 'Zend\Mvc\Service\ValidatorManagerFactory',
            'ViewHelperManager'              => 'Zend\Mvc\Service\ViewHelperManagerFactory',
            'ViewFeedStrategy'               => 'Zend\Mvc\Service\ViewFeedStrategyFactory',
            'ViewJsonStrategy'               => 'Zend\Mvc\Service\ViewJsonStrategyFactory',
            'ViewManager'                    => 'Zend\Mvc\Service\ViewManagerFactory',
            'ViewResolver'                   => 'Zend\Mvc\Service\ViewResolverFactory',
            'ViewTemplateMapResolver'        => 'Zend\Mvc\Service\ViewTemplateMapResolverFactory',
            'ViewTemplatePathStack'          => 'Zend\Mvc\Service\ViewTemplatePathStackFactory',
            'ViewPrefixPathStackResolver'    => 'Zend\Mvc\Service\ViewPrefixPathStackResolverFactory',
        ),
        'aliases' => array(
            'Configuration'                              => 'Config',
            'Console'                                    => 'ConsoleAdapter',
            'Di'                                         => 'DependencyInjector',
            'Zend\Di\LocatorInterface'                   => 'DependencyInjector',
            'Zend\Form\Annotation\FormAnnotationBuilder' => 'FormAnnotationBuilder',
            'Zend\Mvc\Controller\PluginManager'          => 'ControllerPluginManager',
            'Zend\Mvc\View\Http\InjectTemplateListener'  => 'InjectTemplateListener',
            'Zend\View\Resolver\TemplateMapResolver'     => 'ViewTemplateMapResolver',
            'Zend\View\Resolver\TemplatePathStack'       => 'ViewTemplatePathStack',
            'Zend\View\Resolver\AggregateResolver'       => 'ViewResolver',
            'Zend\View\Resolver\ResolverInterface'       => 'ViewResolver',
            'ControllerManager'                          => 'ControllerLoader',
        ),
        'abstract_factories' => array(
            'Zend\Form\FormAbstractServiceFactory',
        ),
    );

    /**
     * Create the service listener service
     *
     * Tries to get a service named ServiceListenerInterface from the service
     * locator, otherwise creates a Zend\ModuleManager\Listener\ServiceListener
     * service, passing it the service locator instance and the default service
     * configuration, which can be overridden by modules.
     *
     * It looks for the 'service_listener_options' key in the application
     * config and tries to add service manager as configured. The value of
     * 'service_listener_options' must be a list (array) which contains the
     * following keys:
     *   - service_manager: the name of the service manage to create as string
     *   - config_key: the name of the configuration key to search for as string
     *   - interface: the name of the interface that modules can implement as string
     *   - method: the name of the method that modules have to implement as string
     *
     * @param  ServiceLocatorInterface  $serviceLocator
     * @return ServiceListener
     * @throws InvalidArgumentException For invalid configurations.
     * @throws RuntimeException
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $configuration   = $serviceLocator->get('ApplicationConfig');

        if ($serviceLocator->has('ServiceListenerInterface')) {
            $serviceListener = $serviceLocator->get('ServiceListenerInterface');

            if (!$serviceListener instanceof ServiceListenerInterface) {
                throw new RuntimeException(
                    'The service named ServiceListenerInterface must implement ' .
                    'Zend\ModuleManager\Listener\ServiceListenerInterface'
                );
            }

            $serviceListener->setDefaultServiceConfig($this->defaultServiceConfig);
        } else {
            $serviceListener = new ServiceListener($serviceLocator, $this->defaultServiceConfig);
        }

        if (isset($configuration['service_listener_options'])) {
            if (!is_array($configuration['service_listener_options'])) {
                throw new InvalidArgumentException(sprintf(
                    'The value of service_listener_options must be an array, %s given.',
                    gettype($configuration['service_listener_options'])
                ));
            }

            foreach ($configuration['service_listener_options'] as $key => $newServiceManager) {
                if (!isset($newServiceManager['service_manager'])) {
                    throw new InvalidArgumentException(sprintf(self::MISSING_KEY_ERROR, $key, 'service_manager'));
                } elseif (!is_string($newServiceManager['service_manager'])) {
                    throw new InvalidArgumentException(sprintf(
                        self::VALUE_TYPE_ERROR,
                        'service_manager',
                        gettype($newServiceManager['service_manager'])
                    ));
                }
                if (!isset($newServiceManager['config_key'])) {
                    throw new InvalidArgumentException(sprintf(self::MISSING_KEY_ERROR, $key, 'config_key'));
                } elseif (!is_string($newServiceManager['config_key'])) {
                    throw new InvalidArgumentException(sprintf(
                        self::VALUE_TYPE_ERROR,
                        'config_key',
                        gettype($newServiceManager['config_key'])
                    ));
                }
                if (!isset($newServiceManager['interface'])) {
                    throw new InvalidArgumentException(sprintf(self::MISSING_KEY_ERROR, $key, 'interface'));
                } elseif (!is_string($newServiceManager['interface'])) {
                    throw new InvalidArgumentException(sprintf(
                        self::VALUE_TYPE_ERROR,
                        'interface',
                        gettype($newServiceManager['interface'])
                    ));
                }
                if (!isset($newServiceManager['method'])) {
                    throw new InvalidArgumentException(sprintf(self::MISSING_KEY_ERROR, $key, 'method'));
                } elseif (!is_string($newServiceManager['method'])) {
                    throw new InvalidArgumentException(sprintf(
                        self::VALUE_TYPE_ERROR,
                        'method',
                        gettype($newServiceManager['method'])
                    ));
                }

                $serviceListener->addServiceManager(
                    $newServiceManager['service_manager'],
                    $newServiceManager['config_key'],
                    $newServiceManager['interface'],
                    $newServiceManager['method']
                );
            }
        }

        return $serviceListener;
    }
}
