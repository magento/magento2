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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_ImportExport_Model_ExportTest extends PHPUnit_Framework_TestCase
{
    /**
     * Model object which used for tests
     *
     * @var Mage_ImportExport_Model_Export
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = Mage::getModel('Mage_ImportExport_Model_Export');
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    /**
     * Test method '_getEntityAdapter' in case when entity is valid
     *
     * @param string $entity
     * @param string $expectedEntityType
     * @dataProvider getEntityDataProvider
     * @covers Mage_ImportExport_Model_Export::_getEntityAdapter
     */
    public function testGetEntityAdapterWithValidEntity($entity, $expectedEntityType)
    {
        $this->_model->setData(array(
            'entity' => $entity
        ));
        $this->_model->getEntityAttributeCollection();
        $this->assertAttributeInstanceOf($expectedEntityType, '_entityAdapter', $this->_model,
            'Entity adapter property has wrong type'
        );
    }

    /**
     * @return array
     */
    public function getEntityDataProvider()
    {
        return array(
            'product'            => array(
                '$entity'             => 'catalog_product',
                '$expectedEntityType' => 'Mage_ImportExport_Model_Export_Entity_Product'
            ),
            'customer main data' => array(
                '$entity'             => 'customer',
                '$expectedEntityType' => 'Mage_ImportExport_Model_Export_Entity_Eav_Customer'
            ),
            'customer address'   => array(
                '$entity'             => 'customer_address',
                '$expectedEntityType' => 'Mage_ImportExport_Model_Export_Entity_Eav_Customer_Address'
            )
        );
    }

    /**
     * Test method '_getEntityAdapter' in case when entity is invalid
     *
     * @expectedException Mage_Core_Exception
     * @covers Mage_ImportExport_Model_Export::_getEntityAdapter
     */
    public function testGetEntityAdapterWithInvalidEntity()
    {
        $this->_model->setData(array(
            'entity' => 'test'
        ));
        $this->_model->getEntityAttributeCollection();
    }
}
