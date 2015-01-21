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
     * All Modules composer.json data
     *
     * @var array
     */
    protected $modulesData;

    /**
     * List of enabled modules
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
     * Set modules data, this also provide the data to Mapper to create mappings of package name to module name
     *
     * @param string[] $modulesData
     * @return void
     */
    public function setModulesData($modulesData)
    {
        $this->modulesData = $modulesData;
        $this->mapper->createMapping($modulesData);
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
}
