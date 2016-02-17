<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Service;

use Zend\Console\Console;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\View\Console\ViewManager as ConsoleViewManager;
use Zend\Mvc\View\Http\ViewManager as HttpViewManager;

class ViewManagerFactory implements FactoryInterface
{
    /**
     * Create and return a view manager based on detected environment
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return ConsoleViewManager|HttpViewManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (Console::isConsole()) {
            return $serviceLocator->get('ConsoleViewManager');
        }

        return $serviceLocator->get('HttpViewManager');
    }
}
