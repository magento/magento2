<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Link\Product;

use \Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;
use \Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitation;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
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
        $this->entityFactoryMock = $this->getMock(
            \Magento\Framework\Data\Collection\EntityFactory::class,
            [],
            [],
            '',
            false
        );
        $this->loggerMock = $this->getMock(\Psr\Log\LoggerInterface::class);
        $this->fetchStrategyMock = $this->getMock(\Magento\Framework\Data\Collection\Db\FetchStrategyInterface::class);
        $this->managerInterfaceMock = $this->getMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->configMock = $this->getMock(\Magento\Eav\Model\Config::class, [], [], '', false);
        $this->resourceMock = $this->getMock(\Magento\Framework\App\ResourceConnection::class, [], [], '', false);
        $this->entityFactoryMock2 = $this->getMock(\Magento\Eav\Model\EntityFactory::class, [], [], '', false);
        $this->helperMock = $this->getMock(\Magento\Catalog\Model\ResourceModel\Helper::class, [], [], '', false);
        $entity = $this->getMock(\Magento\Eav\Model\Entity\AbstractEntity::class, [], [], '', false);
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
        $this->universalFactoryMock = $this->getMock(
            \Magento\Framework\Validator\UniversalFactory::class,
            [],
            [],
            '',
            false
        );
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
        $this->catalogHelperMock = $this->getMock(\Magento\Catalog\Helper\Data::class, [], [], '', false);
        $this->stateMock = $this->getMock(\Magento\Catalog\Model\Indexer\Product\Flat\State::class, [], [], '', false);
        $this->scopeConfigInterfaceMock = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->optionFactoryMock = $this->getMock(
            \Magento\Catalog\Model\Product\OptionFactory::class,
            [],
            [],
            '',
            false
        );
        $this->urlMock = $this->getMock(\Magento\Catalog\Model\ResourceModel\Url::class, [], [], '', false);
        $this->timezoneInterfaceMock = $this->getMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $this->sessionMock = $this->getMock(\Magento\Customer\Model\Session::class, [], [], '', false);
        $this->dateTimeMock = $this->getMock(\Magento\Framework\Stdlib\DateTime::class);
        $productLimitationFactoryMock = $this->getMock(ProductLimitationFactory::class, ['create']);
        $productLimitationFactoryMock->method('create')
            ->willReturn($this->getMock(ProductLimitation::class));

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
        $product = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $product->expects($this->any())->method('getId')->will($this->returnValue('5'));
        $productStore = new \Magento\Framework\DataObject(['id' => 33]);
        $product->expects($this->any())->method('getStore')->will($this->returnValue($productStore));
        $this->collection->setProduct($product);
        $this->assertEquals(33, $this->collection->getStoreId());
    }
}
