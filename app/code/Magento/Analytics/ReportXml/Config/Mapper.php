<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\ReportXml\Config;

/**
 * Class Mapper
 *
 * Transforms configuration data for report builder
 * Just for better config usability, affects data from all sources (DB, xml, etc)
 * @see usage examples \Magento\Analytics\ReportXml\Config\Reader
 */
class Mapper
{
    /**
     * Transforms configuration data for report builder
     *
     * @param array $configData
     * @return array
     */
    public function execute($configData)
    {
        if (!isset($configData['config'][0]['report'])) {
            return [];
        }

        $queries = [];
        foreach ($configData['config'][0]['report'] as $queryData) {
            $entityData = $queryData['source'][0];
            unset($queryData['source'][0]);
            $queries[$queryData['name']] = $queryData;
            $queries[$queryData['name']]['source'] = $entityData;

        }
        return $queries;
    }
}
