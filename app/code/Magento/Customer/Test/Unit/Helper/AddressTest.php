<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Helper;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\AddressMetadataManagementInterface;
use Magento\Customer\Api\CustomerMetadataInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Customer\Helper\Address|\PHPUnit_Framework_MockObject_MockObject */
    protected $helper;

    /** @var \Magento\Framework\App\Helper\Context */
    protected $context;

    /** @var \Magento\Framework\View\Element\BlockFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $blockFactory;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManager;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfig;

    /** @var CustomerMetadataInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerMetadataService;

    /** @var \Magento\Customer\Model\Address\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $addressConfig;

    /** @var  \Magento\Customer\Model\Metadata\AttributeResolver|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeResolver;

    /** @var \PHPUnit_Framework_MockObject_MockObject|AddressMetadataInterface */
    private $addressMetadataService;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = \Magento\Customer\Helper\Address::class;
        $arguments = $objectManagerHelper->getConstructArguments($className);
        /** @var \Magento\Framework\App\Helper\Context $context */
        $this->context = $arguments['context'];
        $this->blockFactory = $arguments['blockFactory'];
        $this->storeManager = $arguments['storeManager'];
        $this->scopeConfig = $this->context->getScopeConfig();
        $this->customerMetadataService = $arguments['customerMetadataService'];
        $this->addressConfig = $arguments['addressConfig'];
        $this->addressMetadataService = $arguments['addressMetadataService'];
        $this->attributeResolver = $arguments['attributeResolver'];

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
            \Magento\Customer\Api\Data\AttributeMetadataInterface::class
        )->getMock();
        $attributeMock->expects($this->any())->method('getMultilineCount')->will($this->returnValue($numLines));

        $this->addressMetadataService
            ->expects($this->any())
            ->method('getAttributeMetadata')
            ->will($this->returnValue($attributeMock));

        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)->disableOriginalConstructor()->getMock();
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $this->assertEquals($expectedNumLines, $this->helper->getStreetLines());
    }

    public function providerGetStreetLines()
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
        $this->helper = new \Magento\Customer\Helper\Address(
            $this->context,
            $blockFactory,
            $this->storeManager,
            $this->customerMetadataService,
            $this->addressMetadataService,
            $this->addressConfig
        );
        $this->assertEquals($result, $this->helper->getRenderer($renderer));
    }

    /**
     * @return array
     */
    public function getRendererDataProvider()
    {
        $blockMock = $this->getMockBuilder(\Magento\Framework\View\Element\BlockInterface::class)->getMock();
        $blockFactory = $this->getMockBuilder(
            \Magento\Framework\View\Element\BlockFactory::class
        )->disableOriginalConstructor()->getMock();
        $blockFactory->expects($this->once())
            ->method('createBlock')
            ->with('some_test_block', [])
            ->will($this->returnValue($blockMock));
        return [
            ['some_test_block', $blockFactory, $blockMock],
            [$blockMock, $blockFactory, $blockMock],
        ];
    }

    public function testGetConfigCanShowConfig()
    {
        $result = ['key1' => 'value1', 'key2' => 'value2'];
        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)->disableOriginalConstructor()->getMock();
        $store->expects($this->any())
            ->method('getWebsiteId')
            ->will($this->returnValue('1'));
        $this->scopeConfig->expects($this->once())//test method cache
            ->method('getValue')
            ->with('customer/address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store)
            ->will($this->returnValue($result));
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));
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

        $attributeMock = $this->getMockBuilder(\Magento\Customer\Api\Data\AttributeMetadataInterface::class)
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

    public function getConvertStreetLinesDataProvider()
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
            ->method('getValue')
            ->with(
                \Magento\Customer\Helper\Address::XML_PATH_VAT_VALIDATION_ENABLED,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            )
            ->will($this->returnValue($result));
        $this->assertEquals($result, $this->helper->isVatValidationEnabled($store));
    }

    /**
     * @return array
     */
    public function getVatValidationEnabledDataProvider()
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
            ->method('getValue')
            ->with(
                \Magento\Customer\Helper\Address::XML_PATH_VIV_ON_EACH_TRANSACTION,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            )
            ->will($this->returnValue($result));
        $this->assertEquals($result, $this->helper->hasValidateOnEachTransaction($store));
    }

    /**
     * @return array
     */
    public function getValidateOnEachTransactionDataProvider()
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
                \Magento\Customer\Helper\Address::XML_PATH_VIV_TAX_CALCULATION_ADDRESS_TYPE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            )
            ->will($this->returnValue($result));
        $this->assertEquals($result, $this->helper->getTaxCalculationAddressType($store));
    }

    /**
     * @return array
     */
    public function getTaxCalculationAddressTypeDataProvider()
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
            ->method('getValue')
            ->with(
                \Magento\Customer\Helper\Address::XML_PATH_VIV_DISABLE_AUTO_ASSIGN_DEFAULT,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ->will($this->returnValue(true));
        $this->assertTrue($this->helper->isDisableAutoGroupAssignDefaultValue());
    }

    public function testIsVatAttributeVisible()
    {
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with(
                \Magento\Customer\Helper\Address::XML_PATH_VAT_FRONTEND_VISIBILITY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ->will($this->returnValue(true));
        $this->assertTrue($this->helper->isVatAttributeVisible());
    }

    /**
     * @param string $code
     * @param \Magento\Customer\Block\Address\Renderer\RendererInterface|null $result
     * @dataProvider getFormatTypeRendererDataProvider
     */
    public function testGetFormatTypeRenderer($code, $result)
    {
        $this->addressConfig->expects($this->once())
            ->method('getFormatByCode')
            ->with($code)
            ->will(
                $this->returnValue(
                    new \Magento\Framework\DataObject($result !== null ? ['renderer' => $result] : [])
                )
            );
        $this->assertEquals($result, $this->helper->getFormatTypeRenderer($code));
    }

    public function getFormatTypeRendererDataProvider()
    {
        $renderer = $this->getMockBuilder(\Magento\Customer\Block\Address\Renderer\RendererInterface::class)
            ->disableOriginalConstructor()->getMock();
        return [
            ['valid_code', $renderer],
            ['invalid_code', null],
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
            $renderer = $this->getMockBuilder(\Magento\Customer\Block\Address\Renderer\RendererInterface::class)
                ->disableOriginalConstructor()->getMock();
            $renderer->expects($this->once())
                ->method('getFormatArray')
                ->will($this->returnValue(['key' => 'value']));
        }
        $this->addressConfig->expects($this->once())
            ->method('getFormatByCode')
            ->with($code)
            ->will(
                $this->returnValue(
                    new \Magento\Framework\DataObject(!empty($result) ? ['renderer' => $renderer] : [])
                )
            );

        $this->assertEquals($result, $this->helper->getFormat($code));
    }

    public function getFormatDataProvider()
    {
        return [
            ['valid_code', ['key' => 'value']],
            ['invalid_code', ''],
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
            $attributeMetadata = $this->getMockBuilder(\Magento\Customer\Api\Data\AttributeMetadataInterface::class)
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

    public function isAttributeVisibleDataProvider()
    {
        return [
            ['fax', true],
            ['invalid_code', false],
        ];
    }

    /**
     * @dataProvider attributeOnFormDataProvider
     * @param bool $isAllowed
     * @param bool $isMetadataExists
     * @param string $attributeCode
     * @param string $formName
     * @param array $attributeFormsList
     */
    public function testIsAttributeAllowedOnForm(
        $isAllowed,
        $isMetadataExists,
        $attributeCode,
        $formName,
        array $attributeFormsList
    ) {
        $attributeMetadata = null;
        if ($isMetadataExists) {
            $attributeMetadata = $this->getMockBuilder(\Magento\Customer\Api\Data\AttributeMetadataInterface::class)
                ->getMockForAbstractClass();
            $attribute = $this->getMockBuilder(\Magento\Customer\Model\Attribute::class)
                ->disableOriginalConstructor()
                ->getMock();
            $this->attributeResolver->expects($this->once())
                ->method('getModelByAttribute')
                ->with(AddressMetadataManagementInterface::ENTITY_TYPE_ADDRESS, $attributeMetadata)
                ->willReturn($attribute);
            $attribute->expects($this->once())
                ->method('getUsedInForms')
                ->willReturn($attributeFormsList);
        }
        $this->addressMetadataService->expects($this->once())
            ->method('getAttributeMetadata')
            ->with($attributeCode)
            ->willReturn($attributeMetadata);
        $this->assertEquals($isAllowed, $this->helper->isAttributeAllowedOnForm($attributeCode, $formName));
    }

    public function attributeOnFormDataProvider()
    {
        return [
            'metadata not exists' => [
                'isAllowed' => false,
                'isMetadataExists' => false,
                'attributeCode' => 'attribute_code',
                'formName' => 'form_name',
                'attributeFormsList' => [],
                ],
            'form not in the list' => [
                'isAllowed' => false,
                'isMetadataExists' => true,
                'attributeCode' => 'attribute_code',
                'formName' => 'form_name',
                'attributeFormsList' => ['form_1', 'form_2'],
                ],
            'allowed' => [
                'isAllowed' => true,
                'isMetadataExists' => true,
                'attributeCode' => 'attribute_code',
                'formName' => 'form_name',
                'attributeFormsList' => ['form_name', 'form_1', 'form_2'],
            ],
        ];
    }
}
