<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Link\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitation;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;

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

    /** @var \Magento\Framework\Data\Collection\EntityFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $entityFactoryMock;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $loggerMock;

    /** @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $fetchStrategyMock;

    /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $managerInterfaceMock;

    /** @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $configMock;

    /** @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject */
    protected $resourceMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $entityFactoryMock2;

    /** @var \Magento\Catalog\Model\ResourceModel\Helper|\PHPUnit_Framework_MockObject_MockObject */
    protected $helperMock;

    /** @var \Magento\Framework\Validator\UniversalFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $universalFactoryMock;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManagerMock;

    /** @var \Magento\Catalog\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $catalogHelperMock;

    /** @var \Magento\Catalog\Model\Indexer\Product\Flat\State|\PHPUnit_Framework_MockObject_MockObject */
    protected $stateMock;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfigInterfaceMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $optionFactoryMock;

    /** @var \Magento\Catalog\Model\ResourceModel\Url|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlMock;

    /** @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $timezoneInterfaceMock;

    /** @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $sessionMock;

    /** @var \Magento\Framework\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject */
    protected $dateTimeMock;

    protected function setUp()
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
        $entity->expects($this->any())->method('getConnection')->will($this->returnValue($connection));
        $entity->expects($this->any())->method('getDefaultAttributes')->will($this->returnValue([]));
        $this->universalFactoryMock = $this->createMock(\Magento\Framework\Validator\UniversalFactory::class);
        $this->universalFactoryMock->expects($this->any())->method('create')->will($this->returnValue($entity));
        $this->storeManagerMock = $this->getMockForAbstractClass(\Magento\Store\Model\StoreManagerInterface::class);
        $this->storeManagerMock
            ->expects($this->any())
            ->method('getStore')
            ->will($this->returnCallback(
                function ($store) {
                    return is_object($store) ? $store : new \Magento\Framework\DataObject(['id' => 42]);
                }
            ));
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
            ]
        );
    }

    public function testSetProduct()
    {
        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $product */
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $product->expects($this->any())->method('getId')->will($this->returnValue('5'));
        $productStore = new \Magento\Framework\DataObject(['id' => 33]);
        $product->expects($this->any())->method('getStore')->will($this->returnValue($productStore));
        $this->collection->setProduct($product);
        $this->assertEquals(33, $this->collection->getStoreId());
    }
}
