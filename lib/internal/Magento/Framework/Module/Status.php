<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Module;

use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\State\Cleanup;

/**
 * A service for controlling module status
 */
class Status
{
    /**
     * Module list loader
     *
     * @var ModuleList\Loader
     */
    private $loader;

    /**
     * Module list
     *
     * @var ModuleList
     */
    private $list;

    /**
     * Deployment config writer
     *
     * @var Writer
     */
    private $writer;

    /**
     * Application state cleanup service
     *
     * @var Cleanup
     */
    private $cleanup;

    /**
     * Error messages collected during last command
     *
     * @var string[]
     */
    private $errors = [];

    /**
     * Constructor
     *
     * @param ModuleList\Loader $loader
     * @param ModuleList $list
     * @param Writer $writer
     * @param Cleanup $cleanup
     */
    public function __construct(ModuleList\Loader $loader, ModuleList $list, Writer $writer, Cleanup $cleanup)
    {
        $this->loader = $loader;
        $this->list = $list;
        $this->writer = $writer;
        $this->cleanup = $cleanup;
    }

    /**
     * Whether it is allowed to enable or disable specified modules
     *
     * TODO: not implemented yet (MAGETWO-32613)
     *
     * @param bool $isEnable
     * @param string[] $modules
     * @return bool
     */
    public function isSetEnabledAllowed($isEnable, $modules)
    {
        $this->errors = [];
        return true;
    }

    /**
     * Gets error messages that may have occurred during last command
     *
     * @return string[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Sets specified modules to enabled or disabled state
     *
     * Performs other necessary routines, such as cache cleanup
     *
     * @param bool $isEnable
     * @param string[] $modules
     * @return void
     * @throws \LogicException
     */
    public function setEnabled($isEnable, $modules)
    {
        $this->errors = [];
        $all = $this->loader->load();
        foreach ($modules as $name) {
            if (!isset($all[$name])) {
                throw new \LogicException("Unknown module: '{$name}'");
            }
        }
        $result = [];
        foreach (array_keys($all) as $name) {
            if (in_array($name, $modules)) {
                $result[$name] = $isEnable;
            } else {
                $result[$name] = $this->list->has($name);
            }
        }
        $segment = new ModuleList\DeploymentConfig($result);
        $this->writer->update($segment);
        $this->cleanup->clearCaches();
        $this->cleanup->clearCodeGeneratedFiles();
    }
}
