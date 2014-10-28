<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Customer\Helper;

use Magento\Customer\Service\V1\AddressMetadataServiceInterface;

class AddressTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Customer\Helper\Address|\PHPUnit_Framework_MockObject_MockObject */
    protected $helper;

    /** @var \Magento\Framework\App\Helper\Context */
    protected $context;

    /** @var \Magento\Framework\View\Element\BlockFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $blockFactory;

    /** @var \Magento\Framework\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManager;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfig;

    /** @var \Magento\Customer\Service\V1\CustomerMetadataServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerMetadataService;

    /** @var \Magento\Customer\Model\Address\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $addressConfig;

    /** @var \PHPUnit_Framework_MockObject_MockObject|AddressMetadataServiceInterface */
    private $addressMetadataService;

    protected function setUp()
    {
        $this->context = $this->getMockBuilder('Magento\Framework\App\Helper\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->blockFactory = $this->getMockBuilder(
            'Magento\Framework\View\Element\BlockFactory'
        )->disableOriginalConstructor()->getMock();
        $this->storeManager = $this->getMockBuilder(
            'Magento\Framework\StoreManagerInterface'
        )->disableOriginalConstructor()->getMock();
        $this->scopeConfig = $this->getMockBuilder(
            'Magento\Framework\App\Config\ScopeConfigInterface'
        )->disableOriginalConstructor()->getMock();
        $this->customerMetadataService = $this->getMockBuilder(
            'Magento\Customer\Service\V1\CustomerMetadataServiceInterface'
        )->disableOriginalConstructor()->getMock();
        $this->addressConfig = $this->getMockBuilder(
            'Magento\Customer\Model\Address\Config'
        )->disableOriginalConstructor()->getMock();

        $this->addressMetadataService = $this->getMockBuilder('Magento\Customer\Service\V1\AddressMetadataService')
            ->disableOriginalConstructor()
            ->getMock();

        $this->helper = new \Magento\Customer\Helper\Address(
            $this->context,
            $this->blockFactory,
            $this->storeManager,
            $this->scopeConfig,
            $this->customerMetadataService,
            $this->addressMetadataService,
            $this->addressConfig
        );
    }

    /**
     * @param int $numLines
     * @param int $expectedNumLines
     * @dataProvider providerGetStreetLines
     */
    public function testGetStreetLines($numLines, $expectedNumLines)
    {
        $attributeMock = $this->getMockBuilder(
            'Magento\Customer\Service\V1\Data\Eav\AttributeMetadata'
        )->disableOriginalConstructor()->getMock();
        $attributeMock->expects($this->any())->method('getMultilineCount')->will($this->returnValue($numLines));

        $this->addressMetadataService
            ->expects($this->any())
            ->method('getAttributeMetadata')
            ->will($this->returnValue($attributeMock));

        $store = $this->getMockBuilder('Magento\Store\Model\Store')->disableOriginalConstructor()->getMock();
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $this->assertEquals($expectedNumLines, $this->helper->getStreetLines());
    }

    public function providerGetStreetLines()
    {
        return array(
            array(-1, 2),
            array(0, 2),
            array(1, 1),
            array(2, 2),
            array(3, 3),
            array(4, 4),
            array(5, 5),
            array(10, 10),
            array(15, 15),
            array(20, 20),
            array(21, 20),
        );
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
            $this->scopeConfig,
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
        $blockMock = $this->getMockBuilder('Magento\Framework\View\Element\BlockInterface')->getMock();
        $blockFactory = $this->getMockBuilder(
            'Magento\Framework\View\Element\BlockFactory'
        )->disableOriginalConstructor()->getMock();
        $blockFactory->expects($this->once())
            ->method('createBlock')
            ->with('some_test_block', array())
            ->will($this->returnValue($blockMock));
        return array(
            array('some_test_block', $blockFactory, $blockMock),
            array($blockMock, $blockFactory, $blockMock),
        );
    }

    public function testGetConfigCanShowConfig()
    {
        $result = array('key1' => 'value1', 'key2' => 'value2');
        $store = $this->getMockBuilder('Magento\Store\Model\Store')->disableOriginalConstructor()->getMock();
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

    /**
     * @param $attrCode
     * @param $attrClass
     * @param $customAttrClass
     * @param $result
     * @dataProvider getAttributeValidationClassDataProvider
     */
    public function testGetAttributeValidationClass($attrCode, $attrClass, $customAttrClass, $result)
    {
        $attributeMock = $this->getMockBuilder('Magento\Customer\Service\V1\Data\Eav\AttributeMetadata')
            ->disableOriginalConstructor()->getMock();
        $attributeMock->expects($this->any())->method('getFrontendClass')->will($this->returnValue($attrClass));

        $customAttrMock = $this->getMockBuilder('Magento\Customer\Service\V1\Data\Eav\AttributeMetadata')
            ->disableOriginalConstructor()->getMock();
        $customAttrMock->expects($this->any())->method('isVisible')->will($this->returnValue(true));
        $customAttrMock->expects($this->any())->method('getFrontendClass')->will($this->returnValue($customAttrClass));

        $this->customerMetadataService->expects($this->any())
            ->method('getAttributeMetadata')
            ->will($this->returnValue($customAttrMock));

        $this->addressMetadataService->expects($this->any())
            ->method('getAttributeMetadata')
            ->will($this->returnValue($attributeMock));

        $this->assertEquals($result, $this->helper->getAttributeValidationClass($attrCode));
    }

    public function getAttributeValidationClassDataProvider()
    {
        return array(
            array('attr_code', 'Attribute_Class', '', 'Attribute_Class'),
            array('firstname', 'Attribute_Class', 'Attribute2_Class', 'Attribute2_Class'),
        );
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
        return array(
            array(array('street1', 'street2', 'street3', 'street4'), 3, array('street1 street2', 'street3', 'street4')),
            array(array('street1', 'street2', 'street3', 'street4'), 2, array('street1 street2', 'street3 street4')),
        );
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
        return array(
            array(0, true),
            array(1, false),
            array(2, true),
        );
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
        return array(
            array(0, true),
            array(1, false),
            array(2, true),
        );
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
        return array(
            array(0, 'address_type_store_0'),
            array(1, 'address_type_store_1'),
            array(2, 'address_type_store_2'),
        );
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
            ->will($this->returnValue(
                new \Magento\Framework\Object(!is_null($result)?array('renderer' => $result):array())
            ));
        $this->assertEquals($result, $this->helper->getFormatTypeRenderer($code));
    }

    public function getFormatTypeRendererDataProvider()
    {
        $renderer = $this->getMockBuilder('Magento\Customer\Block\Address\Renderer\RendererInterface')
            ->disableOriginalConstructor()->getMock();
        return array(
            array('valid_code', $renderer),
            array('invalid_code', null)
        );
    }

    /**
     * @param string $code
     * @param array $result
     * @dataProvider getFormatDataProvider
     */
    public function testGetFormat($code, $result)
    {
        if ($result) {
            $renderer = $this->getMockBuilder('Magento\Customer\Block\Address\Renderer\RendererInterface')
                ->disableOriginalConstructor()->getMock();
            $renderer->expects($this->once())
                ->method('getFormatArray')
                ->will($this->returnValue(array('key' => 'value')));
        }
        $this->addressConfig->expects($this->once())
            ->method('getFormatByCode')
            ->with($code)
            ->will($this->returnValue(
                new \Magento\Framework\Object(!empty($result)?array('renderer' => $renderer):array())
            ));

        $this->assertEquals($result, $this->helper->getFormat($code));
    }

    public function getFormatDataProvider()
    {
        return array(
            array('valid_code', array('key' => 'value')),
            array('invalid_code', '')
        );
    }
}
