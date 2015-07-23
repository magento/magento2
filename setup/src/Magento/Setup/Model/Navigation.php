<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Zend\ServiceManager\ServiceLocatorInterface;

class Navigation
{
    /**#@+
     * Types of wizards
     */
    const NAV_INSTALLER = 'navInstaller';
    const NAV_UPDATER = 'navUpdater';
    /**#@- */

    /**
     * @var array
     */
    private $navStates;

    /**
     * @var string
     */
    private $navType;

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(ServiceLocatorInterface $serviceLocator, ObjectManagerProvider $objectManagerProvider)
    {
        $objectManager = $objectManagerProvider->get();
        if ($objectManager->get('Magento\Framework\App\DeploymentConfig')->isAvailable()) {
            $this->navStates = $serviceLocator->get('config')[self::NAV_UPDATER];
            $this->navType = self::NAV_UPDATER;
        } else {
            $this->navStates = $serviceLocator->get('config')[self::NAV_INSTALLER];
            $this->navType = self::NAV_INSTALLER;
        }
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->navType;
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
