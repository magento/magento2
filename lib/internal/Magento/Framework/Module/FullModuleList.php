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
 */
class FullModuleList implements ModuleListInterface
{
    /**
     * Loader of module information from source code
     *
     * @var ModuleList\Loader
     */
    private $loader;

    /**
     * Enumeration of the module names
     *
     * @var string[]
     */
    private $data;

    /**
     * Constructor
     *
     * @param ModuleList\Loader $loader
     */
    public function __construct(ModuleList\Loader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * {@inheritdoc}
     * @see getNames()
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
     */
    public function getOne($name)
    {
        $data = $this->getAll();
        return $data[$name] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getNames()
    {
        $data = $this->getAll();
        return array_keys($data);
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        $this->getAll();
        return isset($this->data[$name]);
    }
}
