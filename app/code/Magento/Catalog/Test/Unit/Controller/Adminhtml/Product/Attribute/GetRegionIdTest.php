<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\Attribute;

use Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\AttributeTest;
use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Customer\Model\Address\CompositeValidator;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Directory\Helper\Data;
use Magento\Eav\Model\Config;
use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Directory\Model\Region;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class GetRegionIdTest extends AttributeTest
{

    /**
     * @var Context|MockObject
     */
    private Context $context;

    /**
     * @var Registry|MockObject
     */
    private Registry $registry;

    /**
     * @var ExtensionAttributesFactory|MockObject
     */
    private ExtensionAttributesFactory $extensionFactory;

    /**
     * @var AttributeValueFactory|MockObject
     */
    private AttributeValueFactory $customAttributeFactory;

    /**
     * @var Data|MockObject
     */
    private Data $directoryData;

    /**
     * @var Config|MockObject
     */
    private Config $eavConfig;

    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    protected $_addressConfig;

    /**
     * @var CountryFactory|MockObject
     */
    private CountryFactory $countryFactory;

    /**
     * @var AddressMetadataInterface
     */
    protected $metadataService;

    /**
     * @var RegionInterfaceFactory
     */
    protected $regionDataFactory;

    /**
     * @var DataObjectHelper|MockObject
     */
    private DataObjectHelper $dataObjectHelper;

    /**
     * @var AbstractResource|MockObject
     */
    private AbstractResource $resource;

    /**
     * @var AbstractDb|MockObject
     */
    private AbstractDb $resourceCollection;

    /** @var CompositeValidator */
    private $compositeValidator;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var AbstractExtensibleModel|MockObject
     */
    protected $abstractExtensibleModel;

    /**
     * @var Region|MockObject
     */
    protected $region;

    /**
     * @var AbstractAddress|MockObject
     */
    protected $abstractAddress;

    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->registry = $this->createMock(Registry::class);
        $this->extensionFactory = $this->createMock(ExtensionAttributesFactory::class);
        $this->customAttributeFactory = $this->createMock(AttributeValueFactory::class);
        $this->directoryData = $this->createMock(Data::class);
        $this->eavConfig = $this->createMock(Config::class);
        $this->_addressConfig = $this->createMock(AddressConfig::class);
        $this->countryFactory = $this->createMock(CountryFactory::class);
        $this->metadataService = $this->createMock(AddressMetadataInterface::class);
        $this->addressDataFactory = $this->createMock(AddressInterfaceFactory::class);
        $this->regionDataFactory = $this->createMock(RegionInterfaceFactory::class);
        $this->dataObjectHelper = $this->createMock(DataObjectHelper::class);
        $this->resource = $this->createMock(AbstractResource::class);
        $this->resourceCollection = $this->createMock(AbstractDb::class);
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();
        $compositeValidator = $this->createMock(CompositeValidator::class);
        $this->compositeValidator = $compositeValidator ?: ObjectManager::getInstance();
        $this->abstractExtensibleModel = $this->createMock(AbstractExtensibleModel::class);
        $this->region = $this->createMock(Region::class);
    }

    public function testExecute()
    {
        //loadByCode
        $regionFactory = $this->getMockBuilder(RegionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $region = $this->getMockBuilder(Region::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRegionIdByCode'])
            ->getMock();
        $regionFactory->expects($this->once())
            ->method('create')
            ->willReturn($region);
        $abstractAddress = new AbstractAddress(
            $this->context,
            $this->registry,
            $this->extensionFactory,
            $this->customAttributeFactory,
            $this->directoryData,
            $this->eavConfig,
            $this->_addressConfig,
            $regionFactory, // Use the mocked RegionFactory
            $this->countryFactory,
            $this->metadataService,
            $this->addressDataFactory,
            $this->regionDataFactory,
            $this->dataObjectHelper,
            $this->resource,
            $this->resourceCollection,
            [],
            $this->compositeValidator
        );
        $regionId = $abstractAddress->getRegionId();
        // Assert the result
        $this->assertEquals(null, $regionId);
    }
}
