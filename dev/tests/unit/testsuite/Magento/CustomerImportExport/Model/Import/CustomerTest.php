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
 * Test class for \Magento\CustomerImportExport\Model\Import\Customer
 */
namespace Magento\CustomerImportExport\Model\Import;

class CustomerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Customer entity import model
     *
     * @var Customer|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * Available behaviours
     *
     * @var array
     */
    protected $_availableBehaviors = array(
        \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
        \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
        \Magento\ImportExport\Model\Import::BEHAVIOR_CUSTOM
    );

    /**
     * Custom behavior input rows
     *
     * @var array
     */
    protected $_inputRows = array(
        'create' => array(
            Customer::COLUMN_ACTION => 'create',
            Customer::COLUMN_EMAIL => 'create@email.com',
            Customer::COLUMN_WEBSITE => 'website1'
        ),
        'update' => array(
            Customer::COLUMN_ACTION => 'update',
            Customer::COLUMN_EMAIL => 'update@email.com',
            Customer::COLUMN_WEBSITE => 'website1'
        ),
        'delete' => array(
            Customer::COLUMN_ACTION => Customer::COLUMN_ACTION_VALUE_DELETE,
            Customer::COLUMN_EMAIL => 'delete@email.com',
            Customer::COLUMN_WEBSITE => 'website1'
        )
    );

    /**
     * Customer ids for all custom behavior input rows
     *
     * @var array
     */
    protected $_customerIds = array('create' => 1, 'update' => 2, 'delete' => 3);

    /**
     * Unset entity adapter model
     */
    protected function tearDown()
    {
        unset($this->_model);

        parent::tearDown();
    }

    /**
     * Create mock for import with custom behavior test
     *
     * @return Customer|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getModelMockForTestImportDataWithCustomBehaviour()
    {
        // entity adapter mock
        $modelMock = $this->getMockBuilder('Magento\CustomerImportExport\Model\Import\Customer')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'validateRow',
                    '_getCustomerId',
                    '_prepareDataForUpdate',
                    '_saveCustomerEntities',
                    '_saveCustomerAttributes',
                    '_deleteCustomerEntities'
                ])
            ->getMock();

        $availableBehaviors = new \ReflectionProperty($modelMock, '_availableBehaviors');
        $availableBehaviors->setAccessible(true);
        $availableBehaviors->setValue($modelMock, $this->_availableBehaviors);

        // mock to imitate data source model
        $dataSourceModelMock = $this->getMockBuilder('Magento\ImportExport\Model\Resource\Import\Data')
            ->disableOriginalConstructor()
            ->setMethods([
                    'getNextBunch',
                    '__wakeup'
                ])
            ->getMock();

        $dataSourceModelMock->expects($this->at(0))
            ->method('getNextBunch')
            ->will($this->returnValue($this->_inputRows));
        $dataSourceModelMock->expects($this->at(1))
            ->method('getNextBunch')
            ->will($this->returnValue(null));

        $property = new \ReflectionProperty(
            'Magento\CustomerImportExport\Model\Import\Customer',
            '_dataSourceModel'
        );
        $property->setAccessible(true);
        $property->setValue($modelMock, $dataSourceModelMock);

        $modelMock->expects($this->any())
            ->method('validateRow')
            ->will($this->returnValue(true));

        $modelMock->expects($this->any())
            ->method('_getCustomerId')
            ->will($this->returnValue($this->_customerIds['delete']));

        $modelMock->expects($this->any())
            ->method('_prepareDataForUpdate')
            ->will($this->returnCallback(array($this, 'prepareForUpdateMock')));

        $modelMock->expects($this->any())
            ->method('_saveCustomerEntities')
            ->will($this->returnCallback(array($this, 'validateSaveCustomerEntities')));

        $modelMock->expects($this->any())
            ->method('_saveCustomerAttributes')
            ->will($this->returnValue($modelMock));

        $modelMock->expects($this->any())
            ->method('_deleteCustomerEntities')
            ->will($this->returnCallback(array($this, 'validateDeleteCustomerEntities')));

        return $modelMock;
    }

    /**
     * Test whether correct methods are invoked in case of custom behaviour for each row in action column
     */
    public function testImportDataWithCustomBehaviour()
    {
        $this->_model = $this->_getModelMockForTestImportDataWithCustomBehaviour();
        $this->_model->setParameters(['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_CUSTOM]);

        // validation in validateSaveCustomerEntities and validateDeleteCustomerEntities
        $this->_model->importData();
    }

    /**
     * Emulate data preparing depending on value in row action column
     *
     * @param array $rowData
     * @return int
     */
    public function prepareForUpdateMock(array $rowData)
    {
        $preparedResult = array(
            Customer::ENTITIES_TO_CREATE_KEY => array(),
            Customer::ENTITIES_TO_UPDATE_KEY => array(),
            Customer::ATTRIBUTES_TO_SAVE_KEY => array('table' => array())
        );

        $actionColumnKey = Customer::COLUMN_ACTION;
        if ($rowData[$actionColumnKey] == 'create') {
            $preparedResult[Customer::ENTITIES_TO_CREATE_KEY] = [
                ['entity_id' => $this->_customerIds['create']]
            ];
        } elseif ($rowData[$actionColumnKey] == 'update') {
            $preparedResult[Customer::ENTITIES_TO_UPDATE_KEY] = [
                ['entity_id' => $this->_customerIds['update']]
            ];
        }

        return $preparedResult;
    }

    /**
     * Validation method for _saveCustomerEntities
     *
     * @param array $entitiesToCreate
     * @param array $entitiesToUpdate
     * @return Customer|PHPUnit_Framework_MockObject_MockObject
     */
    public function validateSaveCustomerEntities(array $entitiesToCreate, array $entitiesToUpdate)
    {
        $this->assertCount(1, $entitiesToCreate);
        $this->assertEquals($this->_customerIds['create'], $entitiesToCreate[0]['entity_id']);
        $this->assertCount(1, $entitiesToUpdate);
        $this->assertEquals($this->_customerIds['update'], $entitiesToUpdate[0]['entity_id']);
        return $this->_model;
    }

    /**
     * Validation method for _deleteCustomerEntities
     *
     * @param array $customerIdsToDelete
     * @return Customer|PHPUnit_Framework_MockObject_MockObject
     */
    public function validateDeleteCustomerEntities(array $customerIdsToDelete)
    {
        $this->assertCount(1, $customerIdsToDelete);
        $this->assertEquals($this->_customerIds['delete'], $customerIdsToDelete[0]);
        return $this->_model;
    }
}
