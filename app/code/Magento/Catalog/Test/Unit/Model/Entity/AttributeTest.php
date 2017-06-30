<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Entity;

use Magento\Catalog\Model\Entity\Attribute;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Entity\Attribute
     */
    private $attribute;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registryMock;

    /**
     * @var \Magento\Framework\Api\MetadataServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataServiceMock;

    /**
     * @var \Magento\Framework\Api\AttributeValueFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeValueFactoryMock;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var \Magento\Eav\Model\Entity\TypeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $typeFactoryMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $helperMock;

    /**
     * @var \Magento\Framework\Validator\UniversalFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $universalFactoryMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $timezoneMock;

    /**
     * @var \Magento\Catalog\Model\Product\ReservedAttributeList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reservedAttributeListMock;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resolverMock;

    /**
     * @var \Magento\Catalog\Model\Attribute\LockValidatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $lockValidatorMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\AbstractResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var \Magento\Framework\App\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventDispatcher;

    /**
     * @var \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeOptionFactoryMock;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectProcessorMock;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var \Magento\Framework\Api\ExtensionAttributesFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributesFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeFormatter;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return void
     */
    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\Model\Context::class)
            ->setMethods(['getCacheManager', 'getEventDispatcher'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->getMock();
        $this->metadataServiceMock = $this->getMockBuilder(\Magento\Framework\Api\MetadataServiceInterface::class)
            ->getMock();
        $this->extensionAttributesFactory = $this->getMockBuilder(
            \Magento\Framework\Api\ExtensionAttributesFactory::class
        )->disableOriginalConstructor()->getMock();
        $this->attributeValueFactoryMock = $this->getMockBuilder(\Magento\Framework\Api\AttributeValueFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder(\Magento\Eav\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->typeFactoryMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\TypeFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMock();
        $this->helperMock = $this->getMockBuilder(\Magento\Eav\Model\ResourceModel\Helper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->universalFactoryMock = $this->getMockBuilder(\Magento\Framework\Validator\UniversalFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeOptionFactoryMock =
            $this->getMockBuilder(\Magento\Eav\Api\Data\AttributeOptionInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectProcessorMock = $this->getMockBuilder(\Magento\Framework\Reflection\DataObjectProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectHelperMock = $this->getMockBuilder(\Magento\Framework\Api\DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->timezoneMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)
            ->getMock();
        $this->reservedAttributeListMock = $this->getMockBuilder(
            \Magento\Catalog\Model\Product\ReservedAttributeList::class
        )->disableOriginalConstructor()->getMock();
        $this->resolverMock = $this->getMockBuilder(\Magento\Framework\Locale\ResolverInterface::class)
            ->getMock();
        $this->lockValidatorMock = $this->getMockBuilder(\Magento\Catalog\Model\Attribute\LockValidatorInterface::class)
            ->getMock();
        $this->dateTimeFormatter = $this->getMock(\Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface::class);

        $this->resourceMock = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\AbstractResource::class)
            ->setMethods(['_construct', 'getConnection', 'getIdFieldName', 'saveInSetIncluding'])
            ->getMockForAbstractClass();
        $this->cacheManager = $this->getMockBuilder(\Magento\Framework\App\CacheInterface::class)
            ->getMock();
        $this->eventDispatcher = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
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
