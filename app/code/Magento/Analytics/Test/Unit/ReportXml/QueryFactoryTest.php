<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\ReportXml;

/**
 * A unit test for testing of the query factory.
 */
class QueryFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Analytics\ReportXml\QueryFactory
     */
    private $subject;

    /**
     * @var \Magento\Analytics\ReportXml\Query|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queryMock;

    /**
     * @var \Magento\Analytics\ReportXml\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selectMock;

    /**
     * @var \Magento\Analytics\ReportXml\DB\Assembler\AssemblerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assemblerMock;

    /**
     * @var \Magento\Framework\App\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queryCacheMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var \Magento\Analytics\ReportXml\SelectHydrator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selectHydratorMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\Analytics\ReportXml\DB\SelectBuilderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selectBuilderFactoryMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->queryMock = $this->getMockBuilder(
            \Magento\Analytics\ReportXml\Query::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $this->configMock = $this->getMockBuilder(
            \Magento\Analytics\ReportXml\Config::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $this->selectMock = $this->getMockBuilder(
            \Magento\Framework\DB\Select::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $this->assemblerMock = $this->getMockBuilder(
            \Magento\Analytics\ReportXml\DB\Assembler\AssemblerInterface::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $this->queryCacheMock = $this->getMockBuilder(
            \Magento\Framework\App\CacheInterface::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $this->objectManagerMock = $this->getMockBuilder(
            \Magento\Framework\ObjectManagerInterface::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $this->selectHydratorMock = $this->getMockBuilder(
            \Magento\Analytics\ReportXml\SelectHydrator::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $this->selectBuilderFactoryMock = $this->getMockBuilder(
            \Magento\Analytics\ReportXml\DB\SelectBuilderFactory::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $this->objectManagerHelper =
            new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->subject = $this->objectManagerHelper->getObject(
            \Magento\Analytics\ReportXml\QueryFactory::class,
            [
                'config' => $this->configMock,
                'selectBuilderFactory' => $this->selectBuilderFactoryMock,
                'assemblers' => [$this->assemblerMock],
                'queryCache' => $this->queryCacheMock,
                'objectManager' => $this->objectManagerMock,
                'selectHydrator' => $this->selectHydratorMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreateCached()
    {
        $queryName = 'test_query';

        $this->queryCacheMock->expects($this->any())
            ->method('load')
            ->with($queryName)
            ->willReturn('{"connectionName":"sales","config":{},"select_parts":{}}');

        $this->selectHydratorMock->expects($this->any())
            ->method('recreate')
            ->with([])
            ->willReturn($this->selectMock);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(
                \Magento\Analytics\ReportXml\Query::class,
                [
                    'select' => $this->selectMock,
                    'selectHydrator' => $this->selectHydratorMock,
                    'connectionName' => 'sales',
                    'config' => []
                ]
            )
            ->willReturn($this->queryMock);

        $this->queryCacheMock->expects($this->never())
            ->method('save');

        $this->assertEquals(
            $this->queryMock,
            $this->subject->create($queryName)
        );
    }

    /**
     * @return void
     */
    public function testCreateNotCached()
    {
        $queryName = 'test_query';

        $queryConfigMock = [
            'name' => 'test_query',
            'connection' => 'sales'
        ];

        $selectBuilderMock = $this->getMockBuilder(
            \Magento\Analytics\ReportXml\DB\SelectBuilder::class
        )
        ->disableOriginalConstructor()
        ->getMock();
        $selectBuilderMock->expects($this->once())
            ->method('setConnectionName')
            ->with($queryConfigMock['connection']);
        $selectBuilderMock->expects($this->any())
            ->method('create')
            ->willReturn($this->selectMock);
        $selectBuilderMock->expects($this->any())
            ->method('getConnectionName')
            ->willReturn($queryConfigMock['connection']);

        $this->queryCacheMock->expects($this->any())
            ->method('load')
            ->with($queryName)
            ->willReturn(null);

        $this->configMock->expects($this->any())
            ->method('get')
            ->with($queryName)
            ->willReturn($queryConfigMock);

        $this->selectBuilderFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($selectBuilderMock);

        $this->assemblerMock->expects($this->once())
            ->method('assemble')
            ->with($selectBuilderMock, $queryConfigMock)
            ->willReturn($selectBuilderMock);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(
                \Magento\Analytics\ReportXml\Query::class,
                [
                    'select' => $this->selectMock,
                    'selectHydrator' => $this->selectHydratorMock,
                    'connectionName' => $queryConfigMock['connection'],
                    'config' => $queryConfigMock
                ]
            )
            ->willReturn($this->queryMock);

        $this->queryCacheMock->expects($this->once())
            ->method('save')
            ->with(json_encode($this->queryMock), $queryName);

        $this->assertEquals(
            $this->queryMock,
            $this->subject->create($queryName)
        );
    }
}
