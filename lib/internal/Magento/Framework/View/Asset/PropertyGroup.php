<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

/**
 * Association of arbitrary properties with a list of page assets
 */
class PropertyGroup extends Collection
{
    /**
     * Properties
     *
     * @var array
     */
    protected $properties = [];

    /**
     * Constructor
     *
     * @param array $properties
     */
    public function __construct(array $properties)
    {
        $this->properties = $properties;
    }

    /**
     * Retrieve values of all properties
     *
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Retrieve value of an individual property
     *
     * @param string $name
     * @return mixed
     */
    public function getProperty($name)
    {
        return isset($this->properties[$name]) ? $this->properties[$name] : null;
    }
}
