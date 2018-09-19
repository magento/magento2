<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Coverage of obsolete table names usage
 */
namespace Magento\Test\Legacy;

use Magento\Framework\App\Utility\Files;

class TableTest extends \PHPUnit\Framework\TestCase
{
    public function testTableName()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $filePath
             */
            function ($filePath) {
                $tables = self::extractTables($filePath);
                $legacyTables = [];
                foreach ($tables as $table) {
                    $tableName = $table['name'];
                    if (strpos($tableName, '/') === false) {
                        continue;
                    }
                    $legacyTables[] = $table;
                }

                $message = $this->_composeFoundsMessage($legacyTables);
                $this->assertEmpty($message, $message);
            },
            Files::init()->getPhpFiles(
                Files::INCLUDE_APP_CODE
                | Files::INCLUDE_PUB_CODE
                | Files::INCLUDE_LIBS
                | Files::INCLUDE_TEMPLATES
                | Files::AS_DATA_SET
                | Files::INCLUDE_NON_CLASSES
            )
        );
    }

    /**
     * Returns found table names in a file
     *
     * @param  string $filePath
     * @return array
     */
    public static function extractTables($filePath)
    {
        $regexpMethods = ['_getRegexpTableInMethods', '_getRegexpTableInArrays', '_getRegexpTableInProperties'];

        $result = [];
        $content = file_get_contents($filePath);
        foreach ($regexpMethods as $method) {
            $regexp = self::$method($filePath);
            if (!preg_match_all($regexp, $content, $matches, PREG_SET_ORDER)) {
                continue;
            }

            $iterationResult = self::_matchesToInformation($content, $matches);
            $result = array_merge($result, $iterationResult);
        }
        return $result;
    }

    /**
     * Returns regexp to find table names in method calls in a file
     *
     * @param  string $filePath
     * @return string
     */
    protected static function _getRegexpTableInMethods($filePath)
    {
        $methods = [
            'getTableName',
            '_setMainTable',
            'setMainTable',
            'getTable',
            'setTable',
            'getTableRow',
            'deleteTableRow',
            'updateTableRow',
            'updateTable',
            'tableExists',
            ['name' => 'joinField', 'param_index' => 1],
            'joinTable',
            'getFkName',
            ['name' => 'getFkName', 'param_index' => 2],
            'getIdxName',
            ['name' => 'addVirtualGridColumn', 'param_index' => 1],
        ];

        if (self::_isResourceButNotCollection($filePath)) {
            $methods[] = '_init';
        }

        $regexps = [];
        foreach ($methods as $method) {
            $regexps[] = self::_composeRegexpForMethod($method);
        }
        $result = '#->\s*(' . implode('|', $regexps) . ')#';

        return $result;
    }

    /**
     * @param  string $filePath
     * @return bool
     */
    protected static function _isResourceButNotCollection($filePath)
    {
        $filePath = str_replace('\\', '/', $filePath);
        $parts = explode('/', $filePath);
        return array_search('Resource', $parts) !== false && array_search('Collection.php', $parts) === false;
    }

    /**
     * Returns regular expression to find legacy method calls with table in it
     *
     * @param  string|array $method Method name, or array with method name and index of table parameter in signature
     * @return string
     */
    protected static function _composeRegexpForMethod($method)
    {
        if (!is_array($method)) {
            $method = ['name' => $method, 'param_index' => 0];
        }

        if ($method['param_index']) {
            $skipParamsRegexp = '\s*[[:alnum:]$_\'"]+\s*,';
            $skipParamsRegexp = str_repeat($skipParamsRegexp, $method['param_index']);
        } else {
            $skipParamsRegexp = '';
        }

        $result = $method['name'] . '\(' . $skipParamsRegexp . '\s*[\'"]([^\'"]+)';
        return $result;
    }

    /**
     * Returns regexp to find table names in array definitions
     *
     * @param  string $filePath
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected static function _getRegexpTableInArrays($filePath)
    {
        return '/[\'"](?:[a-z\d_]+_)?table[\'"]\s*=>\s*[\'"]([^\'"]+)/';
    }

    /**
     * Returns regexp to find table names in property assignments
     *
     * @param  string $filePath
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected static function _getRegexpTableInProperties($filePath)
    {
        $properties = ['_aggregationTable'];

        $regexps = [];
        foreach ($properties as $property) {
            $regexps[] = $property . '\s*=\s*[\'"]([^\'"]+)';
        }
        $result = '#' . implode('|', $regexps) . '#';

        return $result;
    }

    /**
     * Converts regexp matches to information, understandable by human: extracts legacy table name and line,
     * where it was found
     *
     * @param  string $content
     * @param  array $matches
     * @return array
     */
    protected static function _matchesToInformation($content, $matches)
    {
        $result = [];
        $fromPos = 0;
        foreach ($matches as $match) {
            $pos = strpos($content, $match[0], $fromPos);
            $lineNum = substr_count($content, "\n", 0, $pos) + 1;
            $result[] = ['name' => $match[count($match) - 1], 'line' => $lineNum];
            $fromPos = $pos + 1;
        }
        return $result;
    }

    /**
     * Composes information message based on list of legacy tables, found in a file
     *
     * @param  array $legacyTables
     * @return null|string
     */
    protected function _composeFoundsMessage($legacyTables)
    {
        if (!$legacyTables) {
            return null;
        }

        $descriptions = [];
        foreach ($legacyTables as $legacyTable) {
            $descriptions[] = "{$legacyTable['name']} (line {$legacyTable['line']})";
        }

        $result = 'Legacy table names with slash must be fixed to direct table names. Found: ' . implode(
            ', ',
            $descriptions
        ) . '.';
        return $result;
    }
}
