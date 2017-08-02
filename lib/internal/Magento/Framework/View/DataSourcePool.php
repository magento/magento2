<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View;

use Magento\Framework\View\Element\BlockFactory;

/**
 * Class DataSourcePool
 * @since 2.0.0
 */
class DataSourcePool
{
    /**
     * Block factory
     *
     * @var \Magento\Framework\View\Element\BlockFactory
     * @since 2.0.0
     */
    protected $blockFactory;

    /**
     * Data sources
     *
     * @var array
     * @since 2.0.0
     */
    protected $dataSources = [];

    /**
     * Assignments
     *
     * @var array
     * @since 2.0.0
     */
    protected $assignments = [];

    /**
     * Constructors
     *
     * @param BlockFactory $blockFactory
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function add($name, $class)
    {
        if (!isset($this->dataSources[$name])) {
            if (!class_exists($class)) {
                throw new \InvalidArgumentException(
                    (string)new \Magento\Framework\Phrase('Invalid Data Source class name: %1', [$class])
                );
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getNamespaceData($namespace)
    {
        return isset($this->assignments[$namespace]) ? $this->assignments[$namespace] : [];
    }
}
