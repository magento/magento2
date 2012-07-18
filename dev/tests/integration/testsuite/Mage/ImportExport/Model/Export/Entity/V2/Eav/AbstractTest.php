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
 * Test for abstract export model V2
 *
 * @group module:Mage_ImportExport
 */
class Mage_ImportExport_Model_Export_Entity_V2_Eav_AbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * Skipped attribute codes
     *
     * @var array
     */
    protected static $_skippedAttributes = array('confirmation', 'lastname');

    /**
     * @var Mage_ImportExport_Model_Export_Entity_V2_Eav_Abstract
     */
    protected $_model;

    /**
     * Entity code
     *
     * @var string
     */
    protected $_entityCode = 'customer';

    protected function setUp()
    {
        parent::setUp();

        /** @var $customerAttributes Mage_Customer_Model_Resource_Attribute_Collection */
        $customerAttributes = Mage::getResourceModel('Mage_Customer_Model_Resource_Attribute_Collection');

        $this->_model = $this->getMockForAbstractClass('Mage_ImportExport_Model_Export_Entity_V2_Eav_Abstract', array(),
            '', false);
        $this->_model->expects($this->any())
            ->method('getEntityTypeCode')
            ->will($this->returnValue($this->_entityCode));
        $this->_model->expects($this->any())
            ->method('getAttributeCollection')
            ->will($this->returnValue($customerAttributes));
        $this->_model->__construct();
    }

    protected function tearDown()
    {
        unset($this->_model);
        parent::tearDown();
    }

    /**
     * Test for method getEntityTypeId()
     */
    public function testGetEntityTypeId()
    {
        $entityCode = 'customer';
        $entityId = Mage::getSingleton('Mage_Eav_Model_Config')
            ->getEntityType($entityCode)
            ->getEntityTypeId();

        $this->assertEquals($entityId, $this->_model->getEntityTypeId());
    }

    /**
     * Test for method _getExportAttrCodes()
     *
     * @covers Mage_ImportExport_Model_Export_Entity_V2_Eav_Abstract::_getExportAttrCodes
     */
    public function testGetExportAttrCodes()
    {
        $this->_checkReflectionMethodSetAccessibleExists();

        $this->_model->setParameters($this->_getSkippedAttributes());
        $method = new ReflectionMethod($this->_model, '_getExportAttributeCodes');
        $method->setAccessible(true);
        $attributes = $method->invoke($this->_model);
        foreach (self::$_skippedAttributes as $code) {
            $this->assertNotContains($code, $attributes);
        }
    }

    /**
     * Test for method filterEntityCollection()
     *
     * magentoDataFixture Mage/ImportExport/_files/customers.php
     */
    public function testFilterEntityCollection()
    {
        $this->markTestIncomplete('BUG MAGETWO-1953');

        $createdAtDate = '2013-01-01';
        /**
         * Change created_at date of first customer for future filter test.
         */
        $customers = Mage::registry('_fixture/Mage_ImportExport_Customer_Collection');
        $customers[0]->setCreatedAt($createdAtDate);
        $customers[0]->save();
        /**
         * Change type of created_at attribute. In this case we have possibility to test date rage filter
         */
        /** @var $attributeCollection Mage_Customer_Model_Resource_Attribute_Collection */
        $attributeCollection = Mage::getResourceModel('Mage_Customer_Model_Resource_Attribute_Collection');
        $attributeCollection->addFieldToFilter('attribute_code', 'created_at');
        /** @var $createdAtAttribute Mage_Customer_Model_Attribute */
        $createdAtAttribute = $attributeCollection->getFirstItem();
        $createdAtAttribute->setBackendType('datetime');
        $createdAtAttribute->save();
        /**
         * Prepare filter.
         */
        $parameters = array(
            Mage_ImportExport_Model_Export::FILTER_ELEMENT_GROUP => array(
                'email' => 'example.com',
                'created_at' => array($createdAtDate, ''),
                'store_id' => Mage::app()->getStore()->getId()
            )
        );
        $this->_model->setParameters($parameters);
        /** @var $customers Mage_Customer_Model_Resource_Customer_Collection */
        $collection = $this->_model->filterEntityCollection(
            Mage::getResourceModel('Mage_Customer_Model_Resource_Customer_Collection')
        );

        $this->assertCount(1, $collection);
        $this->assertEquals($customers[0]->getId(), $collection->getFirstItem()->getId());
    }

    /**
     * Test for method getAttributeOptions()
     */
    public function testGetAttributeOptions()
    {
        /** @var $attributeCollection Mage_Customer_Model_Resource_Attribute_Collection */
        $attributeCollection = Mage::getResourceModel('Mage_Customer_Model_Resource_Attribute_Collection');
        $attributeCollection->addFieldToFilter('attribute_code', 'gender');
        /** @var $attribute Mage_Customer_Model_Attribute */
        $attribute = $attributeCollection->getFirstItem();

        $expectedOptions = array();
        foreach ($attribute->getSource()->getAllOptions(false) as $option) {
            $expectedOptions[$option['value']] = $option['label'];
        }

        $actualOptions = $this->_model->getAttributeOptions($attribute);
        $this->assertEquals($expectedOptions, $actualOptions);
    }

    /**
     * Retrieve list of skipped attributes
     *
     * @return array
     */
    protected function _getSkippedAttributes()
    {
        /** @var $attributeCollection Mage_Customer_Model_Resource_Attribute_Collection */
        $attributeCollection = Mage::getResourceModel('Mage_Customer_Model_Resource_Attribute_Collection');
        $attributeCollection->addFieldToFilter('attribute_code', array('in' => self::$_skippedAttributes));
        $skippedAttributes = array();
        /** @var $attribute  Mage_Customer_Model_Attribute */
        foreach ($attributeCollection as $attribute) {
            $skippedAttributes[$attribute->getAttributeCode()] = $attribute->getId();
        }

        return array(
            Mage_ImportExport_Model_Export::FILTER_ELEMENT_SKIP => $skippedAttributes
        );
    }

    /**
     * Check that method ReflectionMethod::setAccessible exists
     */
    protected function _checkReflectionMethodSetAccessibleExists()
    {
        if (!method_exists('ReflectionMethod', 'setAccessible')) {
            $this->markTestSkipped('Test requires ReflectionMethod::setAccessible (PHP 5 >= 5.3.2).');
        }
    }
}
