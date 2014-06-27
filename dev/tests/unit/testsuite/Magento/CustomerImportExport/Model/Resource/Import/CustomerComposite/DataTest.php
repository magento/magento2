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

/**
 * Test class for \Magento\CustomerImportExport\Model\Resource\Import\CustomerComposite\Data
 */
namespace Magento\CustomerImportExport\Model\Resource\Import\CustomerComposite;

use Magento\CustomerImportExport\Model\Import\Address;
use Magento\CustomerImportExport\Model\Import\CustomerComposite;

class DataTest extends \PHPUnit_Framework_TestCase
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
        /** @var $statementMock \Magento\Framework\DB\Statement\Pdo\Mysql */
        $statementMock = $this->getMock(
            'Magento\Framework\DB\Statement\Pdo\Mysql',
            array('setFetchMode', 'getIterator'),
            array(),
            '',
            false
        );
        $statementMock->expects(
            $this->any()
        )->method(
            'getIterator'
        )->will(
            $this->returnValue(new \ArrayIterator($bunchData))
        );

        /** @var $selectMock \Magento\Framework\DB\Select */
        $selectMock = $this->getMock('Magento\Framework\DB\Select', array('from', 'order'), array(), '', false);
        $selectMock->expects($this->any())->method('from')->will($this->returnSelf());
        $selectMock->expects($this->any())->method('order')->will($this->returnSelf());

        /** @var $adapterMock \Magento\Framework\DB\Adapter\Pdo\Mysql */
        $adapterMock = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            array('select', 'from', 'order', 'query'),
            array(),
            '',
            false
        );
        $adapterMock->expects($this->any())->method('select')->will($this->returnValue($selectMock));
        $adapterMock->expects($this->any())->method('query')->will($this->returnValue($statementMock));

        /** @var $resourceModelMock \Magento\Framework\App\Resource */
        $resourceModelMock = $this->getMock(
            'Magento\Framework\App\Resource',
            array('getConnection', '_newConnection', 'getTableName'),
            array(),
            '',
            false
        );
        $resourceModelMock->expects($this->any())->method('getConnection')->will($this->returnValue($adapterMock));

        $data = array('resource' => $resourceModelMock, 'entity_type' => $entityType);

        if ($entityType == CustomerComposite::COMPONENT_ENTITY_ADDRESS) {
            $data['customer_attributes'] = $this->_customerAttributes;
        }

        return $data;
    }

    /**
     * @covers \Magento\CustomerImportExport\Model\Resource\Import\CustomerComposite\Data::getNextBunch
     * @covers \Magento\CustomerImportExport\Model\Resource\Import\CustomerComposite\Data::_prepareRow
     * @covers \Magento\CustomerImportExport\Model\Resource\Import\CustomerComposite\Data::_prepareAddressRowData
     *
     * @dataProvider getNextBunchDataProvider
     * @param string $entityType
     * @param array $bunchData
     * @param array $expectedData
     */
    public function testGetNextBunch($entityType, $bunchData, $expectedData)
    {
        $dependencies = $this->_getDependencies($entityType, $bunchData);

        $resource = $dependencies['resource'];
        $coreHelper = $this->getMock('Magento\Core\Helper\Data', array('__construct'), array(), '', false);
        unset($dependencies['resource'], $dependencies['json_helper']);

        $object = new Data(
            $resource,
            $coreHelper,
            $dependencies
        );
        $this->assertEquals($expectedData, $object->getNextBunch());
    }

    /**
     * Data provider of row data and expected result of getNextBunch() method
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getNextBunchDataProvider()
    {
        return array(
            'address entity' => array(
                '$entityType' => CustomerComposite::COMPONENT_ENTITY_ADDRESS,
                '$bunchData' => array(
                    array(
                        \Zend_Json::encode(
                            array(
                                array(
                                    '_scope' => CustomerComposite::SCOPE_DEFAULT,
                                    Address::COLUMN_WEBSITE =>'website1',
                                    Address::COLUMN_EMAIL => 'email1',
                                    Address::COLUMN_ADDRESS_ID => null,
                                    CustomerComposite::COLUMN_DEFAULT_BILLING => 'value',
                                    CustomerComposite::COLUMN_DEFAULT_SHIPPING => 'value',
                                    'customer_attribute1' => 'value',
                                    'customer_attribute2' => 'value',
                                    CustomerComposite::COLUMN_ADDRESS_PREFIX . 'attribute1' => 'value',
                                    CustomerComposite::COLUMN_ADDRESS_PREFIX . 'attribute2' => 'value'
                                )
                            )
                        )
                    )
                ),
                '$expectedData' => array(
                    0 => array(
                        Address::COLUMN_WEBSITE => 'website1',
                        Address::COLUMN_EMAIL => 'email1',
                        Address::COLUMN_ADDRESS_ID => null,
                        CustomerComposite::COLUMN_DEFAULT_BILLING => 'value',
                        CustomerComposite::COLUMN_DEFAULT_SHIPPING => 'value',
                        'attribute1' => 'value',
                        'attribute2' => 'value'
                    )
                )
            ),
            'customer entity default scope' => array(
                '$entityType' => CustomerComposite::COMPONENT_ENTITY_CUSTOMER,
                '$bunchData' => array(
                    array(
                        \Zend_Json::encode(
                            array(
                                array(
                                    '_scope' => CustomerComposite::SCOPE_DEFAULT,
                                    Address::COLUMN_WEBSITE => 'website1',
                                    Address::COLUMN_EMAIL => 'email1',
                                    Address::COLUMN_ADDRESS_ID => null,
                                    CustomerComposite::COLUMN_DEFAULT_BILLING => 'value',
                                    CustomerComposite::COLUMN_DEFAULT_SHIPPING => 'value',
                                    'customer_attribute1' => 'value',
                                    'customer_attribute2' => 'value',
                                    CustomerComposite::COLUMN_ADDRESS_PREFIX . 'attribute1' => 'value',
                                    CustomerComposite::COLUMN_ADDRESS_PREFIX . 'attribute2' => 'value'
                                )
                            )
                        )
                    )
                ),
                '$expectedData' => array(
                    0 => array(
                        Address::COLUMN_WEBSITE => 'website1',
                        Address::COLUMN_EMAIL => 'email1',
                        Address::COLUMN_ADDRESS_ID => null,
                        CustomerComposite::COLUMN_DEFAULT_BILLING => 'value',
                        CustomerComposite::COLUMN_DEFAULT_SHIPPING => 'value',
                        'customer_attribute1' => 'value',
                        'customer_attribute2' => 'value',
                        CustomerComposite::COLUMN_ADDRESS_PREFIX . 'attribute1' => 'value',
                        CustomerComposite::COLUMN_ADDRESS_PREFIX . 'attribute2' => 'value'
                    )
                )
            ),
            'customer entity address scope' => array(
                '$entityType' => CustomerComposite::COMPONENT_ENTITY_CUSTOMER,
                '$bunchData' => array(
                    array(
                        \Zend_Json::encode(
                            array(
                                array(
                                    '_scope' => CustomerComposite::SCOPE_ADDRESS,
                                    Address::COLUMN_WEBSITE => 'website1',
                                    Address::COLUMN_EMAIL => 'email1',
                                    Address::COLUMN_ADDRESS_ID => null,
                                    CustomerComposite::COLUMN_DEFAULT_BILLING => 'value',
                                    CustomerComposite::COLUMN_DEFAULT_SHIPPING => 'value',
                                    'customer_attribute1' => 'value',
                                    'customer_attribute2' => 'value',
                                    CustomerComposite::COLUMN_ADDRESS_PREFIX . 'attribute1' => 'value',
                                    CustomerComposite::COLUMN_ADDRESS_PREFIX . 'attribute2' => 'value'
                                )
                            )
                        )
                    )
                ),
                '$expectedData' => array()
            )
        );
    }
}
