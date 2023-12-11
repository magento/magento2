<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model;

use Magento\Catalog\Model\Product\ReservedAttributeList;
use Magento\Customer\Model\Attribute;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Metadata\AttributeMetadataCache;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\Data\AttributeOptionInterfaceFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\TypeFactory;
use Magento\Eav\Model\ResourceModel\Helper;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeTest extends TestCase
{
    /**
     * @var Attribute
     */
    protected $attribute;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var Registry|MockObject
     */
    protected $registryMock;

    /**
     * @var AttributeValueFactory|MockObject
     */
    protected $attributeValueFactoryMock;

    /**
     * @var Config|MockObject
     */
    protected $configMock;

    /**
     * @var TypeFactory|MockObject
     */
    protected $typeFactoryMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var Helper|MockObject
     */
    protected $helperMock;

    /**
     * @var UniversalFactory|MockObject
     */
    protected $universalFactoryMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    protected $timezoneMock;

    /**
     * @var AbstractResource|MockObject
     */
    private $resourceMock;

    /**
     * @var ReservedAttributeList|MockObject
     */
    protected $reservedAttributeListMock;

    /**
     * @var ResolverInterface|MockObject
     */
    protected $resolverMock;

    /**
     * @var CacheInterface|MockObject
     */
    private $cacheManager;

    /**
     * @var ManagerInterface|MockObject
     */
    private $eventDispatcher;

    /**
     * @var AttributeOptionInterfaceFactory|MockObject
     */
    private $attributeOptionFactoryMock;

    /**
     * @var DataObjectProcessor|MockObject
     */
    private $dataObjectProcessorMock;

    /**
     * @var DataObjectHelper|MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var IndexerRegistry|MockObject
     */
    private $indexerRegistryMock;

    /**
     * @var ExtensionAttributesFactory|MockObject
     */
    private $extensionAttributesFactory;

    /**
     * @var DateTimeFormatterInterface|MockObject
     */
    private $dateTimeFormatter;

    /**
     * @var AttributeMetadataCache|MockObject
     */
    private $attributeMetadataCacheMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return void
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->getMock();
        $this->extensionAttributesFactory = $this->getMockBuilder(
            ExtensionAttributesFactory::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeValueFactoryMock = $this->getMockBuilder(AttributeValueFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->typeFactoryMock = $this->getMockBuilder(TypeFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMock();
        $this->helperMock = $this->getMockBuilder(Helper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->universalFactoryMock = $this->getMockBuilder(UniversalFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeOptionFactoryMock =
            $this->getMockBuilder(AttributeOptionInterfaceFactory::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->dataObjectProcessorMock = $this->getMockBuilder(DataObjectProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->timezoneMock = $this->getMockBuilder(TimezoneInterface::class)
            ->getMock();
        $this->reservedAttributeListMock = $this->getMockBuilder(
            ReservedAttributeList::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->resolverMock = $this->getMockBuilder(ResolverInterface::class)
            ->getMock();
        $this->dateTimeFormatter = $this->createMock(
            DateTimeFormatterInterface::class
        );

        $this->resourceMock = $this->getMockBuilder(AbstractResource::class)
            ->setMethods(['_construct', 'getConnection', 'getIdFieldName', 'saveInSetIncluding'])
            ->getMockForAbstractClass();
        $this->cacheManager = $this->getMockBuilder(CacheInterface::class)
            ->getMock();
        $this->eventDispatcher = $this->getMockBuilder(ManagerInterface::class)
            ->getMock();

        $this->contextMock
            ->expects($this->any())
            ->method('getCacheManager')
            ->willReturn($this->cacheManager);
        $this->contextMock
            ->expects($this->any())
            ->method('getEventDispatcher')
            ->willReturn($this->eventDispatcher);

        $this->indexerRegistryMock = $this->getMockBuilder(IndexerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeMetadataCacheMock = $this->getMockBuilder(AttributeMetadataCache::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attribute = $objectManagerHelper->getObject(
            Attribute::class,
            [
                'context' => $this->contextMock,
                'registry' => $this->registryMock,
                'extensionFactory' => $this->extensionAttributesFactory,
                'attributeValueFactory' => $this->attributeValueFactoryMock,
                'eavConfig' => $this->configMock,
                'typeFactory' => $this->typeFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'helper' => $this->helperMock,
                'universalFactory' => $this->universalFactoryMock,
                'attributeOptionFactory' => $this->attributeOptionFactoryMock,
                'dataObjectProcessor' => $this->dataObjectProcessorMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'timezone' => $this->timezoneMock,
                'reservedAttributeList' => $this->reservedAttributeListMock,
                'resolver' => $this->resolverMock,
                'dateTimeFormatter' => $this->dateTimeFormatter,
                'indexerRegistry' => $this->indexerRegistryMock,
                'resource' => $this->resourceMock,
                'attributeMetadataCache' => $this->attributeMetadataCacheMock
            ]
        );
    }

    public function testAfterSaveEavCache()
    {
        $this->configMock
            ->expects($this->once())
            ->method('clear');
        $this->attributeMetadataCacheMock
            ->expects($this->once())
            ->method('clean');
        $this->attribute->afterSave();
    }

    public function testAfterDeleteEavCache()
    {
        $this->configMock
            ->expects($this->once())
            ->method('clear');
        $this->attributeMetadataCacheMock
            ->expects($this->once())
            ->method('clean');
        $this->attribute->afterDelete();
    }

    public function testInvalidate()
    {
        /** @var IndexerInterface|MockObject $indexerMock */
        $indexerMock = $this->getMockBuilder(IndexerInterface::class)
            ->getMockForAbstractClass();

        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(Customer::CUSTOMER_GRID_INDEXER_ID)
            ->willReturn($indexerMock);

        $indexerMock->expects($this->once())
            ->method('invalidate');

        $this->attribute->invalidate();
    }

    /**
     * @param int $isSearchableInGrid
     * @param string $frontendInput
     * @param bool $result
     * @dataProvider dataProviderCanBeSearchableInGrid
     */
    public function testCanBeSearchableInGrid($isSearchableInGrid, $frontendInput, $result)
    {
        $this->attribute->setData('is_searchable_in_grid', $isSearchableInGrid);
        $this->attribute->setData(AttributeInterface::FRONTEND_INPUT, $frontendInput);

        $this->assertEquals($result, $this->attribute->canBeSearchableInGrid());
    }

    /**
     * @return array
     */
    public function dataProviderCanBeSearchableInGrid()
    {
        return [
            [0, 'text', false],
            [0, 'textarea', false],
            [1, 'text', true],
            [1, 'textarea', true],
            [1, 'date', false],
            [1, 'boolean', false],
            [1, 'select', false],
            [1, 'media_image', false],
            [1, 'gallery', false],
            [1, 'multiselect', false],
            [1, 'image', false],
            [1, 'price', false],
            [1, 'weight', false],
        ];
    }

    /**
     * @param int $isFilterableInGrid
     * @param string $frontendInput
     * @param bool $result
     * @dataProvider dataProviderCanBeFilterableInGrid
     */
    public function testCanBeFilterableInGrid($isFilterableInGrid, $frontendInput, $result)
    {
        $this->attribute->setData('is_filterable_in_grid', $isFilterableInGrid);
        $this->attribute->setData(AttributeInterface::FRONTEND_INPUT, $frontendInput);

        $this->assertEquals($result, $this->attribute->canBeFilterableInGrid());
    }

    /**
     * @return array
     */
    public function dataProviderCanBeFilterableInGrid()
    {
        return [
            [0, 'text', false],
            [0, 'date', false],
            [0, 'select', false],
            [0, 'boolean', false],
            [1, 'text', true],
            [1, 'date', true],
            [1, 'select', true],
            [1, 'boolean', true],
            [1, 'textarea', false],
            [1, 'media_image', false],
            [1, 'gallery', false],
            [1, 'multiselect', false],
            [1, 'image', false],
            [1, 'price', false],
            [1, 'weight', false],
        ];
    }
}
