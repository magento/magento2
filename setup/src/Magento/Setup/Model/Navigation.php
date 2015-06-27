<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Zend\ServiceManager\ServiceLocatorInterface;

class Navigation
{
    /**
     * @var array
     */
    private $navStates;

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(ServiceLocatorInterface $serviceLocator, ObjectManagerProvider $objectManagerProvider)
    {
        $objectManager = $objectManagerProvider->get();
        if ($objectManager->get('Magento\Framework\App\DeploymentConfig')->isAvailable()) {
            $this->navStates = $serviceLocator->get('config')['navUpdater'];
        } else {
            $this->navStates = $serviceLocator->get('config')['navInstaller'];
        }
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->navStates;
    }

    /**
     * Retrieve array of menu items
     *
     * Returns only items with 'nav-bar' equal to TRUE
     *
     * @return array
     */
    public function getMenuItems()
    {
        return array_filter(
            $this->navStates,
            function ($value) {
                return isset($value['nav-bar']) && (bool)$value['nav-bar'];
            }
        );
    }

    /**
     * Retrieve array of menu items
     *
     * Returns only items with 'main' equal to TRUE
     *
     * @return array
     */
    public function getMainItems()
    {
        $result = array_filter(
            $this->navStates,
            function ($value) {
                return isset($value['main']) && (bool)$value['main'];
            }
        );
        return $result;
    }
}
