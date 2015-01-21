<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

use Magento\Framework\Filesystem;

class Mapper
{
    /**
     * Mapping of package name to module name
     *
     * @var array
     */
    private $packageNameMap;

    /**
     * Create the mapping
     *
     * @param array $modulesData
     * @return void
     */
    public function createMapping($modulesData)
    {
        $jsonDecoder = new \Magento\Framework\Json\Decoder();
        foreach ($modulesData as $path => $data) {
            $data = $jsonDecoder->decode($data);
            $this->packageNameMap[$data['name']] = $path;
        }
    }

    /**
     * Convert package name in composer.json to module name in module.xml
     *
     * @param string $packageName
     * @return string
     */
    public function packageNameToModuleFullName($packageName)
    {
        return isset($this->packageNameMap[$packageName]) ? $this->packageNameMap[$packageName] : '';
    }
}
