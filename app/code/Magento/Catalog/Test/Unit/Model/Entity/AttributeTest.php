<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Model\Entity;

use Magento\Catalog\Model\Entity\Attribute;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Entity\Attribute
     */
    protected $attribute;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \Magento\Framework\Api\MetadataServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataServiceMock;

    /**
     * @var \Magento\Framework\Api\AttributeValueFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeValueFactoryMock;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Eav\Model\Entity\TypeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeFactoryMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Eav\Model\Resource\Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \Magento\Framework\Validator\UniversalFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $universalFactoryMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $timezoneMock;

    /**
     * @var \Magento\Catalog\Model\Product\ReservedAttributeList|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $reservedAttributeListMock;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resolverMock;

    /**
     * @var \Magento\Catalog\Model\Attribute\LockValidatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $lockValidatorMock;

    /**
     * @var \Magento\Framework\Model\Resource\AbstractResource|\PHPUnit_Framework_MockObject_MockObject
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

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder('Magento\Framework\Model\Context')
            ->setMethods(['getCacheManager', 'getEventDispatcher'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock = $this->getMockBuilder('Magento\Framework\Registry')
            ->getMock();
        $this->metadataServiceMock = $this->getMockBuilder('Magento\Framework\Api\MetadataServiceInterface')
            ->getMock();
        $this->extensionAttributesFactory = $this->getMockBuilder('Magento\Framework\Api\ExtensionAttributesFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeValueFactoryMock = $this->getMockBuilder('Magento\Framework\Api\AttributeValueFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder('Magento\Eav\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->typeFactoryMock = $this->getMockBuilder('Magento\Eav\Model\Entity\TypeFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->getMock();
        $this->helperMock = $this->getMockBuilder('Magento\Eav\Model\Resource\Helper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->universalFactoryMock = $this->getMockBuilder('Magento\Framework\Validator\UniversalFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeOptionFactoryMock =
            $this->getMockBuilder('Magento\Eav\Api\Data\AttributeOptionInterfaceFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectProcessorMock = $this->getMockBuilder('Magento\Framework\Reflection\DataObjectProcessor')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectHelperMock = $this->getMockBuilder('Magento\Framework\Api\DataObjectHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->timezoneMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\TimezoneInterface')
            ->getMock();
        $this->reservedAttributeListMock = $this->getMockBuilder('Magento\Catalog\Model\Product\ReservedAttributeList')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resolverMock = $this->getMockBuilder('Magento\Framework\Locale\ResolverInterface')
            ->getMock();
        $this->lockValidatorMock = $this->getMockBuilder('Magento\Catalog\Model\Attribute\LockValidatorInterface')
            ->getMock();

        $this->resourceMock = $this->getMockBuilder('Magento\Framework\Model\Resource\AbstractResource')
            ->setMethods(['_construct', '_getReadAdapter', '_getWriteAdapter', 'getIdFieldName', 'saveInSetIncluding'])
            ->getMock();
        $this->cacheManager = $this->getMockBuilder('Magento\Framework\App\CacheInterface')
            ->getMock();
        $this->eventDispatcher = $this->getMockBuilder('Magento\Framework\Event\ManagerInterface')
            ->getMock();

        $this->contextMock
            ->expects($this->any())
            ->method('getCacheManager')
            ->willReturn($this->cacheManager);
        $this->contextMock
            ->expects($this->any())
            ->method('getEventDispatcher')
            ->willReturn($this->eventDispatcher);

        $this->attribute = new Attribute(
            $this->contextMock,
            $this->registryMock,
            $this->extensionAttributesFactory,
            $this->attributeValueFactoryMock,
            $this->configMock,
            $this->typeFactoryMock,
            $this->storeManagerMock,
            $this->helperMock,
            $this->universalFactoryMock,
            $this->attributeOptionFactoryMock,
            $this->dataObjectProcessorMock,
            $this->dataObjectHelperMock,
            $this->timezoneMock,
            $this->reservedAttributeListMock,
            $this->resolverMock,
            $this->lockValidatorMock,
            $this->resourceMock
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
