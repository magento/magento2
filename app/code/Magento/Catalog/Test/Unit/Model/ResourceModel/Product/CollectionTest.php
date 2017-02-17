<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product;

use \Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $collection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $galleryResourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $entityMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPoolMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $galleryReadHandlerMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $entityFactory = $this->getMock(\Magento\Framework\Data\Collection\EntityFactory::class, [], [], '', false);
        $logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $fetchStrategy = $this->getMockBuilder(\Magento\Framework\Data\Collection\Db\FetchStrategyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $eventManager = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $eavConfig = $this->getMockBuilder(\Magento\Eav\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resource = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eavEntityFactory = $this->getMockBuilder(\Magento\Eav\Model\EntityFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resourceHelper = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Helper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $universalFactory = $this->getMockBuilder(\Magento\Framework\Validator\UniversalFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore', 'getId'])
            ->getMockForAbstractClass();
        $moduleManager = $this->getMockBuilder(\Magento\Framework\Module\Manager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $catalogProductFlatState = $this->getMockBuilder(\Magento\Catalog\Model\Indexer\Product\Flat\State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $productOptionFactory = $this->getMockBuilder(\Magento\Catalog\Model\Product\OptionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $catalogUrl = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Url::class)
            ->disableOriginalConstructor()
            ->getMock();
        $localeDate = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerSession = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dateTime = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $groupManagement = $this->getMockBuilder(\Magento\Customer\Api\GroupManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\AbstractEntity::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->galleryResourceMock = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Product\Gallery::class
        )->disableOriginalConstructor()->getMock();

        $this->metadataPoolMock = $this->getMockBuilder(
            \Magento\Framework\EntityManager\MetadataPool::class
        )->disableOriginalConstructor()->getMock();

        $this->galleryReadHandlerMock = $this->getMockBuilder(
            \Magento\Catalog\Model\Product\Gallery\ReadHandler::class
        )->disableOriginalConstructor()->getMock();

        $storeManager->expects($this->any())->method('getId')->willReturn(1);
        $storeManager->expects($this->any())->method('getStore')->willReturnSelf();
        $universalFactory->expects($this->exactly(1))->method('create')->willReturnOnConsecutiveCalls(
            $this->entityMock
        );
        $this->entityMock->expects($this->once())->method('getConnection')->willReturn($this->connectionMock);
        $this->entityMock->expects($this->once())->method('getDefaultAttributes')->willReturn([]);
        $this->entityMock->expects($this->any())->method('getTable')->willReturnArgument(0);
        $this->connectionMock->expects($this->atLeastOnce())->method('select')->willReturn($this->selectMock);

        $productLimitationMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitation::class
        );
        $productLimitationFactoryMock = $this->getMock(ProductLimitationFactory::class, ['create']);
        $productLimitationFactoryMock->method('create')
            ->willReturn($productLimitationMock);
        $this->collection = $this->objectManager->getObject(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class,
            [
                'entityFactory' => $entityFactory,
                'logger' => $logger,
                'fetchStrategy' => $fetchStrategy,
                'eventManager' => $eventManager,
                'eavConfig' => $eavConfig,
                'resource' => $resource,
                'eavEntityFactory' => $eavEntityFactory,
                'resourceHelper' => $resourceHelper,
                'universalFactory' => $universalFactory,
                'storeManager' => $storeManager,
                'moduleManager' => $moduleManager,
                'catalogProductFlatState' => $catalogProductFlatState,
                'scopeConfig' => $scopeConfig,
                'productOptionFactory' => $productOptionFactory,
                'catalogUrl' => $catalogUrl,
                'localeDate' => $localeDate,
                'customerSession' => $customerSession,
                'dateTime' => $dateTime,
                'groupManagement' => $groupManagement,
                'connection' => $this->connectionMock,
                'productLimitationFactory' => $productLimitationFactoryMock,
                'metadataPool' => $this->metadataPoolMock,
            ]
        );
        $this->collection->setConnection($this->connectionMock);
        $this->objectManager->setBackwardCompatibleProperty(
            $this->collection,
            'mediaGalleryResource',
            $this->galleryResourceMock
        );
        $this->objectManager->setBackwardCompatibleProperty(
            $this->collection,
            'productGalleryReadHandler',
            $this->galleryReadHandlerMock
        );
    }

    public function testAddProductCategoriesFilter()
    {
        $condition = ['in' => [1, 2]];
        $values = [1, 2];
        $conditionType = 'nin';
        $preparedSql = "category_id IN(1,2)";
        $tableName = "catalog_category_product";
        $this->connectionMock->expects($this->any())->method('getId')->willReturn(1);
        $this->connectionMock->expects($this->exactly(2))->method('prepareSqlCondition')->withConsecutive(
            ['cat.category_id', $condition],
            ['e.entity_id', [$conditionType => $this->selectMock]]
        )->willReturnOnConsecutiveCalls(
            $preparedSql,
            'e.entity_id IN (1,2)'
        );
        $this->selectMock->expects($this->once())->method('from')->with(
            ['cat' => $tableName],
            'cat.product_id'
        )->willReturnSelf();
        $this->selectMock->expects($this->exactly(2))->method('where')->withConsecutive(
            [$preparedSql],
            ['e.entity_id IN (1,2)']
        )->willReturnSelf();
        $this->collection->addCategoriesFilter([$conditionType => $values]);
    }

    public function testAddMediaGalleryData()
    {
        $attributeId = 42;
        $rowId = 4;
        $linkField = 'row_id';
        $mediaGalleriesMock = [[$linkField => $rowId]];
        $itemMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMock();
        $attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadataMock = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collection->addItem($itemMock);
        $reflection = new \ReflectionClass(get_class($this->collection));
        $reflectionProperty = $reflection->getProperty('_isCollectionLoaded');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->collection, true);

        $this->galleryResourceMock->expects($this->once())->method('createBatchBaseSelect')->willReturn($selectMock);
        $attributeMock->expects($this->once())->method('getAttributeId')->willReturn($attributeId);
        $this->entityMock->expects($this->once())->method('getAttribute')->willReturn($attributeMock);
        $itemMock->expects($this->atLeastOnce())->method('getData')->willReturn($rowId);
        $selectMock->expects($this->once())->method('where')->with('entity.' . $linkField . ' IN (?)', [$rowId]);
        $this->metadataPoolMock->expects($this->once())->method('getMetadata')->willReturn($metadataMock);
        $metadataMock->expects($this->once())->method('getLinkField')->willReturn($linkField);

        $this->connectionMock->expects($this->once())->method('fetchAll')->with($selectMock)->willReturn(
            [['row_id' => $rowId]]
        );
        $this->galleryReadHandlerMock->expects($this->once())->method('addMediaDataToProduct')
            ->with($itemMock, $mediaGalleriesMock);

        $this->assertSame($this->collection, $this->collection->addMediaGalleryData());
    }
}
