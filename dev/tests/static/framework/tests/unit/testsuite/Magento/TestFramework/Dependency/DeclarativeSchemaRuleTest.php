<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Dependency;

/**
 * Test for declarative schema integrity rule.
 *
 * @package Magento\TestFramework\Dependency
 */
class DeclarativeSchemaRuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DeclarativeSchemaRule
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new DeclarativeSchemaRule(['some_table' => 'SomeModule']);
    }

    /**
     * @param string $module
     * @param string $file
     * @param string $contents
     * @param array $expected
     * @dataProvider getDependencyInfoDataProvider
     */
    public function testGetDependencyInfo($module, $file, $contents, array $expected)
    {
        $actualDependencies = $this->model->getDependencyInfo($module, 'db_schema', $file, $contents);
        $this->assertEquals(
            $expected,
            $actualDependencies
        );
    }

    public function getDependencyInfoDataProvider()
    {
        return [
            ['any', 'non-db-schema-file.php', 'any', []],
            [
                'any',
                '/app/Magento/Module/etc/db_schema.xml',
                '<table name="unknown_table"></table>',
                [['module' => 'Unknown', 'source' => 'unknown_table']]
            ],
            [
                'SomeModule',
                '/app/some/path/etc/db_schema.xml',
                '<table name="some_table"></table>',
                []
            ],
            [
                'any',
                '/app/some/path/etc/db_schema.xml',
                '<table name="some_table"></table>',
                [
                    [
                        'module' => 'SomeModule',
                        'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD,
                        'source' => 'some_table',
                    ]
                ]
            ],
            [
                'any',
                '/app/some/path/etc/db_schema.xml',
                '<schema xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xsi:noNamespaceSchemaLocation="urn:magento:framework:Setup/Declaration/Schema/etc/schema.xsd">
                    <table name="some_table">
                        <constraint xsi:type="foreign" 
                        name="FK_NAME" 
                        table="some_table" 
                        column="some_col" 
                        referenceTable="ref_table" 
                        referenceColumn="ref_col"
                        onDelete="CASCADE"/>
                    </table>
                </schema>',
                [
                    [
                        'module' => 'SomeModule',
                        'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD,
                        'source' => 'some_table',
                    ],
                    [
                        'module' => 'Unknown',
                        'source' => 'ref_table',
                    ]
                ]
            ]
        ];
    }
}
