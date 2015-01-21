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
     * @param Mapper $mapper
     * @param array $modulesData
     * @return DependencyGraph
     */
    public function create(Mapper $mapper, $modulesData)
    {
        $nodes = [];
        $dependencies = [];

        // build the graph data
        foreach ($modulesData as $moduleName => $data) {
            $jsonDecoder = new \Magento\Framework\Json\Decoder();
            $data = $jsonDecoder->decode($data);
            $nodes[] = $moduleName;
            foreach (array_keys($data[self::KEY_REQUIRE]) as $depend) {
                $depend = $mapper->packageNameToModuleFullName($depend);
                if ($depend) {
                    $dependencies[] = [$moduleName, $depend];
                }
            }
        }
        $nodes = array_unique($nodes);

        return new DependencyGraph($nodes, $dependencies);
    }
}
