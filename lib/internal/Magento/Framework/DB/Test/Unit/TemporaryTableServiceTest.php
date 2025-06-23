<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\TemporaryTableService;
use Magento\Framework\Math\Random;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TemporaryTableServiceTest extends TestCase
{
    /**
     * @var TemporaryTableService|MockObject
     */
    private $temporaryTableService;

    /**
     * @var AdapterInterface|MockObject
     */
    private $adapterMock;

    /**
     * @var Random|MockObject
     */
    private $randomMock;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->adapterMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->selectMock = $this->createMock(Select::class);
        $this->randomMock = $this->createMock(Random::class);
        $this->temporaryTableService = (new ObjectManager($this))->getObject(
            TemporaryTableService::class,
            [
                'random' => $this->randomMock,
                'allowedIndexMethods' => ['HASH'],
                'allowedEngines' => ['INNODB']
            ]
        );
    }

    /**
     * Run test createFromSelect method
     *
     * @return void
     */
    public function testCreateFromSelectWithException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $random = 'random_table';
        $indexes = [
            ['PRIMARY' => ['primary_column_name']],
            'CREATE TEMPORARY TABLE random_table (PRIMARY KEY(primary_column_name)) ENGINE=INNODB IGNORE '
            . '(select * from sometable)'
        ];

        $this->assertEquals(
            $random,
            $this->temporaryTableService->createFromSelect(
                $this->selectMock,
                $this->adapterMock,
                $indexes,
                TemporaryTableService::INDEX_METHOD_HASH . "Other",
                TemporaryTableService::DB_ENGINE_INNODB . "Other"
            )
        );
    }

    /**
     * Run test createFromSelect method
     *
     * @param array $indexes
     * @param string $expectedSelect
     * @dataProvider createFromSelectDataProvider
     * @return void
     */
    public function testCreateFromSelect($indexes, $expectedSelect)
    {
        $selectString = 'select * from sometable';
        $random = 'random_table';

        $this->randomMock->expects($this->once())
            ->method('getUniqueHash')
            ->willReturn($random);

        $this->adapterMock->expects($this->once())
            ->method('query')
            ->with($expectedSelect)
            ->willReturnSelf();

        $this->adapterMock->expects($this->once())
            ->method('query')
            ->willReturnSelf();

        $this->adapterMock->expects($this->any())
            ->method('quoteIdentifier')
            ->willReturnArgument(0);

        $this->selectMock->expects($this->once())
            ->method('getBind')
            ->willReturn(['bind']);

        $this->selectMock->expects($this->any())
            ->method('__toString')
            ->willReturn($selectString);

        $this->assertEquals(
            $random,
            $this->temporaryTableService->createFromSelect(
                $this->selectMock,
                $this->adapterMock,
                $indexes
            )
        );
    }

    /**
     * Run test dropTable method when createdTables array of TemporaryTableService is empty
     *
     * @return void
     */
    public function testDropTableWhenCreatedTablesArrayIsEmpty()
    {
        $this->assertFalse($this->temporaryTableService->dropTable('tmp_select_table'));
    }

    /**
     * Run test dropTable method when data exists in createdTables array of TemporaryTableService
     *
     * @param string $tableName
     * @param bool $assertion
     *
     * @dataProvider dropTableWhenCreatedTablesArrayNotEmptyDataProvider
     * @return void
     */
    public function testDropTableWhenCreatedTablesArrayNotEmpty($tableName, $assertion)
    {
        $createdTableAdapters = new \ReflectionProperty($this->temporaryTableService, 'createdTableAdapters');
        $createdTableAdapters->setAccessible(true);
        $createdTableAdapters->setValue($this->temporaryTableService, ['tmp_select_table' => $this->adapterMock]);
        $createdTableAdapters->setAccessible(false);

        $this->adapterMock->expects($this->any())
            ->method('dropTemporaryTable')
            ->willReturn(true);

        $this->assertEquals($this->temporaryTableService->dropTable($tableName), $assertion);
    }

    /**
     * @return array
     */
    public static function createFromSelectDataProvider()
    {
        return [
            [
                ['PRIMARY' => ['primary_column_name']],
                'CREATE TEMPORARY TABLE random_table (PRIMARY KEY(primary_column_name)) ENGINE=INNODB IGNORE '
                . '(select * from sometable)'
            ],
            [
                ['UNQ_INDX' => ['column1', 'column2']],
                'CREATE TEMPORARY TABLE random_table (UNIQUE UNQ_INDX(column1,column2)) ENGINE=INNODB IGNORE '
                . '(select * from sometable)'
            ],
            [
                ['OTH_INDX' => ['column3', 'column4']],
                'CREATE TEMPORARY TABLE random_table (INDEX OTH_INDX USING HASH(column3,column4)) ENGINE=INNODB IGNORE '
                . '(select * from sometable)'
            ],
            [
                [
                    'PRIMARY' => ['primary_column_name'],
                    'OTH_INDX' => ['column3', 'column4'],
                    'UNQ_INDX' => ['column1', 'column2']
                ],
                'CREATE TEMPORARY TABLE random_table '
                . '(PRIMARY KEY(primary_column_name),'
                . 'INDEX OTH_INDX USING HASH(column3,column4),UNIQUE UNQ_INDX(column1,column2)) ENGINE=INNODB IGNORE '
                . '(select * from sometable)'
            ],
        ];
    }

    /**
     * @return array
     */
    public static function dropTableWhenCreatedTablesArrayNotEmptyDataProvider()
    {
        return [
            ['tmp_select_table_1', false],
            ['tmp_select_table', true],
        ];
    }
}
