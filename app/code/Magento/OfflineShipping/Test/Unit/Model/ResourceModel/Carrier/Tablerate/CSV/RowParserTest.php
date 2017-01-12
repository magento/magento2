<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
class RowParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var  ColumnResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $columnResolverMock;

    /**
     * @var RowParser
     */
    private $rowParser;

    /**
     * @var LocationDirectory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $locationDirectoryMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->locationDirectoryMock = $this->getMockBuilder(LocationDirectory::class)
            ->setMethods(['hasCountryId', 'getCountryId', 'hasRegionId', 'getRegionId'])
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
        $this->assertTrue(is_array($columns), 'Columns should be array, ' . gettype($columns) . ' given');
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
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param array $rowData
     * @param $conditionFullName
     * @param array $columnValueMap
     * @param $expectedMessage
     * @throws null|RowException
     * @dataProvider parseWithExceptionDataProvider
     * @expectedException \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\CSV\RowException
     */
    public function testParseWithException(array $rowData, $conditionFullName, array $columnValueMap, $expectedMessage)
    {
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
                'Please correct Country "XX" in the Row #120.',
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
                'Please correct Region/State "AA" in the Row #120.',
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
                'Please correct Shipping Price "BBB" in the Row #120.',
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
