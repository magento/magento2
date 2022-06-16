<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel;

use Magento\Catalog\Model\Factory;
use Magento\Catalog\Model\Indexer\Category\Product\Processor;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\TreeFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Eav\Model\Entity\Context;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface as Adapter;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryTest extends TestCase
{
    private const STUB_PRIMARY_KEY = 'PK';

    /**
     * @var Category
     */
    protected $category;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

    /**
     * @var Adapter|MockObject
     */
    private $connectionMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceMock;

    /**
     * @var Config|MockObject
     */
    private $eavConfigMock;

    /**
     * @var Type|MockObject
     */
    private $entityType;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var Factory|MockObject
     */
    protected $factoryMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $managerMock;

    /**
     * @var Category\TreeFactory|MockObject
     */
    protected $treeFactoryMock;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var Json|MockObject
     */
    private $serializerMock;

    /**
     * @var Processor|MockObject
     */
    private $indexerProcessorMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->selectMock
            ->method('where')
            ->willReturn($this->selectMock);
        $this->selectMock->expects($this->once())->method('from')->willReturnSelf();
        $this->selectMock->expects($this->once())->method('joinLeft')->willReturnSelf();
        $this->connectionMock = $this->getMockBuilder(Adapter::class)->getMockForAbstractClass();
        $this->connectionMock->expects($this->once())->method('select')->willReturn($this->selectMock);
        $this->resourceMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);
        $this->connectionMock->expects($this->any())->method('getTableName')->willReturn('TableName');
        $this->resourceMock->expects($this->any())->method('getTableName')->willReturn('TableName');
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eavConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityType = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eavConfigMock->expects($this->any())->method('getEntityType')->willReturn($this->entityType);
        $this->contextMock->expects($this->any())->method('getEavConfig')->willReturn($this->eavConfigMock);
        $this->contextMock->expects($this->any())->method('getResource')->willReturn($this->resourceMock);
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMock();
        $this->factoryMock = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->managerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMock();
        $this->treeFactoryMock = $this->getMockBuilder(TreeFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->indexerProcessorMock = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->getMock();

        $this->category = new Category(
            $this->contextMock,
            $this->storeManagerMock,
            $this->factoryMock,
            $this->managerMock,
            $this->treeFactoryMock,
            $this->collectionFactoryMock,
            $this->indexerProcessorMock,
            [],
            $this->serializerMock
        );
    }

    /**
     * @return void
     */
    public function testFindWhereAttributeIs(): void
    {
        $entityIdsFilter = [1, 2];
        $expectedValue = 123;
        $attribute = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $backendModel = $this->getMockBuilder(AbstractBackend::class)
            ->disableOriginalConstructor()
            ->getMock();

        $attribute->expects($this->any())->method('getBackend')->willReturn($backendModel);
        $this->connectionMock->expects($this->once())->method('fetchCol')->willReturn(['result']);
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );

        $this->connectionMock->method('getPrimaryKeyName')->willReturn(self::STUB_PRIMARY_KEY);
        $this->connectionMock->method('getIndexList')
            ->willReturn(
                [
                    self::STUB_PRIMARY_KEY => [
                        'COLUMNS_LIST' => ['Column']
                    ]
                ]
            );

        $result = $this->category->findWhereAttributeIs($entityIdsFilter, $attribute, $expectedValue);
        $this->assertEquals(['result'], $result);
    }
}
