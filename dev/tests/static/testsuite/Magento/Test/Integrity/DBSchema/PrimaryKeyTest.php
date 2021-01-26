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
 * Test for existance of primary key in database tables
 */
class PrimaryKeyTest extends TestCase
{
    private $blockWhitelist;

    /**
     * Check existance of database tables' primary key
     *
     * @throws LocalizedException
     */
    public function testMissingPrimaryKey()
    {
        $exemptionList = $this->getExemptionlist();
        $tablesSchemaDeclaration = $this->getDbSchemaDeclaration()['table'];
        $exemptionList = array_intersect(array_keys($tablesSchemaDeclaration), $exemptionList);
        foreach ($exemptionList as $exemptionTableName) {
            unset($tablesSchemaDeclaration[$exemptionTableName]);
        }
        $errorMessage = '';
        $failedTableCtr = 0;
        foreach ($tablesSchemaDeclaration as $tableName => $tableSchemaDeclaration) {
            if (!isset($tableSchemaDeclaration['constraint']['PRIMARY'])) {
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
     * Get database schema declaration from file.
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
     * Get database schema declaration for whole application
     *
     * @return array
     * @throws LocalizedException
     */
    private function getDbSchemaDeclaration(): array
    {
        $declaration = [];
        foreach (Files::init()->getDbSchemaFiles() as $filePath) {
            $filePath = reset($filePath);
            preg_match('#app/code/(\w+/\w+)#', $filePath, $result);
            $moduleName = str_replace('/', '\\', $result[1]);
            $moduleDeclaration = $this->getDbSchemaDeclarationByFile($filePath);

            foreach ($moduleDeclaration['table'] as $tableName => $tableDeclaration) {
                if (!isset($tableDeclaration['modules'])) {
                    $tableDeclaration['modules'] = [];
                }
                array_push($tableDeclaration['modules'], $moduleName);
                $moduleDeclaration = array_replace_recursive(
                    $moduleDeclaration,
                    ['table' => [
                        $tableName => $tableDeclaration,
                    ]
                    ]
                );
            }
            $declaration = array_merge_recursive($declaration, $moduleDeclaration);
        }
        return $declaration;
    }

    /**
     * Return primary key exemption tables list
     *
     * @return string[]
     */
    private function getExemptionlist(): array
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
