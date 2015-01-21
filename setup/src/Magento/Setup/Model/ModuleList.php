<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\Module\ModuleList\Loader;

/**
 * A list of modules in the Magento application
 *
 * Represents all modules, regardless of enabled or not
 */
class ModuleList implements \Magento\Framework\Module\ModuleListInterface
{
    /**
     * Loader of module information from source code
     *
     * @var Loader
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
     * @param Loader $loader
     */
    public function __construct(Loader $loader)
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
        return isset($data[$name]) ? $data[$name] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getNames()
    {
        $data = $this->getAll();
        $result = array_keys($data);
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return array_search($name, $this->getNames()) !== false;
    }
}