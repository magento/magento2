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
 * @category    Magento
 * @package     Mage_ImportExport
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test for abstract export model
 *
 * @group module:Mage_ImportExport
 */
class Mage_ImportExport_Model_Export_EntityAbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_ImportExport_Model_Export_EntityAbstract
     */
    protected $_model;

    protected function setUp()
    {
        parent::setUp();
        $this->_model = $this->getMockForAbstractClass('Mage_ImportExport_Model_Export_EntityAbstract');
    }

    protected function tearDown()
    {
        unset($this->_model);
        parent::tearDown();
    }

    /**
     * Check methods which provide ability to manage errors
     */
    public function testAddRowError()
    {
        $errorCode = 'test_error';
        $errorNum = 1;
        $errorMessage = 'Test error!';
        $this->_model->addMessageTemplate($errorCode, $errorMessage);
        $this->_model->addRowError($errorCode, $errorNum);

        $this->assertEquals(1, $this->_model->getErrorsCount());
        $this->assertEquals(1, $this->_model->getInvalidRowsCount());
        $this->assertArrayHasKey($errorMessage, $this->_model->getErrorMessages());
    }

    /**
     * Check methods which provide ability to manage writer object
     */
    public function testGetWriter()
    {
        $this->_model->setWriter(Mage::getModel('Mage_ImportExport_Model_Export_Adapter_Csv'));
        $this->assertInstanceOf('Mage_ImportExport_Model_Export_Adapter_Csv', $this->_model->getWriter());
    }

    /**
     * Check that method throw exception when writer was not defined
     *
     * @expectedException Mage_Core_Exception
     */
    public function testGetWriterThrowsException()
    {
        $this->_model->getWriter();
    }

    /**
     * Test for method filterAttributeCollection
     */
    public function testFilterAttributeCollection()
    {
        /** @var $model Stub_Mage_ImportExport_Model_Export_EntityAbstract */
        $model = $this->getMockForAbstractClass('Stub_Mage_ImportExport_Model_Export_EntityAbstract');
        $collection = Mage::getResourceModel('Mage_Customer_Model_Resource_Attribute_Collection');
        $collection = $model->filterAttributeCollection($collection);
        /**
         * Check that disabled attributes is not existed in attribute collection
         */
        $existedAttributes = array();
        /** @var $attribute Mage_Customer_Model_Attribute */
        foreach ($collection as $attribute) {
            $existedAttributes[] = $attribute->getAttributeCode();
        }
        $disabledAttributes = $model->getDisabledAttributes();
        foreach ($disabledAttributes as $attributeCode) {
            $this->assertNotContains(
                $attributeCode,
                $existedAttributes,
                'Disabled attribute "' . $attributeCode . '" existed in collection'
            );
        }
    }
}

/**
 * Stub abstract class which provide to change protected property "$_disabledAttrs" and test methods depended on it
 */
abstract class Stub_Mage_ImportExport_Model_Export_EntityAbstract
    extends Mage_ImportExport_Model_Export_EntityAbstract
{
    public function __construct()
    {
        parent::__construct();
        $this->_disabledAttrs = array('default_billing', 'default_shipping');
    }
}
