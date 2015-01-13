<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

use Magento\Framework\App\DeploymentConfig;

/**
 * A list of modules in the Magento application
 *
 * Encapsulates information about whether modules are enabled or not.
 * Represents only enabled modules through its interface
 */
class ModuleList implements ModuleListInterface
{
    /**
     * Deployment configuration
     *
     * @var DeploymentConfig
     */
    private $config;

    /**
     * Loader of module information from source code
     *
     * @var ModuleList\Loader
     */
    private $loader;

    /**
     * An associative array of modules
     *
     * The possible values are 1 (enabled) or 0 (disabled)
     *
     * @var int[]
     */
    private $configData;

    /**
     * Enumeration of the enabled module names
     *
     * @var string[]
     */
    private $enabled;

    /**
     * Constructor
     *
     * @param DeploymentConfig $config
     * @param ModuleList\Loader $loader
     */
    public function __construct(DeploymentConfig $config, ModuleList\Loader $loader)
    {
        $this->config = $config;
        $this->loader = $loader;
    }

    /**
     * {@inheritdoc}
     *
     * Note that this triggers loading definitions of all existing modules in the system.
     * Use this method only when you actually need modules' declared meta-information.
     *
     * @see getNames()
     */
    public function getAll()
    {
        if (null === $this->enabled) {
            $all = $this->loader->load();
            if (empty($all)) {
                return []; // don't record erroneous value into memory
            }
            $this->enabled = [];
            foreach ($all as $key => $value) {
                if ($this->has($key)) {
                    $this->enabled[$key] = $value;
                }
            }
        }
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     * @see has()
     */
    public function getOne($name)
    {
        $enabled = $this->getAll();
        return isset($enabled[$name]) ? $enabled[$name] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getNames()
    {
        $this->loadConfigData();
        if (!$this->configData) {
            return [];
        }
        $result = array_keys(array_filter($this->configData));
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        $this->loadConfigData();
        if (!$this->configData) {
            return false;
        }
        return !empty($this->configData[$name]);
    }

    /**
     * Loads configuration data only
     *
     * @return void
     */
    private function loadConfigData()
    {
        if (null === $this->configData) {
            $this->configData = $this->config->getSegment(ModuleList\DeploymentConfig::CONFIG_KEY);
        }
    }
}
