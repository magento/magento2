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
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->serviceLocator->get('config')['nav'];
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
            $this->serviceLocator->get('config')['nav'],
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
            $this->serviceLocator->get('config')['nav'],
            function ($value) {
                return isset($value['main']) && (bool)$value['main'];
            }
        );
        return $result;
    }
}
