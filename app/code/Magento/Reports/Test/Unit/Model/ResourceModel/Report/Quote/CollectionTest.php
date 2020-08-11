<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Model\ResourceModel\Report\Quote;

use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\ResourceModel\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var MockObject
     */
    protected $selectMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->selectMock = $this->createMock(Select::class);
    }

    public function testGetSelectCountSql()
    {
        /** @var MockObject $collection */
        $collection = $this->getMockBuilder(\Magento\Reports\Model\ResourceModel\Quote\Collection::class)
            ->setMethods(['getSelect'])
            ->disableOriginalConstructor()
            ->getMock();

        $collection->expects($this->atLeastOnce())->method('getSelect')->willReturn($this->selectMock);
        $this->selectMock->expects($this->atLeastOnce())->method('reset')->willReturnSelf();
        $this->selectMock->expects($this->once())
            ->method('columns')
            ->with('COUNT(*)')
            ->willReturnSelf();
        $this->assertEquals($this->selectMock, $collection->getSelectCountSql());
    }

    public function testPrepareActiveCartItems()
    {
        /** @var MockObject $collection */
        $constructArgs = $this->objectManager
            ->getConstructArguments(\Magento\Reports\Model\ResourceModel\Quote\Item\Collection::class);
        $collection = $this->getMockBuilder(\Magento\Reports\Model\ResourceModel\Quote\Item\Collection::class)
            ->setMethods(['getSelect', 'getTable'])
            ->disableOriginalConstructor()
            ->setConstructorArgs($constructArgs)
            ->getMock();

        $collection->expects($this->once())->method('getSelect')->willReturn($this->selectMock);
        $this->selectMock->expects($this->once())->method('reset')->willReturnSelf();
        $this->selectMock->expects($this->once())->method('from')->willReturnSelf();
        $this->selectMock->expects($this->atLeastOnce())->method('columns')->willReturnSelf();
        $this->selectMock->expects($this->once())->method('joinInner')->willReturnSelf();
        $this->selectMock->expects($this->once())->method('where')->willReturnSelf();
        $this->selectMock->expects($this->once())->method('group')->willReturnSelf();
        $collection->expects($this->exactly(2))->method('getTable')->willReturn('table');
        $collection->prepareActiveCartItems();
    }

    public function testLoadWithFilter()
    {
        /** @var MockObject $collection */
        $constructArgs = $this->objectManager
            ->getConstructArguments(\Magento\Reports\Model\ResourceModel\Quote\Item\Collection::class);
        $constructArgs['eventManager'] = $this->getMockForAbstractClass(ManagerInterface::class);
        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $resourceMock = $this->createMock(Quote::class);
        $resourceMock->expects($this->any())->method('getConnection')
            ->willReturn($this->createMock(Mysql::class));
        $constructArgs['resource'] = $resourceMock;
        $productResourceMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        $constructArgs['productResource'] = $productResourceMock;
        $orderResourceMock = $this->createMock(\Magento\Sales\Model\ResourceModel\Order\Collection::class);
        $constructArgs['orderResource'] = $orderResourceMock;
        $collection = $this->getMockBuilder(\Magento\Reports\Model\ResourceModel\Quote\Item\Collection::class)
            ->setMethods(
                [
                    '_beforeLoad',
                    '_renderFilters',
                    '_renderOrders',
                    '_renderLimit',
                    'printLogQuery',
                    'getData',
                    '_setIsLoaded',
                    'setConnection',
                    '_initSelect',
                    'getTable',
                    'getItems',
                    'getOrdersData'
                ]
            )
            ->setConstructorArgs($constructArgs)
            ->getMock();
        //load()
        $collection->expects($this->once())->method('_beforeLoad')->willReturnSelf();
        $collection->expects($this->once())->method('_renderFilters')->willReturnSelf();
        $collection->expects($this->once())->method('_renderOrders')->willReturnSelf();
        $collection->expects($this->once())->method('_renderLimit')->willReturnSelf();
        $collection->expects($this->once())->method('printLogQuery')->willReturnSelf();
        $collection->expects($this->once())->method('getData')->willReturn(null);
        $collection->expects($this->once())->method('_setIsLoaded')->willReturnSelf();
        //productLoad()
        $productAttributeMock = $this->createMock(AbstractAttribute::class);
        $priceAttributeMock = $this->createMock(AbstractAttribute::class);
        $productResourceMock->expects($this->once())->method('getConnection')->willReturn($connectionMock);
        $productResourceMock->expects($this->any())->method('getAttribute')
            ->willReturnMap([['name', $productAttributeMock], ['price', $priceAttributeMock]]);
        $productResourceMock->expects($this->once())->method('getSelect')->willReturn($this->selectMock);
        $eavEntity = $this->createMock(AbstractEntity::class);
        $eavEntity->expects($this->once())->method('getLinkField')->willReturn('entity_id');
        $productResourceMock->expects($this->once())->method('getEntity')->willReturn($eavEntity);
        $this->selectMock->expects($this->once())->method('reset')->willReturnSelf();
        $this->selectMock->expects($this->once())->method('from')->willReturnSelf();
        $this->selectMock->expects($this->once())->method('useStraightJoin')->willReturnSelf();
        $this->selectMock->expects($this->once())->method('joinInner')->willReturnSelf();
        $this->selectMock->expects($this->once())->method('joinLeft')->willReturnSelf();
        $collection->expects($this->once())->method('getOrdersData')->willReturn([]);
        $productAttributeMock->expects($this->once())->method('getBackend')->willReturnSelf();
        $priceAttributeMock->expects($this->once())->method('getBackend')->willReturnSelf();
        $connectionMock->expects($this->once())->method('fetchAssoc')->willReturn([1, 2, 3]);
        //_afterLoad()
        $collection->expects($this->once())->method('getItems')->willReturn([]);
        $collection->loadWithFilter();
    }
}
