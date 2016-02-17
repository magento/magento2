<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\ServiceManager\Di;

use Zend\Di\Di;
use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DiServiceInitializer extends Di implements InitializerInterface
{
    /**
     * @var Di
     */
    protected $di = null;

    /**
     * @var DiInstanceManagerProxy
     */
    protected $diInstanceManagerProxy = null;

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator = null;

    /**
     * Constructor
     *
     * @param \Zend\Di\Di $di
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @param null|DiInstanceManagerProxy $diImProxy
     */
    public function __construct(Di $di, ServiceLocatorInterface $serviceLocator, DiInstanceManagerProxy $diImProxy = null)
    {
        $this->di = $di;
        $this->serviceLocator = $serviceLocator;
        $this->diInstanceManagerProxy = ($diImProxy) ?: new DiInstanceManagerProxy($di->instanceManager(), $serviceLocator);
    }

    /**
     * Initialize
     *
     * @param $instance
     * @param ServiceLocatorInterface $serviceLocator
     * @throws \Exception
     */
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        $instanceManager = $this->di->instanceManager;
        $this->di->instanceManager = $this->diInstanceManagerProxy;
        try {
            $this->di->injectDependencies($instance);
            $this->di->instanceManager = $instanceManager;
        } catch (\Exception $e) {
            $this->di->instanceManager = $instanceManager;
            throw $e;
        }
    }
}
