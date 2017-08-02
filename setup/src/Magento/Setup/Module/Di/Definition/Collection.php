<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\Definition;

/**
 * Class \Magento\Setup\Module\Di\Definition\Collection
 *
 * @since 2.0.0
 */
class Collection
{
    /**
     * List of definitions
     *
     * @var array
     * @since 2.0.0
     */
    private $definitions = [];

    /**
     * Returns definitions as [instance => list of arguments]
     *
     * @return array
     * @since 2.0.0
     */
    public function getCollection()
    {
        return $this->definitions;
    }

    /**
     * Initializes collection with array of definitions
     *
     * @param array $definitions
     *
     * @return void
     * @since 2.0.0
     */
    public function initialize(array $definitions)
    {
        $this->definitions = $definitions;
    }

    /**
     * Adds collection to current collection
     *
     * @param Collection $collection
     *
     * @return void
     * @since 2.0.0
     */
    public function addCollection(Collection $collection)
    {
        $this->initialize(array_merge($this->getCollection(), $collection->getCollection()));
    }

    /**
     * Add new definition for instance
     *
     * @param string $instance
     * @param array|null $arguments
     *
     * @return void
     * @since 2.0.0
     */
    public function addDefinition($instance, $arguments = [])
    {
        $this->definitions[$instance] = $arguments;
    }

    /**
     * Returns instance arguments
     *
     * @param string $instanceName
     * @return null|array
     * @since 2.0.0
     */
    public function getInstanceArguments($instanceName)
    {
        return isset($this->definitions[$instanceName]) ? $this->definitions[$instanceName] : null;
    }

    /**
     * Returns instances names list
     *
     * @return array
     * @since 2.0.0
     */
    public function getInstancesNamesList()
    {
        return array_keys($this->getCollection());
    }

    /**
     * Whether instance defined
     *
     * @param string $instanceName
     * @return bool
     * @since 2.0.0
     */
    public function hasInstance($instanceName)
    {
        return isset($this->definitions[$instanceName]);
    }
}
