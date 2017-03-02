<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\ReportXml;

use Magento\Analytics\ReportXml\SelectHydrator;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;

class SelectHydratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SelectHydrator
     */
    private $selectHydrator;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    /**
     * @var Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selectMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->selectHydrator = new SelectHydrator($this->resourceConnectionMock);
    }

    public function testExtract()
    {
        $selectParts =
            [
                Select::DISTINCT,
                Select::COLUMNS,
                Select::UNION,
                Select::FROM,
                Select::WHERE,
                Select::GROUP,
                Select::HAVING,
                Select::ORDER,
                Select::LIMIT_COUNT,
                Select::LIMIT_OFFSET,
                Select::FOR_UPDATE
            ];

        $result = [];
        foreach ($selectParts as $part) {
            $result[$part] = "Part";
        }
        $this->selectMock->expects($this->any())
            ->method('getPart')
            ->willReturn("Part");
        $this->assertEquals($this->selectHydrator->extract($this->selectMock), $result);
    }

    public function testRecreate()
    {
        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($this->selectMock);
        $parts =
            [
                Select::DISTINCT,
                Select::COLUMNS,
                Select::UNION,
                Select::FROM,
                Select::WHERE,
                Select::GROUP,
                Select::HAVING,
                Select::ORDER,
                Select::LIMIT_COUNT,
                Select::LIMIT_OFFSET,
                Select::FOR_UPDATE
            ];
        $selectParts = [];
        foreach ($parts as $key => $part) {
            $this->selectMock->expects($this->at($key))
                ->method('setPart')
                ->with($part, 'part' . $key);
            $selectParts[$part] = 'part' . $key;
        }
        $this->selectHydrator->recreate($selectParts);
    }
}
