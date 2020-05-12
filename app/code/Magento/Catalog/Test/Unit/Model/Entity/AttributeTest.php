<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->setMethods(['getCacheManager', 'getEventDispatcher'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->getMock();
        $this->metadataServiceMock = $this->getMockBuilder(MetadataServiceInterface::class)
            ->getMock();
        $this->extensionAttributesFactory = $this->getMockBuilder(
            ExtensionAttributesFactory::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->attributeValueFactoryMock = $this->getMockBuilder(AttributeValueFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->typeFactoryMock = $this->getMockBuilder(TypeFactory::class)
            ->setMethods(['create'])
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
                ->setMethods(['create'])
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
        )->disableOriginalConstructor()
            ->getMock();
        $this->resolverMock = $this->getMockBuilder(ResolverInterface::class)
            ->getMock();
        $this->lockValidatorMock = $this->getMockBuilder(LockValidatorInterface::class)
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
        $objectManagerHelper = new ObjectManagerHelper($this);
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
