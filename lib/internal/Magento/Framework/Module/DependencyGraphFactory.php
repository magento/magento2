<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

use Magento\Framework\Filesystem;

class DependencyGraphFactory
{
    const KEY_REQUIRE = 'require';

    /**
     * @var Mapper
     */
    private $mapper;

    /**
     * @var array
     */
    private $modules;

    /**
     * Filesystem
     *
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param Mapper $mapper
     */
    public function __construct(Filesystem $filesystem, Mapper $mapper)
    {
        $this->filesystem = $filesystem;
        $this->mapper = $mapper;
    }

    /**
     * @param array $modules
     * @return DependencyGraph
     */
    public function create($modules)
    {
        $readAdapter = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MODULES);

        $this->modules = $modules;
        $this->mapper->setModules($modules);

        $nodes = [];
        $dependencies = [];

        // build the graph data
        foreach ($this->modules as $module) {
            $jsonDecoder = new \Magento\Framework\Json\Decoder();
            $module_partial = $this->mapper->moduleFullNameToModuleName($module);
            $vendor = $this->mapper->moduleFullNameToVendorName($module);
            $data = $jsonDecoder->decode($readAdapter->readFile("$vendor/$module_partial/composer.json"));
            $nodes[] = $module;
            foreach (array_keys($data[self::KEY_REQUIRE]) as $depend) {
                $depend = $this->mapper->packageNameToModuleFullName($depend);
                if ($depend) {
                    $dependencies[] = [$module, $depend];
                }
            }
        }
        $nodes = array_unique($nodes);

        return new DependencyGraph($nodes, $dependencies);
    }
}
