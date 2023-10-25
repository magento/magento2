<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Model\ResourceModel\Report\Quote;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\ResourceModel\Quote;
use Magento\Reports\Model\Product\DataRetriever as ProductDataRetriever;
use Magento\Reports\Model\ResourceModel\Quote\Collection as QuoteCollection;
use Magento\Reports\Model\ResourceModel\Quote\Item\Collection as QuoteItemCollection;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
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

    /**
     * @var ProductDataRetriever|MockObject
     */
    private $productDataRetriever;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->selectMock = $this->createMock(Select::class);
        $this->productDataRetriever = $this->createMock(ProductDataRetriever::class);
    }

    public function testGetSelectCountSql()
    {
        /** @var MockObject $collection */
        $collection = $this->getMockBuilder(QuoteCollection::class)
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
        static $i = 0;
        /** @var MockObject $collection */
        $constructArgs = $this->objectManager
            ->getConstructArguments(QuoteItemCollection::class);
        $collection = $this->getMockBuilder(QuoteItemCollection::class)
            ->setMethods(['getSelect', 'getTable', 'getFlag', 'setFlag'])
            ->disableOriginalConstructor()
            ->setConstructorArgs($constructArgs)
            ->getMock();

        $collection->expects($this->exactly(2))->method('getSelect')->willReturn($this->selectMock);
        $this->selectMock->expects($this->once())->method('reset')->willReturnSelf();
        $this->selectMock->expects($this->once())->method('from')->willReturnSelf();
        $this->selectMock->expects($this->atLeastOnce())->method('columns')
            ->with(self::callback(function ($columns) use (&$i) {
                switch ($i) {
                    case 0:
                        $this->assertContains('main_table.product_id', $columns);
                        $this->assertContains('main_table.name', $columns);
                        $this->assertContains('main_table.price', $columns);
                        $i++;
                        break;
                    case 1:
                        $this->assertEquals(['carts' => new \Zend_Db_Expr('COUNT(main_table.item_id)')], $columns);
                        $i++;
                        break;
                    case 2:
                        $this->assertEquals('quote.base_to_global_rate', $columns);
                        $i++;
                }
                return true;
            }))->willReturnSelf();
        $this->selectMock->expects($this->once())->method('joinInner')->willReturnSelf();
        $this->selectMock->expects($this->once())->method('where')->willReturnSelf();
        $this->selectMock->expects($this->once())->method('group')->willReturnSelf();
        $collection->expects($this->exactly(2))->method('getTable')->willReturn('table');
        $collection->expects($this->once())->method('setFlag')
            ->with('reports_collection_prepared')->willReturnSelf();
        $collection->prepareActiveCartItems();
        $collection->method('getFlag')
            ->with('reports_collection_prepared')->willReturn(true);
        $this->assertEquals($this->selectMock, $collection->prepareActiveCartItems());
    }

    public function testLoadWithFilter()
    {
        /** @var MockObject $collection */
        $constructArgs = $this->objectManager
            ->getConstructArguments(QuoteItemCollection::class);
        $constructArgs['eventManager'] = $this->getMockForAbstractClass(ManagerInterface::class);
        $resourceMock = $this->createMock(Quote::class);
        $resourceMock->expects($this->any())->method('getConnection')
            ->willReturn($this->createMock(Mysql::class));
        $constructArgs['resource'] = $resourceMock;
        $productResourceMock = $this->createMock(ProductCollection::class);
        $constructArgs['productResource'] = $productResourceMock;
        $orderResourceMock = $this->createMock(OrderCollection::class);
        $constructArgs['orderResource'] = $orderResourceMock;
        $constructArgs['productDataRetriever'] = $this->productDataRetriever;
        $collection = $this->getMockBuilder(QuoteItemCollection::class)
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
        $productResourceMock->expects($this->any())->method('getAttribute')
            ->willReturnMap([['name', $productAttributeMock], ['price', $priceAttributeMock]]);
        $collection->expects($this->once())->method('getOrdersData')->willReturn([]);
        //_afterLoad()
        $collection->expects($this->once())->method('getItems')->willReturn([]);
        $this->productDataRetriever->expects($this->once())->method('execute')->willReturn([]);
        $collection->loadWithFilter();
    }
}
