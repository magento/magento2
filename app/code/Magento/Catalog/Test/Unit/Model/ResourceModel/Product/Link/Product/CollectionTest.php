<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Link\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitation;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection */
    protected $collection;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    private $objectManager;

    /** @var \Magento\Framework\Data\Collection\EntityFactory|\PHPUnit\Framework\MockObject\MockObject */
    protected $entityFactoryMock;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $loggerMock;

    /** @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $fetchStrategyMock;

    /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $managerInterfaceMock;

    /** @var \Magento\Eav\Model\Config|\PHPUnit\Framework\MockObject\MockObject */
    protected $configMock;

    /** @var \Magento\Framework\App\ResourceConnection|\PHPUnit\Framework\MockObject\MockObject */
    protected $resourceMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $entityFactoryMock2;

    /** @var \Magento\Catalog\Model\ResourceModel\Helper|\PHPUnit\Framework\MockObject\MockObject */
    protected $helperMock;

    /** @var \Magento\Framework\Validator\UniversalFactory|\PHPUnit\Framework\MockObject\MockObject */
    protected $universalFactoryMock;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $storeManagerMock;

    /** @var \Magento\Catalog\Helper\Data|\PHPUnit\Framework\MockObject\MockObject */
    protected $catalogHelperMock;

    /** @var \Magento\Catalog\Model\Indexer\Product\Flat\State|\PHPUnit\Framework\MockObject\MockObject */
    protected $stateMock;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $scopeConfigInterfaceMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $optionFactoryMock;

    /** @var \Magento\Catalog\Model\ResourceModel\Url|\PHPUnit\Framework\MockObject\MockObject */
    protected $urlMock;

    /** @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $timezoneInterfaceMock;

    /** @var \Magento\Customer\Model\Session|\PHPUnit\Framework\MockObject\MockObject */
    protected $sessionMock;

    /** @var \Magento\Framework\Stdlib\DateTime|\PHPUnit\Framework\MockObject\MockObject */
    protected $dateTimeMock;

    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->entityFactoryMock = $this->createMock(\Magento\Framework\Data\Collection\EntityFactory::class);
        $this->loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->fetchStrategyMock = $this->createMock(
            \Magento\Framework\Data\Collection\Db\FetchStrategyInterface::class
        );
        $this->managerInterfaceMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->configMock = $this->createMock(\Magento\Eav\Model\Config::class);
        $this->resourceMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->entityFactoryMock2 = $this->createMock(\Magento\Eav\Model\EntityFactory::class);
        $this->helperMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Helper::class);
        $entity = $this->createMock(\Magento\Eav\Model\Entity\AbstractEntity::class);
        $select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\Pdo\Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connection->expects($this->any())
            ->method('select')
            ->willReturn($select);
        $entity->expects($this->any())->method('getConnection')->willReturn($connection);
        $entity->expects($this->any())->method('getDefaultAttributes')->willReturn([]);
        $this->universalFactoryMock = $this->createMock(\Magento\Framework\Validator\UniversalFactory::class);
        $this->universalFactoryMock->expects($this->any())->method('create')->willReturn($entity);
        $this->storeManagerMock = $this->getMockForAbstractClass(\Magento\Store\Model\StoreManagerInterface::class);
        $this->storeManagerMock
            ->expects($this->any())
            ->method('getStore')
            ->willReturnCallback(
                function ($store) {
                    return is_object($store) ? $store : new \Magento\Framework\DataObject(['id' => 42]);
                }
            );
        $this->catalogHelperMock = $this->createMock(\Magento\Catalog\Helper\Data::class);
        $this->stateMock = $this->createMock(\Magento\Catalog\Model\Indexer\Product\Flat\State::class);
        $this->scopeConfigInterfaceMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->optionFactoryMock = $this->createMock(\Magento\Catalog\Model\Product\OptionFactory::class);
        $this->urlMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Url::class);
        $this->timezoneInterfaceMock = $this->createMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $this->sessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->dateTimeMock = $this->createMock(\Magento\Framework\Stdlib\DateTime::class);
        $productLimitationFactoryMock = $this->getMockBuilder(
            ProductLimitationFactory::class
        )->disableOriginalConstructor()->setMethods(['create'])->getMock();

        $productLimitationFactoryMock->method('create')
            ->willReturn($this->createMock(ProductLimitation::class));

        $metadataMock = $this->getMockForAbstractClass(EntityMetadataInterface::class);
        $metadataMock->method('getLinkField')->willReturn('entity_id');
        $metadataPoolMock = $this->getMockBuilder(MetadataPool::class)->disableOriginalConstructor()->getMock();
        $metadataPoolMock->method('getMetadata')->willReturn($metadataMock);

        $this->collection = $this->objectManager->getObject(
            \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection::class,
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
        /** @var \Magento\Catalog\Model\Product|\PHPUnit\Framework\MockObject\MockObject $product */
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $product->expects($this->any())->method('getId')->willReturn('5');
        $productStore = new \Magento\Framework\DataObject(['id' => 33]);
        $product->expects($this->any())->method('getStore')->willReturn($productStore);
        $this->collection->setProduct($product);
        $this->assertEquals(33, $this->collection->getStoreId());
    }
}
