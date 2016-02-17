<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc;

use Zend\EventManager\EventsCapableInterface;

interface ApplicationInterface extends EventsCapableInterface
{
    /**
     * Get the locator object
     *
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceManager();

    /**
     * Get the request object
     *
     * @return \Zend\Stdlib\RequestInterface
     */
    public function getRequest();

    /**
     * Get the response object
     *
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function getResponse();

    /**
     * Run the application
     *
     * @return self
     */
    public function run();
}
