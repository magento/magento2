<?php
/**
 * Rule for searching DB dependency
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Dependency;

/**
 * Rule for testing integrity within declarative schema.
 *
 * @package Magento\TestFramework\Dependency
 */
class DeclarativeSchemaRule implements RuleInterface
{
    /**
     * Map of tables and modules
     *
     * @var array
     */
    protected $_moduleTableMap;

    /**
     * Constructor
     *
     * @param array $tables
     */
    public function __construct(array $tables)
    {
        $this->_moduleTableMap = $tables;
    }

    /**
     * Gets external dependencies information for current module by analyzing db_schema.xml files contents.
     *
     * @param string $currentModule
     * @param string $fileType
     * @param string $file
     * @param string $contents
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getDependencyInfo($currentModule, $fileType, $file, &$contents)
    {
        if ('db_schema' != $fileType || !preg_match('#.*/db_schema\.xml$#', $file)) {
            return [];
        }

        $dependenciesInfo = [];
        $unKnowTables = [];

        $dom = new \DOMDocument();
        $dom->loadXML($contents);
        $tables = $dom->getElementsByTagName('table');
        $constraints = $dom->getElementsByTagName('constraint');

        $tableNames = [];
        $foreignKeyTables = [];
        $foreignKeyReferenceTables = [];

        /** @var \DOMElement $table */
        foreach ($tables as $table) {
            $tableNames[] = $table->getAttribute('name');
        }

        /** @var \DOMElement $constraint */
        foreach ($constraints as $constraint) {
            $xsiType = $constraint->getAttribute('xsi:type');
            if (strtolower($xsiType) == 'foreign' && $constraint->getAttribute('disabled') !== '1') {
                $foreignKeyTables[] = $constraint->getAttribute('table');
                $foreignKeyReferenceTables[] = $constraint->getAttribute('referenceTable');
            }
        }

        $tableNames = array_unique(array_merge($tableNames, $foreignKeyReferenceTables, $foreignKeyTables));

        /** @var string $table */
        foreach ($tableNames as $table) {
            if (!isset($this->_moduleTableMap[$table])) {
                $unKnowTables[$file][$table] = $table;
                continue;
            }
            if (strtolower($currentModule) !== strtolower($this->_moduleTableMap[$table])) {
                $dependenciesInfo[] = [
                    'module' => $this->_moduleTableMap[$table],
                    'type' => RuleInterface::TYPE_HARD,
                    'source' => $table,
                ];
            }
        }

        foreach ($unKnowTables as $tables) {
            foreach ($tables as $table) {
                $dependenciesInfo[] = ['module' => 'Unknown', 'source' => $table];
            }
        }
        return $dependenciesInfo;
    }
}
