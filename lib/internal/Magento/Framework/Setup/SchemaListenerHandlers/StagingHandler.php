<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\SchemaListenerHandlers;

/**
 * Here we will put schema listener handlers
 */
class StagingHandler implements SchemaListenerHandlerInterface
{
    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function handle($moduleName, $tables, array $params, $definition)
    {
        if (strpos($moduleName, 'Staging') !== false) {
            $parentModuleName = str_replace('Staging', '', $moduleName);
            $tableName = $params['table'];
            $oldColumn = $params['old_column'];
            $newColumn = $params['new_column'];

            if (isset($tables[$parentModuleName][$tableName]['constraints']['primary']['PRIMARY'])) {
                $key = 'PRIMARY';
                $constraintData = $tables[$parentModuleName][$tableName]['constraints']['primary']['PRIMARY'];
                $oldColumnIndex = array_search($oldColumn, $constraintData['columns']);
                if (isset($constraintData['columns'][$oldColumnIndex])) {
                    $tables[$moduleName][$tableName]['constraints']['primary'][$key] = [
                        'disabled' => true
                    ];
                    $constraintData['columns'][$oldColumnIndex] = $newColumn;
                    $tables[$moduleName][$tableName]['constraints']['primary']['STAGING_PRIMARY'] = $constraintData;
                }
            }

            if (isset($tables[$parentModuleName][$tableName]['indexes'])) {
                foreach ($tables[$parentModuleName][$tableName]['indexes'] as $key => $indexData) {
                    if (isset($indexData['columns'][$oldColumn])) {
                        $tables[$moduleName][$tableName]['indexes'][$key] = [
                            'disabled' => true
                        ];
                        $oldColumnIndex = array_search($oldColumn, $indexData['columns']);
                        $indexData['columns'][$oldColumnIndex] = $newColumn;
                        $key = $this->generateKey($key, $newColumn, $oldColumn);
                        $tables[$moduleName][$tableName]['indexes'][$key] = $indexData;
                    }
                }
            }

            if (isset($tables[$parentModuleName][$tableName]['constraints']['unique'])) {
                foreach ($tables[$parentModuleName][$tableName]['constraints']['unique'] as $key => $constraintData) {
                    if (isset($constraintData['columns'][$oldColumn])) {
                        $tables[$moduleName][$tableName]['constraints']['unique'][$key] = [
                            'disabled' => true
                        ];
                        $oldColumnIndex = array_search($oldColumn, $constraintData['columns']);
                        $constraintData['columns'][$oldColumnIndex] = $newColumn;
                        $key = $this->generateKey($key, $newColumn, $oldColumn);
                        $tables[$moduleName][$tableName]['constraints']['unique'][$key] = $constraintData;
                    }
                }
            }

            $definition = $this->preprocessType($definition);
            $tables[$moduleName][$tableName]['columns'][strtolower($oldColumn)] = [
                'xsi:type' => $definition['type'],
                'name' => $oldColumn,
                'disabled' => true
            ];
        }

        return $tables;
    }

    /**
     * Preprocess types
     *
     * @param array $definition
     * @return array
     */
    private function preprocessType(array  $definition)
    {
        if ($definition['type'] === 'integer') {
            $definition['type'] = 'int';
        }

        return $definition;
    }

    /**
     * Generates new key for staging keys
     *
     * @param $key
     * @param $newColumn
     * @param $oldColumn
     * @return mixed|string
     */
    private function generateKey($key, $newColumn, $oldColumn)
    {
        if (strpos($key, strtoupper($oldColumn)) !== false) {
            return str_replace(strtoupper($oldColumn), strtoupper($newColumn), $key);
        }

        return $key . '_STAGING';
    }
}
