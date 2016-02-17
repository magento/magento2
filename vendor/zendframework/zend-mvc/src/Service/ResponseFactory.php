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
use Zend\Console\Response as ConsoleResponse;
use Zend\Http\PhpEnvironment\Response as HttpResponse;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ResponseFactory implements FactoryInterface
{
    /**
     * Create and return a response instance, according to current environment.
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return \Zend\Stdlib\Message
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (Console::isConsole()) {
            return new ConsoleResponse();
        }

        return new HttpResponse();
    }
}
