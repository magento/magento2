<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OfflineShipping\Test\Unit\Model\ResourceModel\Carrier\Tablerate;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\File\ReadInterface;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\CSV\ColumnResolver;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\CSV\ColumnResolverFactory;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\CSV\RowParser;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\DataHashGenerator;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\Import;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\Import
 */
class ImportTest extends TestCase
{
    /**
     * @var Import
     */
    private $import;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystemMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var RowParser|MockObject
     */
    private $rowParserMock;

    /**
     * @var MockObject
     */
    private $columnResolverFactoryMock;

    /**
     * @var DataHashGenerator|MockObject
     */
    private $dataHashGeneratorMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->rowParserMock = $this->createMock(RowParser::class);
        $this->columnResolverFactoryMock = $this->createPartialMock(ColumnResolverFactory::class, ['create']);
        $this->dataHashGeneratorMock = $this->createMock(DataHashGenerator::class);
        $this->dataHashGeneratorMock->expects($this->any())
            ->method('getHash')
            ->willReturnCallback(
                function (array $data) {
                    return implode('_', $data);
                }
            );

        $this->import = new Import(
            $this->storeManagerMock,
            $this->filesystemMock,
            $this->scopeConfigMock,
            $this->rowParserMock,
            $this->columnResolverFactoryMock,
            $this->dataHashGeneratorMock
        );
    }

    /**
     * @return void
     */
    public function testGetColumns(): void
    {
        $columns = ['column_1', 'column_2'];
        $this->rowParserMock->expects($this->once())
            ->method('getColumns')
            ->willReturn($columns);
        $result = $this->import->getColumns();
        $this->assertEquals($columns, $result);
    }

    /**
     * @return void
     */
    public function testGetData(): void
    {
        $lines = [
            ['header_1', 'header_2', 'header_3', 'header_4', 'header_5'],
            ['a1', 'b1', 'c1', 'd1', 'e1'],
            ['a2', 'b2', 'c2', 'd2', 'e2'],
            ['a3', 'b3', 'c3', 'd3', 'e3'],
            ['a4', 'b4', 'c4', 'd4', 'e4'],
            ['a5', 'b5', 'c5', 'd5', 'e5']
        ];
        $this->rowParserMock->expects($this->any())
            ->method('parse')
            ->willReturn(
                [['a1', 'b1', 'c1', 'd1', 'e1']],
                [['a2', 'b2', 'c2', 'd2', 'e2']],
                [['a3', 'b3', 'c3', 'd3', 'e3']],
                [['a4', 'b4', 'c4', 'd4', 'e4']],
                [['a5', 'b5', 'c5', 'd5', 'e5']]
            );
        $file = $this->createFileMock($lines);
        $expectedResult = [
            [
                $lines[1],
                $lines[2]
            ],
            [
                $lines[3],
                $lines[4]
            ],
            [
                $lines[5]
            ]
        ];

        $columnResolver = $this->getMockBuilder(ColumnResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->columnResolverFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(['headers' => $lines[0]])
            ->willReturn($columnResolver);

        $result = [];
        foreach ($this->import->getData($file, 1, 'short_name', 'full_name', 2) as $bunch) {
            $result[] = $bunch;
        }
        $this->assertEquals($expectedResult, $result);
        $this->assertFalse($this->import->hasErrors());
        $this->assertEquals([], $this->import->getErrors());
    }

    /**
     * @return void
     */
    public function testGetDataWithDuplicatedLine(): void
    {
        $lines = [
            ['header_1', 'header_2', 'header_3', 'header_4', 'header_5'],
            ['a1', 'b1', 'c1', 'd1', 'e1'],
            ['a1', 'b1', 'c1', 'd1', 'e1'],
            [],
            ['a2', 'b2', 'c2', 'd2', 'e2']
        ];
        $this->rowParserMock->expects($this->any())
            ->method('parse')
            ->willReturn(
                [['a1', 'b1', 'c1', 'd1', 'e1']],
                [['a1', 'b1', 'c1', 'd1', 'e1']],
                [['a2', 'b2', 'c2', 'd2', 'e2']]
            );
        $file = $this->createFileMock($lines);
        $expectedResult = [
            [
                $lines[1],
                $lines[4]
            ]
        ];

        $columnResolver = $this->getMockBuilder(ColumnResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->columnResolverFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(['headers' => $lines[0]])
            ->willReturn($columnResolver);

        $result = [];
        foreach ($this->import->getData($file, 1, 'short_name', 'full_name', 2) as $bunch) {
            $result[] = $bunch;
        }
        $this->assertEquals($expectedResult, $result);
        $this->assertTrue($this->import->hasErrors());
        $this->assertEquals(['Duplicate Row #3 (duplicates row #2)'], $this->import->getErrors());
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testGetDataFromEmptyFile(): void
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('The Table Rates File Format is incorrect. Verify the format and try again.');
        $lines = [];
        $file = $this->createFileMock($lines);
        foreach ($this->import->getData($file, 1, 'short_name', 'full_name', 2) as $bunch) {
            $this->assertTrue(false, 'Exception about empty header is not thrown');
        }
    }

    /**
     * @param array $lines
     *
     * @return ReadInterface|MockObject
     */
    private function createFileMock(array $lines): MockObject
    {
        $file = $this->createMock(ReadInterface::class);
        $willReturnArgs = [];

        foreach ($lines as $line) {
            $willReturnArgs[] = $line;
        }
        $willReturnArgs[] = false;
        $file
            ->method('readCsv')
            ->willReturnOnConsecutiveCalls(...$willReturnArgs);

        return $file;
    }
}
