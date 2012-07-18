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
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_ImportExport_Model_Import_Entity_V2_Eav_Abstract
 */
class Mage_ImportExport_Model_Import_Entity_V2_Eav_AbstractTest extends PHPUnit_Framework_TestCase
{
    /**#@+
     * Mage registry singleton prefix
     */
    const MAGE_REGISTRY_SINGLETON_PREFIX = '_singleton/';
    /**#@-*/

    /**#@+
     * Mage_Eav_Model_Config class name
     */
    const MAGE_EAV_MODEL_CONFIG = 'Mage_Eav_Model_Config';
    /**#@-*/

    /**#@+
     * Mage entity type code and id
     */
    const ENTITY_TYPE_CODE = 'type_code';
    const ENTITY_TYPE_ID   = 1;
    /**#@-*/

    /**
     * Abstract import entity eav model
     *
     * @var Mage_ImportExport_Model_Import_Entity_V2_Eav_Abstract|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    public function setUp()
    {
        parent::setUp();

        $this->_unregisterMageEavModelConfigSingleton();
        $this->_mockMageEavModelConfigSingletonAndRegisterInRegistry();
        $this->_model = $this->_getModelMock();
    }

    public function tearDown()
    {
        unset($this->_model);
        $this->_unregisterMageEavModelConfigSingleton();

        parent::tearDown();
    }

    /**
     * Get abstract import entity eav model mock
     *
     * @return Mage_ImportExport_Model_Import_Entity_V2_Eav_Abstract|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getModelMock()
    {
        $modelMock = $this->getMockForAbstractClass('Mage_ImportExport_Model_Import_Entity_V2_Eav_Abstract', array(),
            '', false, true, true, array('getEntityTypeCode')
        );

        $modelMock->expects($this->once())
            ->method('getEntityTypeCode')
            ->will($this->returnValue(self::ENTITY_TYPE_CODE));

        return $modelMock;
    }

    /**
     * Create mock for Mage_Eav_Model_Config singleton and put it to the registry
     */
    protected function _mockMageEavModelConfigSingletonAndRegisterInRegistry()
    {
        $modelMock = $this->getMock(self::MAGE_EAV_MODEL_CONFIG, array('getEntityType'), array(), '', false, true,
            true
        );

        $modelMock->expects($this->once())
            ->method('getEntityType')
            ->with(self::ENTITY_TYPE_CODE)
            ->will($this->returnValue(new Stub_Mage_Eav_Model_Entity_Type()));

        Mage::register(self::MAGE_REGISTRY_SINGLETON_PREFIX . self::MAGE_EAV_MODEL_CONFIG, $modelMock);
    }

    /**
     * Remove mock for Mage_Eav_Model_Config singleton from the registry
     */
    protected function _unregisterMageEavModelConfigSingleton()
    {
        Mage::unregister(self::MAGE_REGISTRY_SINGLETON_PREFIX . self::MAGE_EAV_MODEL_CONFIG);
    }

    /**
     * Test entity type id getter
     *
     * @covers Mage_ImportExport_Model_Import_Entity_V2_Eav_Abstract::getEntityTypeId()
     */
    public function testGetEntityTypeId()
    {
        $this->assertEquals(self::ENTITY_TYPE_ID, $this->_model->getEntityTypeId());
    }
}

/**
 * Stub class for Mage_Eav_Model_Entity_Type
 */
class Stub_Mage_Eav_Model_Entity_Type
{
    /**
     * Stub for Mage_Eav_Model_Entity_Type::getEntityTypeId()
     *
     * @return int
     */
    public function getEntityTypeId()
    {
        return Mage_ImportExport_Model_Import_Entity_V2_Eav_AbstractTest::ENTITY_TYPE_ID;
    }
}
