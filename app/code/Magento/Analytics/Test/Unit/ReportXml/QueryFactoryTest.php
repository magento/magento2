<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\ReportXml;

use Magento\Analytics\ReportXml\QueryFactory;
use Magento\Analytics\ReportXml\Query;
use Magento\Analytics\ReportXml\Config;
use Magento\Framework\DB\Select;
use Magento\Analytics\ReportXml\DB\Assembler\AssemblerInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Analytics\ReportXml\SelectHydrator;
use Magento\Analytics\ReportXml\DB\SelectBuilder;
use Magento\Analytics\ReportXml\DB\SelectBuilderFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * A unit test for testing of the query factory.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QueryFactoryTest extends TestCase
{
    const STUB_QUERY_NAME = 'test_query';
    const STUB_CONNECTION = 'default';

    /**
     * @var QueryFactory
     */
    private $subject;

    /**
     * @var Query|MockObject
     */
    private $queryMock;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

    /**
     * @var AssemblerInterface|MockObject
     */
    private $assemblerMock;

    /**
     * @var CacheInterface|MockObject
     */
    private $queryCacheMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var SelectHydrator|MockObject
     */
    private $selectHydratorMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var SelectBuilderFactory|MockObject
     */
    private $selectBuilderFactoryMock;

    /**
     * @var Json|MockObject
     */
    private $jsonSerializerMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->queryMock = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assemblerMock = $this->getMockBuilder(AssemblerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->queryCacheMock = $this->getMockBuilder(CacheInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->selectHydratorMock = $this->getMockBuilder(SelectHydrator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->selectBuilderFactoryMock = $this->getMockBuilder(SelectBuilderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->jsonSerializerMock =  $this->createMock(Json::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->subject = $this->objectManagerHelper->getObject(
            QueryFactory::class,
            [
                'queryCache' => $this->queryCacheMock,
                'selectHydrator' => $this->selectHydratorMock,
                'objectManager' => $this->objectManagerMock,
                'selectBuilderFactory' => $this->selectBuilderFactoryMock,
                'config' => $this->configMock,
                'assemblers' => [$this->assemblerMock],
                'jsonSerializer' => $this->jsonSerializerMock
            ]
        );
    }

    /**
     * Get Query Data Mock
     *
     * @return array
     */
    private function getQueryDataMock(): array
    {
        return [
            'connectionName' => self::STUB_CONNECTION,
            'config' => [
                'name' => self::STUB_QUERY_NAME,
                'connection' => self::STUB_CONNECTION
            ],
            'select_parts' => []
        ];
    }

    /**
     * ObjectManager Mock with Query class
     *
     * @param  array $queryDataMock
     * @return void
     */
    private function createQueryObjectMock($queryDataMock): void
    {
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(
                Query::class,
                [
                    'select' => $this->selectMock,
                    'selectHydrator' => $this->selectHydratorMock,
                    'connectionName' => $queryDataMock['connectionName'],
                    'config' => $queryDataMock['config']
                ]
            )
            ->willReturn($this->queryMock);
    }

    /**
     * Test create() if query cached
     *
     * @return void
     */
    public function testCreateIfQueryCached(): void
    {
        $queryName = self::STUB_QUERY_NAME;
        $queryDataMock = $this->getQueryDataMock();

        $this->queryCacheMock->expects($this->any())
            ->method('load')
            ->with($queryName)
            ->willReturn(json_encode($queryDataMock));

        $this->jsonSerializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn($queryDataMock);

        $this->selectHydratorMock->expects($this->any())
            ->method('recreate')
            ->with([])
            ->willReturn($this->selectMock);

        $this->createQueryObjectMock($queryDataMock);

        $this->queryCacheMock->expects($this->never())
            ->method('save');

        $this->assertEquals(
            $this->queryMock,
            $this->subject->create($queryName)
        );
    }

    /**
     * Test create() if query not cached
     *
     * @return void
     */
    public function testCreateIfQueryNotCached(): void
    {
        $queryName = self::STUB_QUERY_NAME;
        $queryDataMock = $this->getQueryDataMock();
        $queryConfigMock = $queryDataMock['config'];

        $selectBuilderMock = $this->getMockBuilder(SelectBuilder::class)
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

        $this->createQueryObjectMock($queryDataMock);

        $this->jsonSerializerMock->expects($this->once())
            ->method('serialize')
            ->willReturn($this->queryMock);

        $this->queryCacheMock->expects($this->once())
            ->method('save')
            ->with($this->queryMock, $queryName);

        $this->assertEquals(
            $this->queryMock,
            $this->subject->create($queryName)
        );
    }
}
