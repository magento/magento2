<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\ReportXml\Config;

/**
 * A reports configuration mapper.
 *
 * Transforms configuration data to improve its usability.
 *
 * @see usage examples in \Magento\Analytics\ReportXml\Config\Reader
 */
class Mapper
{
    /**
     * Transforms configuration data.
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
            $entityData = array_shift($queryData['source']);
            $queries[$queryData['name']] = $queryData;
            $queries[$queryData['name']]['source'] = $entityData;
        }
        return $queries;
    }
}
