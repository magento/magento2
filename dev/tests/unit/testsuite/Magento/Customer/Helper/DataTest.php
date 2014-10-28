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

class DataTest extends \PHPUnit_Framework_TestCase
{
    const FORM_CODE = 'FORM_CODE';

    const ENTITY = 'ENTITY';

    const SCOPE = 'SCOPE';

    protected $_expected = array(
        'filter_key' => 'filter_value',
        'is_in_request_data' => 'request_data_value',
        'is_not_in_request_data' => false,
        'attribute_is_front_end_input' => true
    );

    /** @var \Magento\Customer\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $_dataHelper;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_mockRequest;

    /** @var array */
    protected $_additionalAttributes;

    /** @var \Magento\Customer\Model\Metadata\Form|\PHPUnit_Framework_MockObject_MockObject */
    protected $_mockMetadataForm;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfigMock;

    /** @var \Magento\Customer\Helper\Data */
    protected $model;

    public function setUp()
    {
        $this->_mockRequest = $this->getMock(
            'Magento\Framework\App\RequestInterface',
            ['getPost', 'getModuleName', 'setModuleName', 'getActionName', 'setActionName', 'getParam', 'getCookie'],
            [],
            '',
            false
        );
        $this->_additionalAttributes = array('is_in_request_data', 'is_not_in_request_data');
        $this->_mockMetadataForm = $this->getMockBuilder(
            '\Magento\Customer\Model\Metadata\Form'
        )->disableOriginalConstructor()->getMock();

        $this->scopeConfigMock = $this->getMock(
            'Magento\Framework\App\Config\ScopeConfigInterface',
            ['getValue', 'isSetFlag'],
            [],
            '',
            false
        );
    }

    protected function prepareExtractCustomerData()
    {
        $this->_dataHelper = $this->getMockBuilder(
            '\Magento\Customer\Helper\Data'
        )->disableOriginalConstructor()->setMethods(
            array('__construct')
        )->getMock();

        $filteredData = array(
            'filter_key' => 'filter_value',
            'attribute_is_not_front_end_input' => false,
            'attribute_is_front_end_input' => true
        );
        $this->_mockMetadataForm->expects(
            $this->once()
        )->method(
            'extractData'
        )->with(
            $this->_mockRequest,
            self::SCOPE
        )->will(
            $this->returnValue($filteredData)
        );

        $requestData = array('is_in_request_data' => 'request_data_value');
        $this->_mockRequest->expects(
            $this->once()
        )->method(
            'getPost'
        )->with(
            self::SCOPE
        )->will(
            $this->returnValue($requestData)
        );

        $attributeIsFrontEndInput = $this->getMockBuilder(
            '\Magento\Customer\Service\V1\Data\Eav\AttributeMetadata'
        )->disableOriginalConstructor()->getMock();
        $attributeIsFrontEndInput->expects(
            $this->once()
        )->method(
            'getAttributeCode'
        )->will(
            $this->returnValue('attribute_is_front_end_input')
        );
        $attributeIsFrontEndInput->expects(
            $this->once()
        )->method(
            'getFrontendInput'
        )->will(
            $this->returnValue('boolean')
        );

        $attributeIsNotFrontEndInput = $this->getMockBuilder(
            '\Magento\Customer\Service\V1\Data\Eav\AttributeMetadata'
        )->disableOriginalConstructor()->getMock();
        $attributeIsNotFrontEndInput->expects(
            $this->once()
        )->method(
            'getAttributeCode'
        )->will(
            $this->returnValue('attribute_is_not_front_end_input')
        );
        $attributeIsNotFrontEndInput->expects(
            $this->once()
        )->method(
            'getFrontendInput'
        )->will(
            $this->returnValue(false)
        );

        $formAttributes = array($attributeIsFrontEndInput, $attributeIsNotFrontEndInput);
        $this->_mockMetadataForm->expects(
            $this->once()
        )->method(
            'getAttributes'
        )->will(
            $this->returnValue($formAttributes)
        );
    }

    public function testExtractCustomerData()
    {
        $this->prepareExtractCustomerData();
        $this->assertEquals(
            $this->_expected,
            $this->_dataHelper->extractCustomerData(
                $this->_mockRequest,
                self::FORM_CODE,
                self::ENTITY,
                $this->_additionalAttributes,
                self::SCOPE,
                $this->_mockMetadataForm
            )
        );
    }

    public function testExtractCustomerDataWithFactory()
    {
        $this->prepareExtractCustomerData();
        /** @var \Magento\Customer\Model\Metadata\FormFactory|\PHPUnit_Framework_MockObject_MockObject */
        $mockFormFactory = $this->getMockBuilder(
            '\Magento\Customer\Model\Metadata\FormFactory'
        )->disableOriginalConstructor()->setMethods(
            array('create')
        )->getMock();

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $arguments = array('formFactory' => $mockFormFactory);
        $this->_dataHelper = $objectManagerHelper->getObject('\Magento\Customer\Helper\Data', $arguments);

        $mockFormFactory->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            self::ENTITY,
            self::FORM_CODE,
            [],
            false,
            \Magento\Customer\Model\Metadata\Form::DONT_IGNORE_INVISIBLE
        )->will(
            $this->returnValue($this->_mockMetadataForm)
        );

        $this->assertEquals(
            $this->_expected,
            $this->_dataHelper->extractCustomerData(
                $this->_mockRequest,
                self::FORM_CODE,
                self::ENTITY,
                $this->_additionalAttributes,
                self::SCOPE
            )
        );
    }

    public function testGetCustomerGroupIdBasedOnVatNumberWithoutAutoAssign()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $arguments = array(
            'scopeConfig' => $this->scopeConfigMock
        );
        $this->model = $objectManagerHelper->getObject('Magento\Customer\Helper\Data', $arguments);

        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(
                \Magento\Customer\Helper\Data::XML_PATH_CUSTOMER_GROUP_AUTO_ASSIGN,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                'store'
            )->will($this->returnValue(false));

        $vatResult = $this->getMock(
            'Magento\Framework\Object',
            [],
            [],
            '',
            false
        );

        $this->assertNull($this->model->getCustomerGroupIdBasedOnVatNumber('GB', $vatResult, 'store'));
    }

    /**
     * @param string $countryCode
     * @param bool $resultValid
     * @param bool $resultSuccess
     * @param string $merchantCountryCode
     * @param int $vatDomestic
     * @param int $vatIntra
     * @param int $vatInvalid
     * @param int $vatError
     * @param int|null $groupId
     * @dataProvider dataProviderGetCustomerGroupIdBasedOnVatNumber
     */
    public function testGetCustomerGroupIdBasedOnVatNumber(
        $countryCode,
        $resultValid,
        $resultSuccess,
        $merchantCountryCode,
        $vatDomestic,
        $vatIntra,
        $vatInvalid,
        $vatError,
        $groupId
    ) {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $arguments = [
            'scopeConfig' => $this->scopeConfigMock
        ];
        $this->model = $objectManagerHelper->getObject('Magento\Customer\Helper\Data', $arguments);

        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(
                \Magento\Customer\Helper\Data::XML_PATH_CUSTOMER_GROUP_AUTO_ASSIGN,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                'store'
            )->will($this->returnValue(true));

        $configMap = [
            [
                \Magento\Customer\Helper\Data::XML_PATH_CUSTOMER_VIV_DOMESTIC_GROUP,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                'store',
                $vatDomestic
            ],
            [
                \Magento\Customer\Helper\Data::XML_PATH_CUSTOMER_VIV_INTRA_UNION_GROUP,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                'store',
                $vatIntra
            ],
            [
                \Magento\Customer\Helper\Data::XML_PATH_CUSTOMER_VIV_INVALID_GROUP,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                'store',
                $vatInvalid
            ],
            [
                \Magento\Customer\Helper\Data::XML_PATH_CUSTOMER_VIV_ERROR_GROUP,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                'store',
                $vatError
            ],
            [
                \Magento\Customer\Helper\Data::XML_PATH_MERCHANT_COUNTRY_CODE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                'store',
                $merchantCountryCode
            ],
        ];
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValueMap($configMap));

        $vatResult = $this->getMock(
            'Magento\Framework\Object',
            ['getIsValid', 'getRequestSuccess'],
            [],
            '',
            false
        );
        $vatResult->expects($this->any())
            ->method('getIsValid')
            ->will($this->returnValue($resultValid));
        $vatResult->expects($this->any())
            ->method('getRequestSuccess')
            ->will($this->returnValue($resultSuccess));

        $this->assertEquals(
            $groupId,
            $this->model->getCustomerGroupIdBasedOnVatNumber($countryCode, $vatResult, 'store')
        );
    }

    public function dataProviderGetCustomerGroupIdBasedOnVatNumber()
    {
        return [
            ['US', false, false, 'US', null, null, null, null, 0],
            ['US', false, false, 'GB', null, null, null, null, 0],
            ['US', true, false, 'US', null, null, null, null, 0],
            ['US', false, true, 'US', null, null, null, null, 0],
            ['GB', false, false, 'GB', 3, 4, 5, 6, 6],
            ['GB', false, false, 'DE', 3, 4, 5, 6, 6],
            ['GB', true, true, 'GB', 3, 4, 5, 6, 3],
            ['GB', true, true, 'DE', 3, 4, 5, 6, 4],
            ['GB', false, true, 'DE', 3, 4, 5, 6, 5],
            ['GB', false, true, 'GB', 3, 4, 5, 6, 5],
            ['GB', false, false, 'GB', null, null, null, null, 0],
            ['GB', false, false, 'DE', null, null, null, null, 0],
            ['GB', true, true, 'GB', null, null, null, null, 0],
            ['GB', true, true, 'DE', null, null, null, null, 0],
            ['GB', false, true, 'DE', null, null, null, null, 0],
            ['GB', false, true, 'GB', null, null, null, null, 0],
        ];
    }
}
