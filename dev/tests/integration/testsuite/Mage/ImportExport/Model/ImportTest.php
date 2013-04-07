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
 * @category    Mage
 * @package     Mage_ImportExport
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @magentoDataFixture Mage/ImportExport/_files/import_data.php
 */
class Mage_ImportExport_Model_ImportTest extends PHPUnit_Framework_TestCase
{
    /**
     * Model object which is used for tests
     *
     * @var Mage_ImportExport_Model_Import
     */
    protected $_model;

    /**
     * Expected entity behaviors
     *
     * @var array
     */
    protected $_entityBehaviors = array(
        'catalog_product' => array(
            'token' => 'Mage_ImportExport_Model_Source_Import_Behavior_Basic',
            'code'  => 'basic_behavior',
        ),
        'customer_composite' => array(
            'token' => 'Mage_ImportExport_Model_Source_Import_Behavior_Basic',
            'code'  => 'basic_behavior',
        ),
        'customer' => array(
            'token' => 'Mage_ImportExport_Model_Source_Import_Behavior_Custom',
            'code'  => 'custom_behavior',
        ),
        'customer_address' => array(
            'token' => 'Mage_ImportExport_Model_Source_Import_Behavior_Custom',
            'code'  => 'custom_behavior',
        ),
    );

    /**
     * Expected unique behaviors
     *
     * @var array
     */
    protected $_uniqueBehaviors = array(
        'basic_behavior'  => 'Mage_ImportExport_Model_Source_Import_Behavior_Basic',
        'custom_behavior' => 'Mage_ImportExport_Model_Source_Import_Behavior_Custom',
    );

    protected function setUp()
    {
        $this->_model = Mage::getModel('Mage_ImportExport_Model_Import');
    }

    /**
     * @covers Mage_ImportExport_Model_Import::_getEntityAdapter
     */
    public function testImportSource()
    {
        /** @var $customersCollection Mage_Customer_Model_Resource_Customer_Collection */
        $customersCollection = Mage::getResourceModel('Mage_Customer_Model_Resource_Customer_Collection');

        $existCustomersCount = count($customersCollection->load());

        $customersCollection->resetData();
        $customersCollection->clear();

        $this->_model->importSource();

        $customers = $customersCollection->getItems();

        $addedCustomers = count($customers) - $existCustomersCount;

        $this->assertGreaterThan($existCustomersCount, $addedCustomers);
    }

    public function testValidateSource()
    {
        $this->_model->setEntity('catalog_product');
        /** @var Mage_ImportExport_Model_Import_SourceAbstract|PHPUnit_Framework_MockObject_MockObject $source */
        $source = $this->getMockForAbstractClass('Mage_ImportExport_Model_Import_SourceAbstract', array(
            array('sku', 'name')
        ));
        $source->expects($this->any())->method('_getNextRow')->will($this->returnValue(false));
        $this->assertTrue($this->_model->validateSource($source));
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Entity is unknown
     */
    public function testValidateSourceException()
    {
        $source = $this->getMockForAbstractClass('Mage_ImportExport_Model_Import_SourceAbstract', array(), '', false);
        $this->_model->validateSource($source);
    }

    public function testGetEntity()
    {
        $entityName = 'entity_name';
        $this->_model->setEntity($entityName);
        $this->assertSame($entityName, $this->_model->getEntity());
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Entity is unknown
     */
    public function testGetEntityEntityIsNotSet()
    {
        $this->_model->getEntity();
    }

    /**
     * Test getEntityBehaviors with all required data
     * Can't check array on equality because this test should be useful for CE
     *
     * @covers Mage_ImportExport_Model_Import::getEntityBehaviors
     */
    public function testGetEntityBehaviors()
    {
        $importModel = $this->_model;
        $actualBehaviors = $importModel::getEntityBehaviors();

        foreach ($this->_entityBehaviors as $entityKey => $behaviorData) {
            $this->assertArrayHasKey($entityKey, $actualBehaviors);
            $this->assertEquals($behaviorData, $actualBehaviors[$entityKey]);
        }
    }

    /**
     * Test getEntityBehaviors with not existing behavior class
     *
     * @magentoConfigFixture global/importexport/import_entities/customer/behavior_token Unknown_Behavior_Class
     *
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Invalid behavior token for customer
     */
    public function testGetEntityBehaviorsWithUnknownBehavior()
    {
        $importModel = $this->_model;
        $actualBehaviors = $importModel::getEntityBehaviors();
        $this->assertArrayNotHasKey('customer', $actualBehaviors);
    }

    /**
     * Test getUniqueEntityBehaviors with all required data
     * Can't check array on equality because this test should be useful for CE
     *
     * @covers Mage_ImportExport_Model_Import::getUniqueEntityBehaviors
     */
    public function testGetUniqueEntityBehaviors()
    {
        $importModel = $this->_model;
        $actualBehaviors = $importModel::getUniqueEntityBehaviors();

        foreach ($this->_uniqueBehaviors as $behaviorCode => $behaviorClass) {
            $this->assertArrayHasKey($behaviorCode, $actualBehaviors);
            $this->assertEquals($behaviorClass, $actualBehaviors[$behaviorCode]);
        }
    }
}
