<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Model\Config;

/**
 * Transforms Analytics configuration data.
 */
class Mapper
{
    /**
     * Transforms Analytics configuration data.
     *
     * @param array $configData
     * @return array
     */
    public function execute($configData)
    {
        if (!isset($configData['config'][0]['file'])) {
            return [];
        }

        $files = [];
        foreach ($configData['config'][0]['file'] as $fileData) {
            $providers = [];
            if (isset($fileData['providers'][0])) {
                $providers = $fileData['providers'][0];
                unset($fileData['providers'][0]);
            }
            $files[$fileData['name']] = $fileData;
            $files[$fileData['name']]['providers'] = $providers;
        }
        return $files;
    }
}
