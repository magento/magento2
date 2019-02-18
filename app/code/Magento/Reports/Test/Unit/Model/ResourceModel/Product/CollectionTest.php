<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Unit\Model\ResourceModel\Product;

use Magento\Catalog\Model\Indexer\Product\Flat\State;
use Magento\Catalog\Model\Product\Attribute\DefaultAttributes;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\ResourceModel\Helper;
use Magento\Catalog\Model\ResourceModel\Product as ResourceProduct;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;
use Magento\Catalog\Model\ResourceModel\Url;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Context;
use Magento\Eav\Model\Entity\Type;
use Magento\Eav\Model\EntityFactory as EavEntityFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\ResourceModelPoolInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Quote\Model\ResourceModel\Quote\Collection;
use Magento\Reports\Model\Event\TypeFactory;
use Magento\Reports\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Test for Magento\Reports\Model\ResourceModel\Product\Collection.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductCollection
     */
    private $collection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $eventTypeFactoryMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $selectMock;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->selectMock = $this->createPartialMock(
            Select::class,
            [
                'reset',
                'from',
                'join',
                'where',
                'group',
                'order',
                'having',
            ]
        );
        $this->connectionMock = $this->createMock(AdapterInterface::class);
        $this->connectionMock->expects($this->atLeastOnce())->method('select')->willReturn($this->selectMock);
        $this->resourceMock = $this->createPartialMock(ResourceConnection::class, ['getTableName', 'getConnection']);
        $this->resourceMock->expects($this->atLeastOnce())->method('getTableName')->willReturn('test_table');
        $this->resourceMock->expects($this->atLeastOnce())->method('getConnection')->willReturn($this->connectionMock);
        $eavConfig = $this->createPartialMock(Config::class, ['getEntityType']);
        $eavConfig->expects($this->atLeastOnce())->method('getEntityType')->willReturn($this->createMock(Type::class));
        $context = $this->createPartialMock(Context::class, ['getResource', 'getEavConfig']);
        $context->expects($this->atLeastOnce())->method('getResource')->willReturn($this->resourceMock);
        $context->expects($this->atLeastOnce())->method('getEavConfig')->willReturn($eavConfig);
        $storeMock = $this->createMock(StoreInterface::class);
        $storeMock->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $storeManagerMock->expects($this->atLeastOnce())->method('getStore')->willReturn($storeMock);
        $productMock = $this->objectManager->getObject(
            ResourceProduct::class,
            [
                'context' => $context,
                'defaultAttributes' => $this->createPartialMock(
                    DefaultAttributes::class,
                    ['_getDefaultAttributes']
                )
            ]
        );
        $resourceModelPoolMock = $this->createMock(ResourceModelPoolInterface::class);
        $resourceModelPoolMock->expects($this->atLeastOnce())->method('get')->willReturn($productMock);
        $this->eventTypeFactoryMock = $this->createMock(TypeFactory::class);

        $this->collection = new ProductCollection(
            $this->createMock(EntityFactory::class),
            $this->createMock(LoggerInterface::class),
            $this->createMock(FetchStrategyInterface::class),
            $this->createMock(ManagerInterface::class),
            $this->createMock(Config::class),
            $this->resourceMock,
            $this->createMock(EavEntityFactory::class),
            $this->createMock(Helper::class),
            $this->createMock(UniversalFactory::class),
            $storeManagerMock,
            $this->createMock(Manager::class),
            $this->createMock(State::class),
            $this->createMock(ScopeConfigInterface::class),
            $this->createMock(OptionFactory::class),
            $this->createMock(Url::class),
            $this->createMock(TimezoneInterface::class),
            $this->createMock(Session::class),
            $this->createMock(DateTime::class),
            $this->createMock(GroupManagementInterface::class),
            $productMock,
            $this->eventTypeFactoryMock,
            $this->createMock(ProductType::class),
            $this->createMock(Collection::class),
            $this->connectionMock,
            $this->createMock(ProductLimitationFactory::class),
            $this->createMock(MetadataPool::class),
            $this->createMock(\Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer::class),
            $this->createMock(\Magento\Catalog\Model\Indexer\Product\Price\PriceTableResolver::class),
            $this->createMock(\Magento\Framework\Indexer\DimensionFactory::class),
            $resourceModelPoolMock
        );
    }

    /**
     * Test addViewsCount behavior.
     */
    public function testAddViewsCount()
    {
        $context = $this->createPartialMock(
            \Magento\Framework\Model\ResourceModel\Db\Context::class,
            ['getResources']
        );
        $context->expects($this->atLeastOnce())
            ->method('getResources')
            ->willReturn($this->resourceMock);
        $abstractResourceMock = $this->getMockForAbstractClass(
            \Magento\Framework\Model\ResourceModel\Db\AbstractDb::class,
            ['context' => $context],
            '',
            true,
            true,
            true,
            [
                'getTableName',
                'getConnection',
                'getMainTable',
            ]
        );

        $abstractResourceMock->expects($this->atLeastOnce())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $abstractResourceMock->expects($this->atLeastOnce())
            ->method('getMainTable')
            ->willReturn('catalog_product');

        /** @var \Magento\Reports\Model\ResourceModel\Event\Type\Collection $eventTypesCollection */
        $eventTypesCollection = $this->objectManager->getObject(
            \Magento\Reports\Model\ResourceModel\Event\Type\Collection::class,
            ['resource' => $abstractResourceMock]
        );
        $eventTypeMock = $this->createPartialMock(
            \Magento\Reports\Model\Event\Type::class,
            [
                'getEventName',
                'getId',
                'getCollection',
            ]
        );

        $eventTypesCollection->addItem($eventTypeMock);

        $this->eventTypeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eventTypeMock);
        $eventTypeMock->expects($this->atLeastOnce())
            ->method('getCollection')
            ->willReturn($eventTypesCollection);
        $eventTypeMock->expects($this->atLeastOnce())
            ->method('getEventName')
            ->willReturn('catalog_product_view');
        $eventTypeMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(1);

        $this->selectMock->expects($this->atLeastOnce())
            ->method('reset')
            ->willReturn($this->selectMock);
        $this->selectMock->expects($this->atLeastOnce())
            ->method('from')
            ->with(
                ['report_table_views' => 'test_table'],
                ['views' => 'COUNT(report_table_views.event_id)']
            )->willReturn($this->selectMock);
        $this->selectMock->expects($this->atLeastOnce())
            ->method('join')
            ->with(
                ['e' => 'test_table'],
                'e.entity_id = report_table_views.object_id'
            )->willReturn($this->selectMock);
        $this->selectMock->expects($this->atLeastOnce())
            ->method('where')
            ->with('report_table_views.event_type_id = ?', 1)
            ->willReturn($this->selectMock);
        $this->selectMock->expects($this->atLeastOnce())
            ->method('group')
            ->with('e.entity_id')
            ->willReturn($this->selectMock);
        $this->selectMock->expects($this->atLeastOnce())
            ->method('order')
            ->with('views DESC')
            ->willReturn($this->selectMock);
        $this->selectMock->expects($this->atLeastOnce())
            ->method('having')
            ->with('COUNT(report_table_views.event_id) > ?', 0)
            ->willReturn($this->selectMock);

        $this->collection->addViewsCount();
    }
}
