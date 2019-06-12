<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\ReportXml;

use Magento\Analytics\ReportXml\Query;
use Magento\Analytics\ReportXml\SelectHydrator as selectHydrator;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

<<<<<<< HEAD
/**
 * Class QueryTest
 */
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
class QueryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selectMock;

    /**
     * @var selectHydrator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selectHydratorMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Query
     */
    private $query;

    /**
     * @var string
     */
    private $connectionName = 'test_connection';

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

<<<<<<< HEAD
        $this->selectHydratorMock = $this->getMockBuilder(SelectHydrator::class)
=======
        $this->selectHydratorMock = $this->getMockBuilder(selectHydrator::class)
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->query = $this->objectManagerHelper->getObject(
            Query::class,
            [
                'select' => $this->selectMock,
                'connectionName' => $this->connectionName,
                'selectHydrator' => $this->selectHydratorMock,
                'config' => []
            ]
        );
    }

    /**
     * @return void
     */
    public function testJsonSerialize()
    {
        $selectParts = ['part' => 1];

        $this->selectHydratorMock
            ->expects($this->once())
            ->method('extract')
            ->with($this->selectMock)
            ->willReturn($selectParts);

        $expectedResult = [
            'connectionName' => $this->connectionName,
            'select_parts' => $selectParts,
            'config' => []
        ];

        $this->assertSame($expectedResult, $this->query->jsonSerialize());
    }
}
