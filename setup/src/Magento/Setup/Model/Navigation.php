<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Class Navigation implements the data model for the navigation menu
 */
class Navigation
{
    /**
     * Type of navigation
     */
    const NAV_LANDING = 'navLanding';

    /**
     * @var string
     */
    private $navStates;

    /**
     * @var string
     */
    private $navType;

    /**
     * @var string
     */
    private $titles;

    /**
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->navStates = $serviceLocator->get('config')[self::NAV_LANDING];
        $this->navType = self::NAV_LANDING;
        $this->titles = $serviceLocator->get('config')[self::NAV_LANDING . 'Titles'];
    }

    /**
     * Type getter method
     *
     * @return string
     */
    public function getType()
    {
        return $this->navType;
    }

    /**
     * Data getter method
     *
     * @return array
     */
    public function getData()
    {
        return $this->navStates;
    }

    /**
     * Retrieve array of menu items
     *
     * Returns only items with 'nav' equal to TRUE
     *
     * @return array
     */
    public function getMenuItems()
    {
        return array_values(array_filter(
            $this->navStates,
            function ($value) {
                return isset($value['nav']) && (bool)$value['nav'];
            }
        ));
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
        $result = array_values(array_filter(
            $this->navStates,
            function ($value) {
                return isset($value['main']) && (bool)$value['main'];
            }
        ));
        return $result;
    }

    /**
     * Returns titles of the navigation pages
     *
     * @return array
     */
    public function getTitles()
    {
        return $this->titles;
    }
}
