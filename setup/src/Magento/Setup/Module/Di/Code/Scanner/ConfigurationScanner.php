<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\Code\Scanner;

use Magento\Framework\App\Area;

class ConfigurationScanner
{
    /**
     * ConfigurationScanner constructor.
     *
     * @param \Magento\Framework\App\Config\FileResolver $fileResolver
     * @param \Magento\Framework\App\AreaList $areaList
     */
    public function __construct(
        \Magento\Framework\App\Config\FileResolver $fileResolver,
        \Magento\Framework\App\AreaList $areaList
    ) {
        $this->fileResolver = $fileResolver;
        $this->areaList = $areaList;
    }

    /**
     * Scan configuration files
     *
     * @param string $fileName
     *
     * @return array array of paths to the configuration files
     */
    public function scan($fileName)
    {
        $files = [];
        $areaCodes = array_merge(
            ['primary', Area::AREA_GLOBAL],
            $this->areaList->getCodes()
        );
        foreach ($areaCodes as $area) {
            $files = array_merge_recursive(
                $files,
                $this->fileResolver->get($fileName, $area)->toArray()
            );
        }
        return array_keys($files);
    }
}
