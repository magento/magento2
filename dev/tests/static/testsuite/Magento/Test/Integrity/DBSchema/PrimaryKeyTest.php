<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Test\Integrity\DBSchema;

use Magento\Framework\App\Utility\Files;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Declaration\Schema\Config\Converter;
use PHPUnit\Framework\TestCase;

/**
 * Test for finding database tables missing primary key
 */
class PrimaryKeyTest extends TestCase
{
    /**
     * Check missing database tables' primary key
     *
     * @throws LocalizedException
     */
    public function testMissingPrimaryKey()
    {
        $exemptionList = $this->getExemptionList();
        $tablesSchemaDeclaration = $this->getDbSchemaDeclarations()['table'];
        $exemptionList = array_intersect(array_keys($tablesSchemaDeclaration), $exemptionList);
        foreach ($exemptionList as $exemptionTableName) {
            unset($tablesSchemaDeclaration[$exemptionTableName]);
        }
        $errorMessage = '';
        $failedTableCtr = 0;
        foreach ($tablesSchemaDeclaration as $tableName => $tableSchemaDeclaration) {
            if (!$this->hasPrimaryKey($tableSchemaDeclaration)) {
                $message = '';
                if (!empty($tableSchemaDeclaration['modules'])) {
                    $message = "It is declared in the following modules: \n" . implode(
                            "\t\n",
                            $tableSchemaDeclaration['modules']
                        );
                }
                $errorMessage .= 'Table ' . $tableName . ' does not have primary key. ' . $message . "\n";
                $failedTableCtr ++;
            }
        }
        if (!empty($errorMessage)) {
            $errorMessage .= "\n\nTotal " . $failedTableCtr . " tables failed";
            $this->fail($errorMessage);
        }
    }

    /**
     * Check table schema and verify if the table has primary key defined.
     *
     * @param array $tableSchemaDeclaration
     * @return bool
     */
    private function hasPrimaryKey(array $tableSchemaDeclaration): bool
    {
        if (isset($tableSchemaDeclaration['constraint'])) {
            foreach ($tableSchemaDeclaration['constraint'] as $constraint) {
                if ($constraint['type'] == 'primary') {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Get database schema declarations from file.
     *
     * @param string $filePath
     * @return array
     */
    private function getDbSchemaDeclarationByFile(string $filePath): array
    {
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($filePath));
        return (new Converter())->convert($dom);
    }

    /**
     * Get database schema declarations for whole application
     *
     * @return array
     * @throws LocalizedException
     */
    private function getDbSchemaDeclarations(): array
    {
        $declarations = [];
        foreach (Files::init()->getDbSchemaFiles() as $filePath) {
            $filePath = reset($filePath);
            preg_match('#/(\w+/\w+)/etc/db_schema.xml#', $filePath, $result);
            $moduleName = str_replace('/', '_', $result[1]);
            $moduleDeclaration = $this->getDbSchemaDeclarationByFile($filePath);

            foreach ($moduleDeclaration['table'] as $tableName => $tableDeclaration) {
                if (!isset($tableDeclaration['modules'])) {
                    $tableDeclaration['modules'] = [];
                }
                array_push($tableDeclaration['modules'], $moduleName);
                $moduleDeclaration = array_replace_recursive(
                    $moduleDeclaration,
                    [
                        'table' => [
                            $tableName => $tableDeclaration,
                        ]
                    ]
                );
            }
            $declarations = array_merge_recursive($declarations, $moduleDeclaration);
        }
        return $declarations;
    }

    /**
     * Return primary key exemption tables list
     *
     * @return string[]
     */
    private function getExemptionList(): array
    {
        $exemptionListFiles = str_replace(
            '\\',
            '/',
            realpath(__DIR__) . '/_files/primary_key_exemption_list*.txt'
        );
        $exemptionList = [];
        foreach (glob($exemptionListFiles) as $fileName) {
            $exemptionList[] = file($fileName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        }
        return array_merge([], ...$exemptionList);
    }
}
