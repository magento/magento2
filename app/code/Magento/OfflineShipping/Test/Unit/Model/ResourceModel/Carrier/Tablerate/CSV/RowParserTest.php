<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\OfflineShipping\Test\Unit\Model\ResourceModel\Carrier\Tablerate\CSV;

use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\CSV\ColumnResolver;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\CSV\RowException;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\CSV\RowParser;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\LocationDirectory;

/**
 * Unit test for Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\CSV\RowParser
 */
class RowParserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var  ColumnResolver|\PHPUnit\Framework\MockObject\MockObject
     */
    private $columnResolverMock;

    /**
     * @var RowParser
     */
    private $rowParser;

    /**
     * @var LocationDirectory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $locationDirectoryMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->locationDirectoryMock = $this->getMockBuilder(LocationDirectory::class)
            ->setMethods(['hasCountryId', 'getCountryId', 'hasRegionId', 'getRegionIds'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->columnResolverMock = $this->getMockBuilder(ColumnResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->rowParser = new RowParser(
            $this->locationDirectoryMock
        );
    }

    /**
     * @return void
     */
    public function testGetColumns()
    {
        $columns = $this->rowParser->getColumns();
        $this->assertIsArray($columns, 'Columns should be array, ' . gettype($columns) . ' given');
        $this->assertNotEmpty($columns);
    }

    /**
     * @return void
     */
    public function testParse()
    {
        $expectedResult = [
            'website_id' => 58,
            'dest_country_id' => '0',
            'dest_region_id' => 0,
            'dest_zip' => '*',
            'condition_name' => 'condition_short_name',
            'condition_value' => 40.0,
            'price' => 350.0,
        ];
        $rowData = ['a', 'b', 'c', 'd', 'e'];
        $rowNumber = 120;
        $websiteId = 58;
        $conditionShortName = 'condition_short_name';
        $conditionFullName = 'condition_full_name';
        $columnValueMap = [
            [ColumnResolver::COLUMN_COUNTRY, $rowData, '*'],
            [ColumnResolver::COLUMN_REGION, $rowData, '*'],
            [ColumnResolver::COLUMN_ZIP, $rowData, ''],
            [$conditionFullName, $rowData, 40],
            [ColumnResolver::COLUMN_PRICE, $rowData, 350],
        ];
        $result = $this->parse(
            $rowData,
            $conditionFullName,
            $rowNumber,
            $websiteId,
            $conditionShortName,
            $columnValueMap
        );
        $this->assertEquals([$expectedResult], $result);
    }

    /**
     * @param array $rowData
     * @param $conditionFullName
     * @param array $columnValueMap
     * @param $expectedMessage
     * @throws null|RowException
     * @dataProvider parseWithExceptionDataProvider
     */
    public function testParseWithException(array $rowData, $conditionFullName, array $columnValueMap, $expectedMessage)
    {
        $this->expectException(\Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\CSV\RowException::class);

        $rowNumber = 120;
        $websiteId = 58;
        $conditionShortName = 'condition_short_name';
        $actualMessage = null;
        $exception = null;
        try {
            $this->parse(
                $rowData,
                $conditionFullName,
                $rowNumber,
                $websiteId,
                $conditionShortName,
                $columnValueMap
            );
        } catch (\Exception $e) {
            $actualMessage = $e->getMessage();
            $exception = $e;
        }
        $this->assertEquals($expectedMessage, $actualMessage);
        throw $exception;
    }

    /**
     * @return array
     */
    public function parseWithExceptionDataProvider()
    {
        $rowData = ['a', 'b', 'c', 'd', 'e'];
        $conditionFullName = 'condition_full_name';
        return [
            [
                $rowData,
                $conditionFullName,
                [
                    [ColumnResolver::COLUMN_COUNTRY, $rowData, 'XX'],
                    [ColumnResolver::COLUMN_REGION, $rowData, '*'],
                    [ColumnResolver::COLUMN_ZIP, $rowData, ''],
                    [$conditionFullName, $rowData, 40],
                    [ColumnResolver::COLUMN_PRICE, $rowData, 350],
                ],
                'The "XX" country in row number "120" is incorrect. Verify the country and try again.',
            ],
            [
                $rowData,
                $conditionFullName,
                [
                    [ColumnResolver::COLUMN_COUNTRY, $rowData, '*'],
                    [ColumnResolver::COLUMN_REGION, $rowData, 'AA'],
                    [ColumnResolver::COLUMN_ZIP, $rowData, ''],
                    [$conditionFullName, $rowData, 40],
                    [ColumnResolver::COLUMN_PRICE, $rowData, 350],
                ],
                'The "AA" region or state in row number "120" is incorrect. Verify the region or state and try again.',
            ],
            [
                $rowData,
                $conditionFullName,
                [
                    [ColumnResolver::COLUMN_COUNTRY, $rowData, '*'],
                    [ColumnResolver::COLUMN_REGION, $rowData, '*'],
                    [ColumnResolver::COLUMN_ZIP, $rowData, ''],
                    [$conditionFullName, $rowData, 'QQQ'],
                    [ColumnResolver::COLUMN_PRICE, $rowData, 350],
                ],
                'Please correct condition_full_name "QQQ" in the Row #120.',
            ],
            [
                $rowData,
                $conditionFullName,
                [
                    [ColumnResolver::COLUMN_COUNTRY, $rowData, '*'],
                    [ColumnResolver::COLUMN_REGION, $rowData, '*'],
                    [ColumnResolver::COLUMN_ZIP, $rowData, ''],
                    [$conditionFullName, $rowData, 40],
                    [ColumnResolver::COLUMN_PRICE, $rowData, 'BBB'],
                ],
                'The "BBB" shipping price in row number "120" is incorrect. Verify the shipping price and try again.',
            ],
        ];
    }

    /**
     * @param $rowData
     * @param $conditionFullName
     * @param $rowNumber
     * @param $websiteId
     * @param $conditionShortName
     * @return array
     * @throws \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\CSV\RowException
     */
    private function parse($rowData, $conditionFullName, $rowNumber, $websiteId, $conditionShortName, $columnValueMap)
    {
        $this->columnResolverMock->expects($this->any())
            ->method('getColumnValue')
            ->willReturnMap($columnValueMap);
        $result = $this->rowParser->parse(
            $rowData,
            $rowNumber,
            $websiteId,
            $conditionShortName,
            $conditionFullName,
            $this->columnResolverMock
        );
        return $result;
    }
}
