<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Log;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Logger abstract service factory.
 *
 * Allow to configure multiple loggers for application.
 */
class LoggerAbstractServiceFactory implements AbstractFactoryInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * Configuration key holding logger configuration
     *
     * @var string
     */
    protected $configKey = 'log';

    /**
     * @param  ServiceLocatorInterface $services
     * @param  string                  $name
     * @param  string                  $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        $config = $this->getConfig($services);
        if (empty($config)) {
            return false;
        }

        return isset($config[$requestedName]);
    }

    /**
     * @param  ServiceLocatorInterface $services
     * @param  string                  $name
     * @param  string                  $requestedName
     * @return Logger
     */
    public function createServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        $config  = $this->getConfig($services);
        $config  = $config[$requestedName];
        $this->processConfig($config, $services);
        return new Logger($config);
    }

    /**
     * Retrieve configuration for loggers, if any
     *
     * @param  ServiceLocatorInterface $services
     * @return array
     */
    protected function getConfig(ServiceLocatorInterface $services)
    {
        if ($this->config !== null) {
            return $this->config;
        }

        if (!$services->has('Config')) {
            $this->config = array();
            return $this->config;
        }

        $config = $services->get('Config');
        if (!isset($config[$this->configKey])) {
            $this->config = array();
            return $this->config;
        }

        $this->config = $config[$this->configKey];
        return $this->config;
    }

    protected function processConfig(&$config, ServiceLocatorInterface $services)
    {
        if (isset($config['writer_plugin_manager'])
            && is_string($config['writer_plugin_manager'])
            && $services->has($config['writer_plugin_manager'])
        ) {
            $config['writer_plugin_manager'] = $services->get($config['writer_plugin_manager']);
        }

        if ((!isset($config['writer_plugin_manager'])
            || ! $config['writer_plugin_manager'] instanceof AbstractPluginManager)
            && $services->has('LogWriterManager')
        ) {
            $config['writer_plugin_manager'] = $services->get('LogWriterManager');
        }

        if (isset($config['processor_plugin_manager'])
            && is_string($config['processor_plugin_manager'])
            && $services->has($config['processor_plugin_manager'])
        ) {
            $config['processor_plugin_manager'] = $services->get($config['processor_plugin_manager']);
        }

        if ((!isset($config['processor_plugin_manager'])
            || ! $config['processor_plugin_manager'] instanceof AbstractPluginManager)
            && $services->has('LogProcessorManager')
        ) {
            $config['processor_plugin_manager'] = $services->get('LogProcessorManager');
        }

        if (!isset($config['writers'])) {
            return;
        }

        foreach ($config['writers'] as $index => $writerConfig) {
            if (!isset($writerConfig['options']['db'])
                || !is_string($writerConfig['options']['db'])
            ) {
                continue;
            }
            if (!$services->has($writerConfig['options']['db'])) {
                continue;
            }

            // Retrieve the DB service from the service locator, and
            // inject it into the configuration.
            $db = $services->get($writerConfig['options']['db']);
            $config['writers'][$index]['options']['db'] = $db;
        }
    }
}
