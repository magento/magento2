<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Entity;

use Magento\Catalog\Model\Attribute\LockValidatorInterface;
use Magento\Catalog\Model\Entity\Attribute;
use Magento\Catalog\Model\Product\ReservedAttributeList;
use Magento\Eav\Api\Data\AttributeOptionInterfaceFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\TypeFactory;
use Magento\Eav\Model\ResourceModel\Helper;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\MetadataServiceInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
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
    use MockCreationTrait;
    /**
     * @var Attribute
     */
    private $attribute;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Registry|MockObject
     */
    private $registryMock;

    /**
     * @var MetadataServiceInterface|MockObject
     */
    private $metadataServiceMock;

    /**
     * @var AttributeValueFactory|MockObject
     */
    private $attributeValueFactoryMock;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var TypeFactory|MockObject
     */
    private $typeFactoryMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var Helper|MockObject
     */
    private $helperMock;

    /**
     * @var UniversalFactory|MockObject
     */
    private $universalFactoryMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    private $timezoneMock;

    /**
     * @var ReservedAttributeList|MockObject
     */
    private $reservedAttributeListMock;

    /**
     * @var ResolverInterface|MockObject
     */
    private $resolverMock;

    /**
     * @var LockValidatorInterface|MockObject
     */
    private $lockValidatorMock;

    /**
     * @var AbstractResource|MockObject
     */
    private $resourceMock;

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
     * @var ExtensionAttributesFactory|MockObject
     */
    private $extensionAttributesFactory;

    /**
     * @var DateTimeFormatterInterface|MockObject
     */
    private $dateTimeFormatter;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return void
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createPartialMock(Context::class, ['getCacheManager', 'getEventDispatcher']);
        $this->registryMock = $this->createMock(Registry::class);
        $this->metadataServiceMock = $this->createMock(MetadataServiceInterface::class);
        $this->extensionAttributesFactory = $this->createMock(ExtensionAttributesFactory::class);
        $this->attributeValueFactoryMock = $this->createMock(AttributeValueFactory::class);
        $this->configMock = $this->createMock(Config::class);
        $this->typeFactoryMock = $this->createPartialMock(TypeFactory::class, ['create']);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->helperMock = $this->createMock(Helper::class);
        $this->universalFactoryMock = $this->createMock(UniversalFactory::class);
        $this->attributeOptionFactoryMock = $this->createPartialMock(
            AttributeOptionInterfaceFactory::class,
            ['create']
        );
        $this->dataObjectProcessorMock = $this->createMock(DataObjectProcessor::class);
        $this->dataObjectHelperMock = $this->createMock(DataObjectHelper::class);
        $this->timezoneMock = $this->createMock(TimezoneInterface::class);
        $this->reservedAttributeListMock = $this->createMock(ReservedAttributeList::class);
        $this->resolverMock = $this->createMock(ResolverInterface::class);
        $this->lockValidatorMock = $this->createMock(LockValidatorInterface::class);
        $this->dateTimeFormatter = $this->createMock(
            DateTimeFormatterInterface::class
        );

        $this->resourceMock = $this->createPartialMockWithReflection(
            AbstractResource::class,
            ['_construct', 'getConnection', 'getIdFieldName', 'saveInSetIncluding']
        );
        $this->cacheManager = $this->createMock(CacheInterface::class);
        $this->eventDispatcher = $this->createMock(ManagerInterface::class);
        $this->contextMock
            ->method('getCacheManager')->willReturn($this->cacheManager);
        $this->contextMock
            ->method('getEventDispatcher')->willReturn($this->eventDispatcher);
        $objectManagerHelper = new ObjectManagerHelper($this);
        $objectManagerHelper->prepareObjectManager();
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
                'resource' => $this->resourceMock
            ]
        );
    }

    public function testAfterSaveEavCache()
    {
        $this->configMock
            ->expects($this->once())
            ->method('clear');

        $this->attribute->afterSave();
    }
}
