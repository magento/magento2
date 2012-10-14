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
 * Test class for Mage_ImportExport_Model_Resource_Import_CustomerComposite_Data
 */
class Mage_ImportExport_Model_Resource_Import_CustomerComposite_DataTest extends PHPUnit_Framework_TestCase
{
    /**
     * Array of customer attributes
     *
     * @var array
     */
    protected $_customerAttributes = array('customer_attribute1', 'customer_attribute2');

    /**
     * Generate dependencies for model
     *
     * @param string $entityType
     * @param array $bunchData
     * @return array
     */
    protected function _getDependencies($entityType, $bunchData)
    {
        /** @var $statementMock Varien_Db_Statement_Pdo_Mysql */
        $statementMock = $this->getMock('Varien_Db_Statement_Pdo_Mysql', array('setFetchMode', 'getIterator'), array(),
            '', false
        );
        $statementMock->expects($this->any())
            ->method('getIterator')
            ->will($this->returnValue(new ArrayIterator($bunchData)));

        /** @var $selectMock Varien_Db_Select */
        $selectMock = $this->getMock('Varien_Db_Select', array('from', 'order'),
            array(), '', false
        );
        $selectMock->expects($this->any())
            ->method('from')
            ->will($this->returnSelf());
        $selectMock->expects($this->any())
            ->method('order')
            ->will($this->returnSelf());

        /** @var $adapterMock Varien_Db_Adapter_Pdo_Mysql */
        $adapterMock = $this->getMock('Varien_Db_Adapter_Pdo_Mysql', array('select', 'from', 'order', 'query'),
            array(), '', false
        );
        $adapterMock->expects($this->any())
            ->method('select')
            ->will($this->returnValue($selectMock));
        $adapterMock->expects($this->any())
            ->method('query')
            ->will($this->returnValue($statementMock));

        /** @var $resourceModelMock Mage_Core_Model_Resource */
        $resourceModelMock = $this->getMock('Mage_Core_Model_Resource', array('_newConnection', 'getTableName'),
            array(), '', false
        );
        $resourceModelMock->expects($this->any())
            ->method('_newConnection')
            ->will($this->returnValue($adapterMock));
        $resourceModelMock->createConnection('core_write', '', array());

        $data = array(
            'json_helper' => new Mage_Core_Helper_Data(),
            'resource'    => $resourceModelMock,
            'entity_type' => $entityType
        );

        if ($entityType == Mage_ImportExport_Model_Import_Entity_CustomerComposite::COMPONENT_ENTITY_ADDRESS) {
            $data['customer_attributes'] = $this->_customerAttributes;
        }

        return $data;
    }

    /**
     * @covers Mage_ImportExport_Model_Resource_Import_CustomerComposite_Data::getNextBunch
     * @covers Mage_ImportExport_Model_Resource_Import_CustomerComposite_Data::_prepareRow
     * @covers Mage_ImportExport_Model_Resource_Import_CustomerComposite_Data::_prepareAddressRowData
     *
     * @dataProvider getNextBunchDataProvider
     */
    public function testGetNextBunch($entityType, $bunchData, $expectedData)
    {
        $dependencies = $this->_getDependencies($entityType, $bunchData);

        $object = new Mage_ImportExport_Model_Resource_Import_CustomerComposite_Data($dependencies);
        $this->assertEquals($expectedData, $object->getNextBunch());
    }

    /**
     * Data provider of row data and expected result of getNextBunch() method
     *
     * @return array
     */
    public function getNextBunchDataProvider()
    {
        return array(
            'address entity' => array(
                '$entityType' => Mage_ImportExport_Model_Import_Entity_CustomerComposite::COMPONENT_ENTITY_ADDRESS,
                '$bunchData'    => array(array(Zend_Json::encode(array(
                    array(
                        '_scope' => Mage_ImportExport_Model_Import_Entity_CustomerComposite::SCOPE_DEFAULT,
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_WEBSITE => 'website1',
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_EMAIL => 'email1',
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_ADDRESS_ID => null,
                        Mage_ImportExport_Model_Import_Entity_CustomerComposite::COLUMN_DEFAULT_BILLING => 'value',
                        Mage_ImportExport_Model_Import_Entity_CustomerComposite::COLUMN_DEFAULT_SHIPPING => 'value',
                        'customer_attribute1' => 'value',
                        'customer_attribute2' => 'value',
                        Mage_ImportExport_Model_Import_Entity_CustomerComposite::COLUMN_ADDRESS_PREFIX . 'attribute1'
                            => 'value',
                        Mage_ImportExport_Model_Import_Entity_CustomerComposite::COLUMN_ADDRESS_PREFIX . 'attribute2'
                            => 'value'
                    )
                )))),
                '$expectedData' => array(
                    0 => array(
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_WEBSITE => 'website1',
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_EMAIL     => 'email1',
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_ADDRESS_ID => NULL,
                        Mage_ImportExport_Model_Import_Entity_CustomerComposite::COLUMN_DEFAULT_BILLING => 'value',
                        Mage_ImportExport_Model_Import_Entity_CustomerComposite::COLUMN_DEFAULT_SHIPPING => 'value',
                        'attribute1' => 'value',
                        'attribute2' => 'value'
                    ),
                ),
            ),
            'customer entity default scope' => array(
                '$entityType' => Mage_ImportExport_Model_Import_Entity_CustomerComposite::COMPONENT_ENTITY_CUSTOMER,
                '$bunchData'    => array(array(Zend_Json::encode(array(
                    array(
                        '_scope' => Mage_ImportExport_Model_Import_Entity_CustomerComposite::SCOPE_DEFAULT,
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_WEBSITE => 'website1',
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_EMAIL => 'email1',
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_ADDRESS_ID => null,
                        Mage_ImportExport_Model_Import_Entity_CustomerComposite::COLUMN_DEFAULT_BILLING => 'value',
                        Mage_ImportExport_Model_Import_Entity_CustomerComposite::COLUMN_DEFAULT_SHIPPING => 'value',
                        'customer_attribute1' => 'value',
                        'customer_attribute2' => 'value',
                        Mage_ImportExport_Model_Import_Entity_CustomerComposite::COLUMN_ADDRESS_PREFIX . 'attribute1'
                            => 'value',
                        Mage_ImportExport_Model_Import_Entity_CustomerComposite::COLUMN_ADDRESS_PREFIX . 'attribute2'
                            => 'value'
                    )
                )))),
                '$expectedData' => array(
                    0 => array(
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_WEBSITE => 'website1',
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_EMAIL     => 'email1',
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_ADDRESS_ID => NULL,
                        Mage_ImportExport_Model_Import_Entity_CustomerComposite::COLUMN_DEFAULT_BILLING => 'value',
                        Mage_ImportExport_Model_Import_Entity_CustomerComposite::COLUMN_DEFAULT_SHIPPING => 'value',
                        'customer_attribute1' => 'value',
                        'customer_attribute2' => 'value',
                        Mage_ImportExport_Model_Import_Entity_CustomerComposite::COLUMN_ADDRESS_PREFIX . 'attribute1'
                            => 'value',
                        Mage_ImportExport_Model_Import_Entity_CustomerComposite::COLUMN_ADDRESS_PREFIX . 'attribute2'
                            => 'value'
                    ),
                ),
            ),
            'customer entity address scope' => array(
                '$entityType' => Mage_ImportExport_Model_Import_Entity_CustomerComposite::COMPONENT_ENTITY_CUSTOMER,
                '$bunchData'    => array(array(Zend_Json::encode(array(
                    array(
                        '_scope' => Mage_ImportExport_Model_Import_Entity_CustomerComposite::SCOPE_ADDRESS,
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_WEBSITE => 'website1',
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_EMAIL => 'email1',
                        Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_ADDRESS_ID => null,
                        Mage_ImportExport_Model_Import_Entity_CustomerComposite::COLUMN_DEFAULT_BILLING => 'value',
                        Mage_ImportExport_Model_Import_Entity_CustomerComposite::COLUMN_DEFAULT_SHIPPING => 'value',
                        'customer_attribute1' => 'value',
                        'customer_attribute2' => 'value',
                        Mage_ImportExport_Model_Import_Entity_CustomerComposite::COLUMN_ADDRESS_PREFIX . 'attribute1'
                            => 'value',
                        Mage_ImportExport_Model_Import_Entity_CustomerComposite::COLUMN_ADDRESS_PREFIX . 'attribute2'
                            => 'value'
                    )
                )))),
                '$expectedData' => array(),
            ),
        );
    }
}
