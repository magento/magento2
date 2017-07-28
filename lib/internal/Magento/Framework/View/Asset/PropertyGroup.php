<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

/**
 * Association of arbitrary properties with a list of page assets
 * @since 2.0.0
 */
class PropertyGroup extends Collection
{
    /**
     * Properties
     *
     * @var array
     * @since 2.0.0
     */
    protected $properties = [];

    /**
     * Constructor
     *
     * @param array $properties
     * @since 2.0.0
     */
    public function __construct(array $properties)
    {
        $this->properties = $properties;
    }

    /**
     * Retrieve values of all properties
     *
     * @return array
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getProperty($name)
    {
        return isset($this->properties[$name]) ? $this->properties[$name] : null;
    }
}
