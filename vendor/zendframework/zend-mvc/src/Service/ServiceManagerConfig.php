<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Service;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\Stdlib\ArrayUtils;

class ServiceManagerConfig extends Config
{
    /**
     * Services that can be instantiated without factories
     *
     * @var array
     */
    protected $invokables = array(
        'SharedEventManager' => 'Zend\EventManager\SharedEventManager',
    );

    /**
     * Service factories
     *
     * @var array
     */
    protected $factories = array(
        'EventManager'  => 'Zend\Mvc\Service\EventManagerFactory',
        'ModuleManager' => 'Zend\Mvc\Service\ModuleManagerFactory',
    );

    /**
     * Abstract factories
     *
     * @var array
     */
    protected $abstractFactories = array();

    /**
     * Aliases
     *
     * @var array
     */
    protected $aliases = array(
        'Zend\EventManager\EventManagerInterface'     => 'EventManager',
        'Zend\ServiceManager\ServiceLocatorInterface' => 'ServiceManager',
        'Zend\ServiceManager\ServiceManager'          => 'ServiceManager',
    );

    /**
     * Shared services
     *
     * Services are shared by default; this is primarily to indicate services
     * that should NOT be shared
     *
     * @var array
     */
    protected $shared = array(
        'EventManager' => false,
    );

    /**
     * Delegators
     *
     * @var array
     */
    protected $delegators = array();

    /**
     * Initializers
     *
     * @var array
     */
    protected $initializers = array();

    /**
     * Constructor
     *
     * Merges internal arrays with those passed via configuration
     *
     * @param  array $configuration
     */
    public function __construct(array $configuration = array())
    {
        $this->initializers = array(
            'EventManagerAwareInitializer' => function ($instance, ServiceLocatorInterface $serviceLocator) {
                if ($instance instanceof EventManagerAwareInterface) {
                    $eventManager = $instance->getEventManager();

                    if ($eventManager instanceof EventManagerInterface) {
                        $eventManager->setSharedManager($serviceLocator->get('SharedEventManager'));
                    } else {
                        $instance->setEventManager($serviceLocator->get('EventManager'));
                    }
                }
            },
            'ServiceManagerAwareInitializer' => function ($instance, ServiceLocatorInterface $serviceLocator) {
                if ($serviceLocator instanceof ServiceManager && $instance instanceof ServiceManagerAwareInterface) {
                    $instance->setServiceManager($serviceLocator);
                }
            },
            'ServiceLocatorAwareInitializer' => function ($instance, ServiceLocatorInterface $serviceLocator) {
                if ($instance instanceof ServiceLocatorAwareInterface) {
                    $instance->setServiceLocator($serviceLocator);
                }
            },
        );

        $this->factories['ServiceManager'] = function (ServiceLocatorInterface $serviceLocator) {
            return $serviceLocator;
        };

        parent::__construct(ArrayUtils::merge(
            array(
                'invokables'         => $this->invokables,
                'factories'          => $this->factories,
                'abstract_factories' => $this->abstractFactories,
                'aliases'            => $this->aliases,
                'shared'             => $this->shared,
                'delegators'         => $this->delegators,
                'initializers'       => $this->initializers,
            ),
            $configuration
        ));
    }
}
