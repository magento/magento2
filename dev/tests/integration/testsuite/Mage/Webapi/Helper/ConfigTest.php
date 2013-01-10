<?php
/**
 * Config helper tests.
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Class implements tests for Mage_Webapi_Helper_Data class.
 */
class Mage_Webapi_Helper_ConfigTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_Webapi_Helper_Config */
    protected $_helper;

    /**
     * Set up helper.
     */
    protected function setUp()
    {
        $objectManager = new Magento_Test_ObjectManager();
        $this->_helper = $objectManager->get('Mage_Webapi_Helper_Config');
        parent::setUp();
    }

    /**
     * @dataProvider dataProviderForTestConvertSingularToPlural
     */
    public function testConvertSingularToPlural($singular, $expectedPlural)
    {
        $this->assertEquals(
            $expectedPlural,
            $this->_helper->convertSingularToPlural($singular),
            "Conversion from singular to plural was performed incorrectly."
        );
    }

    public static function dataProviderForTestConvertSingularToPlural()
    {
        return array(
            array('customer', 'customers'),
            array('category', 'categories'),
            array('webapi', 'webapis'),
            array('downloadable', 'downloadables'),
            array('eway', 'eways'),
            array('tax', 'taxes'),
            array('', '')
        );
    }

    /**
     * @dataProvider dataProviderTestTranslateArrayTypeName
     * @param string $typeToBeTranslated
     * @param string $expectedResult
     */
    public function testTranslateArrayTypeName($typeToBeTranslated, $expectedResult)
    {
        $this->assertEquals(
            $expectedResult,
            $this->_helper->translateArrayTypeName($typeToBeTranslated),
            "Array type was translated incorrectly."
        );
    }

    public static function dataProviderTestTranslateArrayTypeName()
    {
        return array(
            array('ComplexType[]', 'ArrayOfComplexType'),
            array('string[]', 'ArrayOfString'),
            array('integer[]', 'ArrayOfInt'),
            array('bool[]', 'ArrayOfBoolean'),
        );
    }

    /**
     * @dataProvider dataProviderForTestTranslateTypeName
     * @param string $typeName
     * @param string $expectedResult
     */
    public function testTranslateTypeName($typeName, $expectedResult)
    {
        $this->assertEquals(
            $expectedResult,
            $this->_helper->translateTypeName($typeName),
            "Type translation was performed incorrectly."
        );
    }

    public static function dataProviderForTestTranslateTypeName()
    {
        return array(
            array('Mage_Customer_Model_Webapi_CustomerData', 'CustomerData'),
            array('Mage_Catalog_Model_Webapi_ProductData', 'CatalogProductData'),
            array('Vendor_Customer_Model_Webapi_Customer_AddressData', 'VendorCustomerAddressData'),
            array('Producer_Module_Model_Webapi_ProducerData', 'ProducerModuleProducerData'),
            array('Producer_Module_Model_Webapi_ProducerModuleData', 'ProducerModuleProducerModuleData'),
        );
    }

    public function testTranslateTypeNameInvalidArgument()
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid parameter type "Invalid_Type_Name".');
        $this->_helper->translateTypeName('Invalid_Type_Name');
    }

    public function testGetBodyParamNameInvalidInterface()
    {
        $methodName = 'updateV1';
        $bodyPosition = 2;
        $this->setExpectedException(
            'LogicException',
            sprintf(
                'Method "%s" must have parameter for passing request body. '
                    . 'Its position must be "%s" in method interface.',
                $methodName,
                $bodyPosition
            )
        );
        $this->_helper->getOperationBodyParamName(
            Mage_Webapi_Helper_Data::createMethodReflection(
                'Vendor_Module_Controller_Webapi_Invalid_Interface',
                $methodName
            )
        );
    }

    public function testGetIdParamNameEmptyMethodInterface()
    {
        $this->setExpectedException('LogicException', 'must have at least one parameter: resource ID.');
        $this->_helper->getOperationIdParamName(
            Mage_Webapi_Helper_Data::createMethodReflection(
                'Vendor_Module_Controller_Webapi_Invalid_Interface',
                'emptyInterfaceV2'
            )
        );
    }

    public function testGetResourceNamePartsException()
    {
        $className = 'Vendor_Module_Webapi_Resource_Invalid';
        $this->setExpectedException(
            'InvalidArgumentException',
            sprintf('The controller class name "%s" is invalid.', $className)
        );
        $this->_helper->getResourceNameParts($className);
    }

    /**
     * @dataProvider dataProviderForTestGetResourceNameParts
     * @param $className
     * @param $expectedParts
     */
    public function testGetResourceNameParts($className, $expectedParts)
    {
        $this->assertEquals(
            $expectedParts,
            $this->_helper->getResourceNameParts($className),
            "Resource parts for rest route were identified incorrectly."
        );
    }

    public static function dataProviderForTestGetResourceNameParts()
    {
        return array(
            array('Vendor_Customer_Controller_Webapi_Customer_Address', array('VendorCustomer', 'Address')),
            /** Check removal of 'Mage' prefix as well as duplicating parts ('Customer') */
            array('Mage_Customer_Controller_Webapi_Customer_Address', array('Customer', 'Address')),
        );
    }

    public function testGetIdParamException()
    {
        $className = 'Vendor_Module_Webapi_Resource_Invalid';
        $this->setExpectedException('LogicException', sprintf('"%s" is not a valid resource class.', $className));
        $this->_helper->getOperationIdParamName(
            Mage_Webapi_Helper_Data::createMethodReflection($className, 'updateV1')
        );
    }
}
