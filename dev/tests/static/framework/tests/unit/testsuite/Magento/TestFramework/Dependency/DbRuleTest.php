<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Dependency;

class DbRuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DbRule
     */
    protected $model;

    protected function setUp(): void
    {
        $this->model = new DbRule(['some_table' => 'SomeModule']);
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
        $this->assertEquals($expected, $this->model->getDependencyInfo($module, 'php', $file, $contents));
    }

    public function getDependencyInfoDataProvider()
    {
        return [
            ['any', 'non-resource-file-path.php', 'any', []],
            [
                'any',
                '/app/some/path/Setup/some-file.php',
                '$install->getTableName("unknown_table")',
                [['module' => 'Unknown', 'source' => 'unknown_table']]
            ],
            [
                'SomeModule',
                '/app/some/path/Resource/Setup.php',
                '$install->getTableName("some_table")',
                []
            ],
            [
                'any',
                '/app/some/path/Resource/Setup.php',
                '$install->getTableName("some_table")',
                [
                    [
                        'module' => 'SomeModule',
                        'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD,
                        'source' => 'some_table',
                    ]
                ]
            ]
        ];
    }
}
