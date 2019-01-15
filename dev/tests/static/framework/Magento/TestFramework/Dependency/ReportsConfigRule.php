<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Dependency;

/**
 * Class provides dependency rule for reports.xml config file.
 */
class ReportsConfigRule implements RuleInterface
{
    /**
     * Map of tables and modules
     *
     * @var array
     */
    private $moduleTableMap;

    /**
     * @param array $tables
     */
    public function __construct(array $tables)
    {
        $this->moduleTableMap = $tables;
    }

    /**
     * @inheritdoc
     */
    public function getDependencyInfo($currentModule, $fileType, $file, &$contents)
    {
        if ('config' != $fileType || !preg_match('#.*/reports\.xml$#', $file)) {
            return [];
        }

        $dependenciesInfo = [];
        if (preg_match_all('#<source[^>]*name=[\'"]([^\'"]+)[\'"]#i', $contents, $matches)) {
            $tables = array_pop($matches);
            foreach ($tables as $table) {
                if (!isset($this->moduleTableMap[$table])) {
                    continue;
                }
                if (strtolower($currentModule) !== strtolower($this->moduleTableMap[$table])) {
                    $dependenciesInfo[] = [
                        'module' => $this->moduleTableMap[$table],
                        'type' => RuleInterface::TYPE_HARD,
                        'source' => $table,
                    ];
                }
            }
        }

        return $dependenciesInfo;
    }
}
