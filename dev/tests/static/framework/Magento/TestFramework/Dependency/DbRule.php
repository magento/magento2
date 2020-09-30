<?php
/**
 * Rule for searching DB dependency
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Dependency;

/**
 * Class to get DB dependencies information
 */
class DbRule implements \Magento\TestFramework\Dependency\RuleInterface
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
     * Gets alien dependencies information for current module by analyzing file's contents
     *
     * @param string $currentModule
     * @param string $fileType
     * @param string $file
     * @param string $contents
     * @return array
     */
    public function getDependencyInfo($currentModule, $fileType, $file, &$contents)
    {
        if ('php' !== $fileType || !preg_match('#.*/(Setup|Resource|Query)/.*\.php$#', $file)) {
            return [];
        }

        $dependenciesInfo = [];
        $unKnowTables = [];
        if (preg_match_all('#>gettable(name)?\([\'"]([^\'"]+)[\'"]\)#i', $contents, $matches)) {
            $tables = array_pop($matches);
            foreach ($tables as $table) {
                if (!isset($this->_moduleTableMap[$table])) {
                    $unKnowTables[$file][$table] = $table;
                    continue;
                }
                if (strtolower($currentModule) !== strtolower($this->_moduleTableMap[$table])) {
                    $dependenciesInfo[] = [
                        'modules' => [$this->_moduleTableMap[$table]],
                        'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD,
                        'source' => $table,
                    ];
                }
            }
        }
        foreach ($unKnowTables as $tables) {
            foreach ($tables as $table) {
                $dependenciesInfo[] = ['modules' => ['Unknown'], 'source' => $table];
            }
        }
        return $dependenciesInfo;
    }
}
