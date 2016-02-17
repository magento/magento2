<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Service;

use stdClass;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\Console;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ConsoleAdapterFactory implements FactoryInterface
{
    /**
     * Create and return a Console adapter instance.
     * In case we're not in a Console environment, return a dummy stdClass object.
     *
     * In order to disable adapter auto-detection and use a specific adapter (and charset),
     * add the following fields to application configuration, for example:
     *
     *     'console' => array(
     *         'adapter' => 'MyConsoleAdapter',     // always use this console adapter
     *         'charset' => 'MyConsoleCharset',     // always use this console charset
     *      ),
     *      'service_manager' => array(
     *          'invokables' => array(
     *              'MyConsoleAdapter' => 'Zend\Console\Adapter\Windows',
     *              'MyConsoleCharset' => 'Zend\Console\Charset\DESCG',
     *          )
     *      )
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return AdapterInterface|stdClass
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // First, check if we're actually in a Console environment
        if (!Console::isConsole()) {
            // SM factory cannot currently return null, so we return dummy object
            return new stdClass();
        }

        // Read app config and determine Console adapter to use
        $config = $serviceLocator->get('Config');
        if (!empty($config['console']) && !empty($config['console']['adapter'])) {
            // use the adapter supplied in application config
            $adapter = $serviceLocator->get($config['console']['adapter']);
        } else {
            // try to detect best console adapter
            $adapter = Console::detectBestAdapter();
            $adapter = new $adapter();
        }

        // check if we have a valid console adapter
        if (!$adapter instanceof AdapterInterface) {
            // SM factory cannot currently return null, so we convert it to dummy object
            return new stdClass();
        }

        // Optionally, change Console charset
        if (!empty($config['console']) && !empty($config['console']['charset'])) {
            // use the charset supplied in application config
            $charset = $serviceLocator->get($config['console']['charset']);
            $adapter->setCharset($charset);
        }

        return $adapter;
    }
}
