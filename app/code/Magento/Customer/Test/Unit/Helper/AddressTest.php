<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Helper;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Block\Address\Renderer\RendererInterface;
use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Address\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\BlockFactory;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressTest extends TestCase
{
    /** @var Address|MockObject */
    protected $helper;

    /** @var Context */
    protected $context;

    /** @var BlockFactory|MockObject */
    protected $blockFactory;

    /** @var StoreManagerInterface|MockObject */
    protected $storeManager;

    /** @var ScopeConfigInterface|MockObject */
    protected $scopeConfig;

    /** @var CustomerMetadataInterface|MockObject */
    protected $customerMetadataService;

    /** @var Config|MockObject */
    protected $addressConfig;

    /** @var MockObject|AddressMetadataInterface */
    private $addressMetadataService;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $className = Address::class;
        $arguments = $objectManagerHelper->getConstructArguments($className);
        /** @var Context $context */
        $this->context = $arguments['context'];
        $this->blockFactory = $arguments['blockFactory'];
        $this->storeManager = $arguments['storeManager'];
        $this->scopeConfig = $this->context->getScopeConfig();
        $this->customerMetadataService = $arguments['customerMetadataService'];
        $this->addressConfig = $arguments['addressConfig'];
        $this->addressMetadataService = $arguments['addressMetadataService'];

        $this->helper = $objectManagerHelper->getObject($className, $arguments);
    }

    /**
     * @param int $numLines
     * @param int $expectedNumLines
     * @dataProvider providerGetStreetLines
     */
    public function testGetStreetLines($numLines, $expectedNumLines)
    {
        $attributeMock = $this->getMockBuilder(
            AttributeMetadataInterface::class
        )->getMock();
        $attributeMock->expects($this->any())->method('getMultilineCount')->willReturn($numLines);

        $this->addressMetadataService
            ->expects($this->any())
            ->method('getAttributeMetadata')
            ->willReturn($attributeMock);

        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager->expects($this->any())->method('getStore')->willReturn($store);

        $this->assertEquals($expectedNumLines, $this->helper->getStreetLines());
    }

    /**
     * @return array
     */
    public static function providerGetStreetLines()
    {
        return [
            [-1, 2],
            [0, 2],
            [1, 1],
            [2, 2],
            [3, 3],
            [4, 4],
            [5, 5],
            [10, 10],
            [15, 15],
            [20, 20],
            [21, 20],
        ];
    }

    /**
     * @dataProvider getRendererDataProvider
     */
    public function testGetRenderer($renderer, $blockFactory, $result)
    {
        if ($renderer!="some_test_block") {
            $renderer = $renderer($this);
        }
        $blockFactory = $blockFactory($this);
        $result = $result($this);
        $this->helper = new Address(
            $this->context,
            $blockFactory,
            $this->storeManager,
            $this->customerMetadataService,
            $this->addressMetadataService,
            $this->addressConfig
        );
        $this->assertEquals($result, $this->helper->getRenderer($renderer));
    }

    protected function getMockForBlockInterface()
    {
        $blockMock = $this->getMockBuilder(BlockInterface::class)
            ->getMock();
        return $blockMock;
    }

    protected function getMockForBlockFactory()
    {
        $blockMock = $this->getMockBuilder(BlockInterface::class)
            ->getMock();
        $blockFactory = $this->getMockBuilder(
            BlockFactory::class
        )->disableOriginalConstructor()
            ->getMock();
        $blockFactory->expects($this->any())
            ->method('createBlock')
            ->with('some_test_block', [])
            ->willReturn($blockMock);
        return $blockFactory;
    }

    /**
     * @return array
     */
    public static function getRendererDataProvider()
    {
        $blockMock = static fn (self $testCase) => $testCase->getMockForBlockInterface();
        $blockFactory = static fn (self $testCase) => $testCase->getMockForBlockFactory();
        return [
            ['some_test_block', $blockFactory, $blockMock],
            [$blockMock, $blockFactory, $blockMock],
        ];
    }

    public function testGetConfigCanShowConfig()
    {
        $result = ['key1' => 'value1', 'key2' => 'value2'];
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn('1');
        $this->scopeConfig->expects($this->once())//test method cache
            ->method('getValue')
            ->with('customer/address', ScopeInterface::SCOPE_STORE, $store)
            ->willReturn($result);
        $this->storeManager->expects($this->any())->method('getStore')->willReturn($store);
        $this->assertNull($this->helper->getConfig('unavailable_key'));
        $this->assertFalse($this->helper->canShowConfig('unavailable_key'));
        $this->assertEquals($result['key1'], $this->helper->getConfig('key1'));
        $this->assertEquals($result['key2'], $this->helper->getConfig('key2'));
        $this->assertTrue($this->helper->canShowConfig('key1'));
        $this->assertTrue($this->helper->canShowConfig('key2'));
    }

    public function testGetAttributeValidationClass()
    {
        $attributeCode = 'attr_code';
        $attributeClass = 'Attribute_Class';

        $attributeMock = $this->getMockBuilder(AttributeMetadataInterface::class)
            ->getMockForAbstractClass();
        $attributeMock->expects($this->once())
            ->method('getFrontendClass')
            ->willReturn($attributeClass);

        $this->addressMetadataService->expects($this->any())
            ->method('getAttributeMetadata')
            ->willReturn($attributeMock);

        $this->assertEquals($attributeClass, $this->helper->getAttributeValidationClass($attributeCode));
    }

    public function testGetAttributeValidationClassWithNoAttribute()
    {
        $attrCode = 'attr_code';

        $this->addressMetadataService->expects($this->any())
            ->method('getAttributeMetadata')
            ->willReturn(null);

        $this->assertEquals('', $this->helper->getAttributeValidationClass($attrCode));
    }

    /**
     * @param $origStreets
     * @param $toCount
     * @param $result
     * @dataProvider getConvertStreetLinesDataProvider
     */
    public function testConvertStreetLines($origStreets, $toCount, $result)
    {
        $this->assertEquals($result, $this->helper->convertStreetLines($origStreets, $toCount));
    }

    /**
     * @return array
     */
    public static function getConvertStreetLinesDataProvider()
    {
        return [
            [['street1', 'street2', 'street3', 'street4'], 3, ['street1 street2', 'street3', 'street4']],
            [['street1', 'street2', 'street3', 'street4'], 2, ['street1 street2', 'street3 street4']],
        ];
    }

    /**
     * @param $store
     * @param $result
     * @dataProvider getVatValidationEnabledDataProvider
     */
    public function testIsVatValidationEnabled($store, $result)
    {
        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with(
                Address::XML_PATH_VAT_VALIDATION_ENABLED,
                ScopeInterface::SCOPE_STORE,
                $store
            )
            ->willReturn($result);
        $this->assertEquals($result, $this->helper->isVatValidationEnabled($store));
    }

    /**
     * @return array
     */
    public static function getVatValidationEnabledDataProvider()
    {
        return [
            [0, true],
            [1, false],
            [2, true],
        ];
    }

    /**
     * @param $store
     * @param $result
     * @dataProvider getValidateOnEachTransactionDataProvider
     */
    public function testHasValidateOnEachTransaction($store, $result)
    {
        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with(
                Address::XML_PATH_VIV_ON_EACH_TRANSACTION,
                ScopeInterface::SCOPE_STORE,
                $store
            )
            ->willReturn($result);
        $this->assertEquals($result, $this->helper->hasValidateOnEachTransaction($store));
    }

    /**
     * @return array
     */
    public static function getValidateOnEachTransactionDataProvider()
    {
        return [
            [0, true],
            [1, false],
            [2, true],
        ];
    }

    /**
     * @param $store
     * @param $result
     * @dataProvider getTaxCalculationAddressTypeDataProvider
     */
    public function testGetTaxCalculationAddressType($store, $result)
    {
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with(
                Address::XML_PATH_VIV_TAX_CALCULATION_ADDRESS_TYPE,
                ScopeInterface::SCOPE_STORE,
                $store
            )
            ->willReturn($result);
        $this->assertEquals($result, $this->helper->getTaxCalculationAddressType($store));
    }

    /**
     * @return array
     */
    public static function getTaxCalculationAddressTypeDataProvider()
    {
        return [
            [0, 'address_type_store_0'],
            [1, 'address_type_store_1'],
            [2, 'address_type_store_2'],
        ];
    }

    public function testIsDisableAutoGroupAssignDefaultValue()
    {
        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with(
                Address::XML_PATH_VIV_DISABLE_AUTO_ASSIGN_DEFAULT,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn(true);
        $this->assertTrue($this->helper->isDisableAutoGroupAssignDefaultValue());
    }

    public function testIsVatAttributeVisible()
    {
        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with(
                Address::XML_PATH_VAT_FRONTEND_VISIBILITY,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn(true);
        $this->assertTrue($this->helper->isVatAttributeVisible());
    }

    /**
     * @param string $code
     * @param RendererInterface|null $result
     * @dataProvider getFormatTypeRendererDataProvider
     */
    public function testGetFormatTypeRenderer($code, $result)
    {
        if(is_callable($result))
        {
            $result = $result($this);
        }
        $this->addressConfig->expects($this->once())
            ->method('getFormatByCode')
            ->with($code)
            ->willReturn(
                new DataObject($result !== null ? ['renderer' => $result] : [])
            );
        $this->assertEquals($result, $this->helper->getFormatTypeRenderer($code));
    }

    protected function getMockForRendererClass()
    {
        $renderer = $this->getMockBuilder(RendererInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        return $renderer;
    }

    /**
     * @return array
     */
    public static function getFormatTypeRendererDataProvider()
    {
        $renderer = static fn (self $testCase) => $testCase->getMockForRendererClass();
        return [
            ['valid_code', $renderer],
            ['invalid_code', null]
        ];
    }

    /**
     * @param string $code
     * @param array $result
     * @dataProvider getFormatDataProvider
     */
    public function testGetFormat($code, $result)
    {
        if ($result) {
            $renderer = $this->getMockBuilder(RendererInterface::class)
                ->disableOriginalConstructor()
                ->getMockForAbstractClass();
            $renderer->expects($this->once())
                ->method('getFormatArray')
                ->willReturn(['key' => 'value']);
        }
        $this->addressConfig->expects($this->once())
            ->method('getFormatByCode')
            ->with($code)
            ->willReturn(
                new DataObject(!empty($result) ? ['renderer' => $renderer] : [])
            );

        $this->assertEquals($result, $this->helper->getFormat($code));
    }

    /**
     * @return array
     */
    public static function getFormatDataProvider()
    {
        return [
            ['valid_code', ['key' => 'value']],
            ['invalid_code', '']
        ];
    }

    /**
     * @param string $attributeCode
     * @param bool $isMetadataExists
     * @dataProvider isAttributeVisibleDataProvider
     */
    public function testIsAttributeVisible($attributeCode, $isMetadataExists)
    {
        $attributeMetadata = null;
        if ($isMetadataExists) {
            $attributeMetadata = $this->getMockBuilder(AttributeMetadataInterface::class)
                ->getMockForAbstractClass();
            $attributeMetadata->expects($this->once())
                ->method('isVisible')
                ->willReturn(true);
        }
        $this->addressMetadataService->expects($this->once())
            ->method('getAttributeMetadata')
            ->with($attributeCode)
            ->willReturn($attributeMetadata);
        $this->assertEquals($isMetadataExists, $this->helper->isAttributeVisible($attributeCode));
    }

    /**
     * @return array
     */
    public static function isAttributeVisibleDataProvider()
    {
        return [
            ['fax', true],
            ['invalid_code', false]
        ];
    }

    /**
     * Data provider for test  testIsAttributeRequire
     *
     * @return array
     */
    public function isAttributeRequiredDataProvider()
    {
        return [
            ['fax', true],
            ['invalid_code', false]
        ];
    }
}
