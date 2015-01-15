<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

use Magento\Framework\Filesystem;

class Checker
{
    /**
     * List of all modules
     *
     * @var array
     */
    protected $modules;

    /**
     * List of all enabled modules assuming module enable/disable operation succeeds
     *
     * @var array
     */
    protected $enabledModules;

    /**
     * Package name to module name mapper
     *
     * @var Mapper
     */
    protected $mapper;

    /**
     * @param Mapper $mapper
     */
    public function __construct(Mapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * Set list of all modules
     *
     * @param $modules
     * @return void
     */
    public function setModules($modules)
    {
        $this->modules = $modules;
        $this->mapper->setModules($modules);
    }

    /**
     * Set list of enabled modules, assuming module enable/disable succeeds
     *
     * @param $enabledModules
     * @return void
     */
    public function setEnabledModules($enabledModules)
    {
        $this->enabledModules = $enabledModules;
    }

    /**
     * Check if module is enabled
     *
     * @param string $moduleName
     * @return bool
     */
    protected function checkIfEnabled($moduleName)
    {
        return array_search($moduleName, $this->enabledModules) !== false;
    }
}
