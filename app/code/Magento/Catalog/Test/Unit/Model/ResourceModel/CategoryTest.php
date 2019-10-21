<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\ResourceModel;

use Magento\Catalog\Model\Factory;
use Magento\Catalog\Model\Indexer\Category\Product\Processor;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
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

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Category
     */
    protected $category;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selectMock;

    /**
     * @var Adapter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eavConfigMock;

    /**
     * @var Type|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityType;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $factoryMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $managerMock;

    /**
     * @var Category\TreeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $treeFactoryMock;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * @var Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $indexerProcessorMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->selectMock = $this->getMockBuilder(Select::class)->disableOriginalConstructor()->getMock();
        $this->selectMock->expects($this->at(2))->method('where')->willReturnSelf();
        $this->selectMock->expects($this->once())->method('from')->willReturnSelf();
        $this->selectMock->expects($this->once())->method('joinLeft')->willReturnSelf();
        $this->connectionMock = $this->getMockBuilder(Adapter::class)->getMockForAbstractClass();
        $this->connectionMock->expects($this->once())->method('select')->willReturn($this->selectMock);
        $this->resourceMock = $this->getMockBuilder(ResourceConnection::class)->disableOriginalConstructor()->getMock();
        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);
        $this->connectionMock->expects($this->any())->method('getTableName')->willReturn('TableName');
        $this->resourceMock->expects($this->any())->method('getTableName')->willReturn('TableName');
        $this->contextMock = $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock();
        $this->eavConfigMock = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $this->entityType = $this->getMockBuilder(Type::class)->disableOriginalConstructor()->getMock();
        $this->eavConfigMock->expects($this->any())->method('getEntityType')->willReturn($this->entityType);
        $this->contextMock->expects($this->any())->method('getEavConfig')->willReturn($this->eavConfigMock);
        $this->contextMock->expects($this->any())->method('getResource')->willReturn($this->resourceMock);
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)->getMock();
        $this->factoryMock = $this->getMockBuilder(Factory::class)->disableOriginalConstructor()->getMock();
        $this->managerMock = $this->getMockBuilder(ManagerInterface::class)->getMock();
        $this->treeFactoryMock = $this->getMockBuilder(Category\TreeFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->indexerProcessorMock = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializerMock = $this->getMockBuilder(Json::class)->getMock();

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
    public function testFindWhereAttributeIs()
    {
        $entityIdsFilter = [1, 2];
        $expectedValue = 123;
        $attribute = $this->getMockBuilder(Attribute::class)->disableOriginalConstructor()->getMock();
        $backendModel = $this->getMockBuilder(AbstractBackend::class)->disableOriginalConstructor()->getMock();

        $attribute->expects($this->any())->method('getBackend')->willReturn($backendModel);
        $this->connectionMock->expects($this->once())->method('fetchCol')->willReturn(['result']);
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return json_encode($value);
                    }
                )
            );

        $result = $this->category->findWhereAttributeIs($entityIdsFilter, $attribute, $expectedValue);
        $this->assertEquals(['result'], $result);
    }
}
