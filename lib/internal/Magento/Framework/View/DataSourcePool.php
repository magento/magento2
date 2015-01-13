<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View;

use Magento\Framework\View\Element\BlockFactory;

/**
 * Class DataSourcePool
 */
class DataSourcePool
{
    /**
     * Block factory
     *
     * @var \Magento\Framework\View\Element\BlockFactory
     */
    protected $blockFactory;

    /**
     * Data sources
     *
     * @var array
     */
    protected $dataSources = [];

    /**
     * Assignments
     *
     * @var array
     */
    protected $assignments = [];

    /**
     * Constructors
     *
     * @param BlockFactory $blockFactory
     */
    public function __construct(BlockFactory $blockFactory)
    {
        $this->blockFactory = $blockFactory;
    }

    /**
     * Add data source
     *
     * @param string $name
     * @param string $class
     * @return object
     * @throws \Exception
     */
    public function add($name, $class)
    {
        if (!isset($this->dataSources[$name])) {
            if (!class_exists($class)) {
                throw new \InvalidArgumentException(__('Invalid Data Source class name: ' . $class));
            }

            $data = $this->blockFactory->createBlock($class);

            $this->dataSources[$name] = $data;
        }

        return $this->dataSources[$name];
    }

    /**
     * Get data source
     *
     * @param string|null $name
     * @return array|object|null
     */
    public function get($name = null)
    {
        if (!isset($name)) {
            return $this->dataSources;
        }

        return isset($this->dataSources[$name]) ? $this->dataSources[$name] : null;
    }

    /**
     * Assign
     *
     * @param string $dataName
     * @param string $namespace
     * @param string $alias
     * @return void
     */
    public function assign($dataName, $namespace, $alias)
    {
        $alias = $alias ?: $dataName;
        $data = $this->get($dataName);

        $this->assignments[$namespace][$alias] = $data;
    }

    /**
     * Retrieve namespace data
     *
     * @param string $namespace
     * @return array
     */
    public function getNamespaceData($namespace)
    {
        return isset($this->assignments[$namespace]) ? $this->assignments[$namespace] : [];
    }
}
