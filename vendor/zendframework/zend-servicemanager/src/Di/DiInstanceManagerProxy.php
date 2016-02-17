<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\ServiceManager\Di;

use Zend\Di\InstanceManager as DiInstanceManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class DiInstanceManagerProxy extends DiInstanceManager
{
    /**
     * @var DiInstanceManager
     */
    protected $diInstanceManager = null;

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator = null;

    /**
     * Constructor
     *
     * @param DiInstanceManager $diInstanceManager
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(DiInstanceManager $diInstanceManager, ServiceLocatorInterface $serviceLocator)
    {
        $this->diInstanceManager = $diInstanceManager;
        $this->serviceLocator = $serviceLocator;

        // localize state
        $this->aliases = &$diInstanceManager->aliases;
        $this->sharedInstances = &$diInstanceManager->sharedInstances;
        $this->sharedInstancesWithParams = &$diInstanceManager->sharedInstancesWithParams;
        $this->configurations = &$diInstanceManager->configurations;
        $this->typePreferences = &$diInstanceManager->typePreferences;
    }

    /**
     * Determine if we have a shared instance by class or alias
     *
     * @param $classOrAlias
     * @return bool
     */
    public function hasSharedInstance($classOrAlias)
    {
        return ($this->serviceLocator->has($classOrAlias) || $this->diInstanceManager->hasSharedInstance($classOrAlias));
    }

    /**
     * Get shared instance
     *
     * @param $classOrAlias
     * @return mixed
     */
    public function getSharedInstance($classOrAlias)
    {
        if ($this->serviceLocator->has($classOrAlias)) {
            return $this->serviceLocator->get($classOrAlias);
        }

        return $this->diInstanceManager->getSharedInstance($classOrAlias);
    }
}
