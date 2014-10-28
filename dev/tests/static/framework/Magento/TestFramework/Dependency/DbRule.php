<?php
/**
 * Rule for searching DB dependency
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\TestFramework\Dependency;

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
        if (!preg_match('#/app/.*/(sql|data|resource)/.*\.php$#', $file)) {
            return array();
        }

        $dependenciesInfo = array();
        $unKnowTables = array();
        if (preg_match_all('#>gettable(name)?\([\'"]([^\'"]+)[\'"]\)#i', $contents, $matches)) {
            $tables = array_pop($matches);
            foreach ($tables as $table) {
                if (!isset($this->_moduleTableMap[$table])) {
                    $unKnowTables[$file][$table] = $table;
                    continue;
                }
                if (strtolower($currentModule) !== strtolower($this->_moduleTableMap[$table])) {
                    $dependenciesInfo[] = array(
                        'module' => $this->_moduleTableMap[$table],
                        'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD,
                        'source' => $table
                    );
                }
            }
        }
        foreach ($unKnowTables as $tables) {
            foreach ($tables as $table) {
                $dependenciesInfo[] = array('module' => 'Unknown', 'source' => $table);
            }
        }
        return $dependenciesInfo;
    }
}
