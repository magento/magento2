<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

/**
 * A list of modules in the Magento application
 *
 * Represents all modules, regardless of enabled or not
 * @since 2.0.0
 */
class FullModuleList implements ModuleListInterface
{
    /**
     * Loader of module information from source code
     *
     * @var ModuleList\Loader
     * @since 2.0.0
     */
    private $loader;

    /**
     * Enumeration of the module names
     *
     * @var string[]
     * @since 2.0.0
     */
    private $data;

    /**
     * Constructor
     *
     * @param ModuleList\Loader $loader
     * @since 2.0.0
     */
    public function __construct(ModuleList\Loader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * {@inheritdoc}
     * @see getNames()
     * @since 2.0.0
     */
    public function getAll()
    {
        if (null === $this->data) {
            $this->data = $this->loader->load();
        }
        return $this->data;
    }

    /**
     * {@inheritdoc}
     * @see has()
     * @since 2.0.0
     */
    public function getOne($name)
    {
        $data = $this->getAll();
        return isset($data[$name]) ? $data[$name] : null;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getNames()
    {
        $data = $this->getAll();
        return array_keys($data);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function has($name)
    {
        $this->getAll();
        return isset($this->data[$name]);
    }
}
