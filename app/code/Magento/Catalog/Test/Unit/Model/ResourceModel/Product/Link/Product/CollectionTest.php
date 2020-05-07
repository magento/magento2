<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Link\Product;

use Magento\Catalog\Helper\Data;
use Magento\Catalog\Model\Indexer\Product\Flat\State;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Catalog\Model\ResourceModel\Helper;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitation;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Url;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CollectionTest extends TestCase
{
    /** @var Collection */
    protected $collection;

    /** @var ObjectManager */
    private $objectManager;

    /** @var EntityFactory|MockObject */
    protected $entityFactoryMock;

    /** @var LoggerInterface|MockObject */
    protected $loggerMock;

    /** @var FetchStrategyInterface|MockObject */
    protected $fetchStrategyMock;

    /** @var ManagerInterface|MockObject */
    protected $managerInterfaceMock;

    /** @var Config|MockObject */
    protected $configMock;

    /** @var ResourceConnection|MockObject */
    protected $resourceMock;

    /** @var MockObject */
    protected $entityFactoryMock2;

    /** @var Helper|MockObject */
    protected $helperMock;

    /** @var UniversalFactory|MockObject */
    protected $universalFactoryMock;

    /** @var StoreManagerInterface|MockObject */
    protected $storeManagerMock;

    /** @var Data|MockObject */
    protected $catalogHelperMock;

    /** @var State|MockObject */
    protected $stateMock;

    /** @var ScopeConfigInterface|MockObject */
    protected $scopeConfigInterfaceMock;

    /** @var MockObject */
    protected $optionFactoryMock;

    /** @var Url|MockObject */
    protected $urlMock;

    /** @var TimezoneInterface|MockObject */
    protected $timezoneInterfaceMock;

    /** @var Session|MockObject */
    protected $sessionMock;

    /** @var DateTime|MockObject */
    protected $dateTimeMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->entityFactoryMock = $this->createMock(EntityFactory::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->fetchStrategyMock = $this->createMock(
            FetchStrategyInterface::class
        );
        $this->managerInterfaceMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->configMock = $this->createMock(Config::class);
        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->entityFactoryMock2 = $this->createMock(\Magento\Eav\Model\EntityFactory::class);
        $this->helperMock = $this->createMock(Helper::class);
        $entity = $this->createMock(AbstractEntity::class);
        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connection = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connection->expects($this->any())
            ->method('select')
            ->willReturn($select);
        $entity->expects($this->any())->method('getConnection')->willReturn($connection);
        $entity->expects($this->any())->method('getDefaultAttributes')->willReturn([]);
        $this->universalFactoryMock = $this->createMock(UniversalFactory::class);
        $this->universalFactoryMock->expects($this->any())->method('create')->willReturn($entity);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->storeManagerMock
            ->expects($this->any())
            ->method('getStore')
            ->willReturnCallback(
                function ($store) {
                    return is_object($store) ? $store : new DataObject(['id' => 42]);
                }
            );
        $this->catalogHelperMock = $this->createMock(Data::class);
        $this->stateMock = $this->createMock(State::class);
        $this->scopeConfigInterfaceMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->optionFactoryMock = $this->createMock(OptionFactory::class);
        $this->urlMock = $this->createMock(Url::class);
        $this->timezoneInterfaceMock = $this->getMockForAbstractClass(TimezoneInterface::class);
        $this->sessionMock = $this->createMock(Session::class);
        $this->dateTimeMock = $this->createMock(DateTime::class);
        $productLimitationFactoryMock = $this->getMockBuilder(
            ProductLimitationFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])->getMock();

        $productLimitationFactoryMock->method('create')
            ->willReturn($this->createMock(ProductLimitation::class));

        $metadataMock = $this->getMockForAbstractClass(EntityMetadataInterface::class);
        $metadataMock->method('getLinkField')->willReturn('entity_id');
        $metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadataPoolMock->method('getMetadata')->willReturn($metadataMock);

        $this->collection = $this->objectManager->getObject(
            Collection::class,
            [
                'entityFactory' => $this->entityFactoryMock,
                'logger' => $this->loggerMock,
                'fetchStrategy' => $this->fetchStrategyMock,
                'eventManager' => $this->managerInterfaceMock,
                'eavConfig' => $this->configMock,
                'resource' => $this->resourceMock,
                'eavEntityFactory' => $this->entityFactoryMock2,
                'resourceHelper' => $this->helperMock,
                'universalFactory' => $this->universalFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'catalogData' => $this->catalogHelperMock,
                'catalogProductFlatState' => $this->stateMock,
                'scopeConfig' => $this->scopeConfigInterfaceMock,
                'productOptionFactory' => $this->optionFactoryMock,
                'catalogUrl' => $this->urlMock,
                'localeDate' => $this->timezoneInterfaceMock,
                'customerSession' => $this->sessionMock,
                'dateTime' => $this->dateTimeMock,
                'productLimitationFactory' => $productLimitationFactoryMock,
                'metadataPool' => $metadataPoolMock
            ]
        );
    }

    public function testSetProduct()
    {
        /** @var Product|MockObject $product */
        $product = $this->createMock(Product::class);
        $product->expects($this->any())->method('getId')->willReturn('5');
        $productStore = new DataObject(['id' => 33]);
        $product->expects($this->any())->method('getStore')->willReturn($productStore);
        $this->collection->setProduct($product);
        $this->assertEquals(33, $this->collection->getStoreId());
    }
}
