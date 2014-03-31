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
 * @package     Magento_ImportExport
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address
 *
 * @todo Fix tests in the scope of https://wiki.magento.com/display/MAGE2/Technical+Debt+%28Team-Donetsk-B%29
 */
namespace Magento\ImportExport\Model\Import\Entity\Eav\Customer;

class AddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Customer address entity adapter mock
     *
     * @var \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * Websites array (website id => code)
     *
     * @var array
     */
    protected $_websites = array(1 => 'website1', 2 => 'website2');

    /**
     * Attributes array
     *
     * @var array
     */
    protected $_attributes = array(
        'country_id' => array(
            'id' => 1,
            'attribute_code' => 'country_id',
            'table' => '',
            'is_required' => true,
            'is_static' => false,
            'validate_rules' => false,
            'type' => 'select',
            'attribute_options' => null
        )
    );

    /**
     * Customers array
     *
     * @var array
     */
    protected $_customers = array(
        array('id' => 1, 'email' => 'test1@email.com', 'website_id' => 1),
        array('id' => 2, 'email' => 'test2@email.com', 'website_id' => 2)
    );

    /**
     * Customer addresses array
     *
     * @var array
     */
    protected $_addresses = array(1 => array('id' => 1, 'parent_id' => 1));

    /**
     * Customers array
     *
     * @var array
     */
    protected $_regions = array(
        array('id' => 1, 'country_id' => 'c1', 'code' => 'code1', 'default_name' => 'region1'),
        array('id' => 2, 'country_id' => 'c1', 'code' => 'code2', 'default_name' => 'region2')
    );

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
     * Customer behaviours parameters
     *
     * @var array
     */
    protected $_customBehaviour = array('update_id' => 1, 'delete_id' => 2);

    /**
     * @var \Magento\Core\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_coreDataMock;

    /**
     * @var \Magento\Stdlib\String
     */
    protected $_stringLib;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManagerMock;

    /**
     * Init entity adapter model
     */
    protected function setUp()
    {
        $this->markTestSkipped();
        $this->_objectManagerMock = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_coreDataMock = $this->getMock('Magento\Core\Helper\Data', array(), array(), '', false);
        $this->_stringLib = new \Magento\Stdlib\String();
        $this->_model = $this->_getModelMock();
    }

    /**
     * Unset entity adapter model
     */
    protected function tearDown()
    {
        unset($this->_model);
    }

    /**
     * Create mocks for all $this->_model dependencies
     *
     * @return array
     */
    protected function _getModelDependencies()
    {
        $dataSourceModel = $this->getMock('stdClass', array('getNextBunch'));

        $connection = $this->getMock('stdClass');

        $websiteManager = $this->getMock('stdClass', array('getWebsites'));
        $websiteManager->expects(
            $this->once()
        )->method(
            'getWebsites'
        )->will(
            $this->returnCallback(array($this, 'getWebsites'))
        );

        $attributeCollection = $this->_createAttrCollectionMock();

        $customerStorage = $this->_createCustomerStorageMock();

        $customerEntity = $this->_createCustomerEntityMock();

        $addressCollection = new \Magento\Data\Collection(
            $this->getMock('Magento\Core\Model\EntityFactory', array(), array(), '', false)
        );
        foreach ($this->_addresses as $address) {
            $addressCollection->addItem(new \Magento\Object($address));
        }

        $regionCollection = new \Magento\Data\Collection(
            $this->getMock('Magento\Core\Model\EntityFactory', array(), array(), '', false)
        );
        foreach ($this->_regions as $region) {
            $regionCollection->addItem(new \Magento\Object($region));
        }

        $data = array(
            'data_source_model' => $dataSourceModel,
            'connection' => $connection,
            'page_size' => 1,
            'max_data_size' => 1,
            'bunch_size' => 1,
            'attribute_collection' => $attributeCollection,
            'entity_type_id' => 1,
            'customer_storage' => $customerStorage,
            'customer_entity' => $customerEntity,
            'address_collection' => $addressCollection,
            'entity_table' => 'not_used',
            'region_collection' => $regionCollection
        );

        return $data;
    }

    /**
     * Create mock of attribute collection, so it can be used for tests
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Data\Collection
     */
    protected function _createAttrCollectionMock()
    {
        $entityFactory = $this->getMock('Magento\Core\Model\EntityFactory', array(), array(), '', false);
        $attributeCollection = $this->getMock(
            'Magento\Data\Collection',
            array('getEntityTypeCode'),
            array($entityFactory)
        );
        foreach ($this->_attributes as $attributeData) {
            $arguments = $this->_objectManagerMock->getConstructArguments(
                'Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
                array(
                    $this->getMock('Magento\Model\Context', array(), array(), '', false, false),
                    $this->getMock('Magento\Registry'),
                    $this->getMock('Magento\Core\Helper\Data', array(), array(), '', false, false),
                    $this->getMock('Magento\Eav\Model\Config', array(), array(), '', false, false),
                    $this->getMock('Magento\Eav\Model\Entity\TypeFactory'),
                    $this->getMock('Magento\Core\Model\StoreManager', array(), array(), '', false, false),
                    $this->getMock('Magento\Eav\Model\Resource\Helper', array(), array(), '', false, false),
                    $this->getMock('Magento\Validator\UniversalFactory', array(), array(), '', false, false)
                )
            );
            $arguments['data'] = $attributeData;
            $attribute = $this->getMockForAbstractClass(
                'Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
                $arguments,
                '',
                true,
                true,
                true,
                array('_construct', 'getBackend')
            );
            $attribute->expects($this->any())->method('getBackend')->will($this->returnSelf());
            $attribute->expects($this->any())->method('getTable')->will($this->returnValue($attributeData['table']));
            $attributeCollection->addItem($attribute);
        }
        return $attributeCollection;
    }

    /**
     * Create mock of customer storage, so it can be used for tests
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\ImportExport\Model\Resource\Customer\Storage
     */
    protected function _createCustomerStorageMock()
    {
        $customerStorage = $this->getMock(
            'Magento\ImportExport\Model\Resource\Customer\Storage',
            array('load'),
            array(),
            '',
            false
        );
        $resourceMock = $this->getMock(
            'Magento\Customer\Model\Resource\Customer',
            array('getIdFieldName'),
            array(),
            '',
            false
        );
        $resourceMock->expects($this->any())->method('getIdFieldName')->will($this->returnValue('id'));
        foreach ($this->_customers as $customerData) {
            $data = array(
                'resource' => $resourceMock,
                'data' => $customerData,
                $this->getMock('Magento\Customer\Model\Config\Share', array(), array(), '', false),
                $this->getMock('Magento\Customer\Model\AddressFactory', array(), array(), '', false),
                $this->getMock(
                    'Magento\Customer\Model\Resource\Address\CollectionFactory',
                    array(),
                    array(),
                    '',
                    false
                ),
                $this->getMock('Magento\Customer\Model\GroupFactory', array(), array(), '', false),
                $this->getMock('Magento\Customer\Model\AttributeFactory', array(), array(), '', false)
            );
            /** @var $customer \Magento\Customer\Model\Customer */
            $customer = $this->_objectManagerMock->getObject('Magento\Customer\Model\Customer', $data);
            $customerStorage->addCustomer($customer);
        }
        return $customerStorage;
    }

    /**
     * Create simple mock of customer entity, so it can be used for tests
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _createCustomerEntityMock()
    {
        $customerEntity = $this->getMock('stdClass', array('filterEntityCollection', 'setParameters'));
        $customerEntity->expects($this->any())->method('filterEntityCollection')->will($this->returnArgument(0));
        $customerEntity->expects($this->any())->method('setParameters')->will($this->returnSelf());
        return $customerEntity;
    }

    /**
     * Get websites stub
     *
     * @param bool $withDefault
     * @return array
     */
    public function getWebsites($withDefault = false)
    {
        $websites = array();
        if (!$withDefault) {
            unset($websites[0]);
        }
        foreach ($this->_websites as $id => $code) {
            if (!$withDefault && $id == \Magento\Core\Model\Store::DEFAULT_STORE_ID) {
                continue;
            }
            $websiteData = array('id' => $id, 'code' => $code);
            $websites[$id] = new \Magento\Object($websiteData);
        }

        return $websites;
    }

    /**
     * Iterate stub
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param \Magento\Data\Collection $collection
     * @param int $pageSize
     * @param array $callbacks
     */
    public function iterate(\Magento\Data\Collection $collection, $pageSize, array $callbacks)
    {
        foreach ($collection as $customer) {
            foreach ($callbacks as $callback) {
                call_user_func($callback, $customer);
            }
        }
    }

    /**
     * Create mock for custom behavior test
     *
     * @return \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getModelMockForTestImportDataWithCustomBehaviour()
    {
        // input data
        $customBehaviorRows = array(
            array(
                \Magento\ImportExport\Model\Import\AbstractEntity::COLUMN_ACTION => 'update',
                \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address::COLUMN_ADDRESS_ID =>
                    $this->_customBehaviour['update_id']
            ),
            array(
                \Magento\ImportExport\Model\Import\AbstractEntity::COLUMN_ACTION =>
                    \Magento\ImportExport\Model\Import\AbstractEntity::COLUMN_ACTION_VALUE_DELETE,
                \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address::COLUMN_ADDRESS_ID =>
                    $this->_customBehaviour['delete_id']
            )
        );
        $updateResult = array(
            'entity_row' => $this->_customBehaviour['update_id'],
            'attributes' => array(),
            'defaults' => array()
        );

        // entity adapter mock
        $modelMock = $this->getMock(
            'Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address',
            array(
                'validateRow',
                '_prepareDataForUpdate',
                '_saveAddressEntities',
                '_saveAddressAttributes',
                '_saveCustomerDefaults',
                '_deleteAddressEntities',
                '_mergeEntityAttributes'
            ),
            array(),
            '',
            false,
            true,
            true
        );

        $availableBehaviors = new \ReflectionProperty($modelMock, '_availableBehaviors');
        $availableBehaviors->setAccessible(true);
        $availableBehaviors->setValue($modelMock, $this->_availableBehaviors);

        // mock to imitate data source model
        $dataSourceMock = $this->getMock(
            'Magento\ImportExport\Model\Resource\Import\Data',
            array('getNextBunch', '__wakeup'),
            array(),
            '',
            false
        );
        $dataSourceMock->expects($this->at(0))->method('getNextBunch')->will($this->returnValue($customBehaviorRows));
        $dataSourceMock->expects($this->at(1))->method('getNextBunch')->will($this->returnValue(null));

        $dataSourceModel = new \ReflectionProperty(
            'Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address',
            '_dataSourceModel'
        );
        $dataSourceModel->setAccessible(true);
        $dataSourceModel->setValue($modelMock, $dataSourceMock);

        // mock expects for entity adapter
        $modelMock->expects($this->any())->method('validateRow')->will($this->returnValue(true));

        $modelMock->expects($this->any())->method('_prepareDataForUpdate')->will($this->returnValue($updateResult));

        $modelMock->expects(
            $this->any()
        )->method(
            '_saveAddressEntities'
        )->will(
            $this->returnCallback(array($this, 'validateSaveAddressEntities'))
        );

        $modelMock->expects($this->any())->method('_saveAddressAttributes')->will($this->returnValue($modelMock));

        $modelMock->expects($this->any())->method('_saveCustomerDefaults')->will($this->returnValue($modelMock));

        $modelMock->expects(
            $this->any()
        )->method(
            '_deleteAddressEntities'
        )->will(
            $this->returnCallback(array($this, 'validateDeleteAddressEntities'))
        );

        $modelMock->expects($this->any())->method('_mergeEntityAttributes')->will($this->returnValue(array()));

        return $modelMock;
    }

    /**
     * Create mock for customer address model class
     *
     * @return \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getModelMock()
    {
        $coreStoreConfig = $this->getMock('Magento\Core\Model\Store\Config', array(), array(), '', false);
        $storeManager = $this->getMock('\Magento\Core\Model\StoreManager', array('getWebsites'), array(), '', false);
        $storeManager->expects(
            $this->once()
        )->method(
            'getWebsites'
        )->will(
            $this->returnCallback(array($this, 'getWebsites'))
        );

        $modelMock = new \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address(
            $this->_coreDataMock,
            $this->_stringLib,
            $coreStoreConfig,
            $this->getMock('Magento\ImportExport\Model\ImportFactory', array(), array(), '', false),
            $this->getMock('Magento\ImportExport\Model\Resource\Helper', array(), array(), '', false),
            $this->getMock('Magento\App\Resource', array(), array(), '', false),
            $storeManager,
            $this->getMock('Magento\ImportExport\Model\Export\Factory', array(), array(), '', false),
            $this->getMock('Magento\Eav\Model\Config', array(), array(), '', false),
            $this->getMock('Magento\ImportExport\Model\Resource\Customer\StorageFactory', array(), array(), '', false),
            $this->getMock('Magento\Customer\Model\AddressFactory', array(), array(), '', false),
            $this->getMock('Magento\Directory\Model\Resource\Region\CollectionFactory', array(), array(), '', false),
            $this->getMock('Magento\Customer\Model\CustomerFactory', array(), array(), '', false),
            $this->getMock('Magento\Customer\Model\Resource\Address\CollectionFactory', array(), array(), '', false),
            $this->getMock(
                'Magento\Customer\Model\Resource\Address\Attribute\CollectionFactory',
                array(),
                array(),
                '',
                false
            ),
            new \Magento\Stdlib\DateTime(),
            $this->_getModelDependencies()
        );

        $property = new \ReflectionProperty($modelMock, '_availableBehaviors');
        $property->setAccessible(true);
        $property->setValue($modelMock, $this->_availableBehaviors);

        return $modelMock;
    }

    /**
     * Data provider of row data and errors for add/update action
     *
     * @return array
     */
    public function validateRowForUpdateDataProvider()
    {
        return array(
            'valid' => array(
                '$rowData' => include __DIR__ . '/_files/row_data_address_update_valid.php',
                '$errors' => array(),
                '$isValid' => true
            ),
            'empty address id' => array(
                '$rowData' => include __DIR__ . '/_files/row_data_address_update_empty_address_id.php',
                '$errors' => array(),
                '$isValid' => true
            ),
            'no customer' => array(
                '$rowData' => include __DIR__ . '/_files/row_data_address_update_no_customer.php',
                '$errors' => array(
                    \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address::ERROR_CUSTOMER_NOT_FOUND => array(
                        array(1, null)
                    )
                )
            ),
            'absent required attribute' => array(
                '$rowData' => include __DIR__ . '/_files/row_data_address_update_absent_required_attribute.php',
                '$errors' => array(
                    \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address::ERROR_VALUE_IS_REQUIRED => array(
                        array(1, \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address::COLUMN_COUNTRY_ID)
                    )
                )
            ),
            'invalid region' => array(
                '$rowData' => include __DIR__ . '/_files/row_data_address_update_invalid_region.php',
                '$errors' => array(
                    \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address::ERROR_INVALID_REGION => array(
                        array(1, \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address::COLUMN_REGION)
                    )
                )
            )
        );
    }

    /**
     * Data provider of row data and errors for add/update action
     *
     * @return array
     */
    public function validateRowForDeleteDataProvider()
    {
        return array(
            'valid' => array(
                '$rowData' => include __DIR__ . '/_files/row_data_address_update_valid.php',
                '$errors' => array(),
                '$isValid' => true
            ),
            'empty address id' => array(
                '$rowData' => include __DIR__ . '/_files/row_data_address_delete_empty_address_id.php',
                '$errors' => array(
                    \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address::ERROR_ADDRESS_ID_IS_EMPTY => array(
                        array(1, null)
                    )
                )
            ),
            'invalid address' => array(
                '$rowData' => include __DIR__ . '/_files/row_data_address_delete_address_not_found.php',
                '$errors' => array(
                    \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address::ERROR_ADDRESS_NOT_FOUND => array(
                        array(1, null)
                    )
                )
            ),
            'no customer' => array(
                '$rowData' => include __DIR__ . '/_files/row_data_address_delete_no_customer.php',
                '$errors' => array(
                    \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address::ERROR_CUSTOMER_NOT_FOUND => array(
                        array(1, null)
                    )
                )
            )
        );
    }

    /**
     * Test \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address::validateRow() with add/update action
     *
     * @covers \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address::validateRow
     * @covers \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address::_validateRowForUpdate
     * @dataProvider validateRowForUpdateDataProvider
     *
     * @param array $rowData
     * @param array $errors
     * @param boolean $isValid
     */
    public function testValidateRowForUpdate(array $rowData, array $errors, $isValid = false)
    {
        $this->_model->setParameters(array('behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE));

        if ($isValid) {
            $this->assertTrue($this->_model->validateRow($rowData, 0));
        } else {
            $this->assertFalse($this->_model->validateRow($rowData, 0));
        }
        $this->assertAttributeEquals($errors, '_errors', $this->_model);
    }

    /**
     * Test \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address::validateRow()
     * with 2 rows with identical PKs in case when add/update behavior is performed
     *
     * @covers \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address::validateRow
     * @covers \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address::_validateRowForUpdate
     */
    public function testValidateRowForUpdateDuplicateRows()
    {
        $behavior = \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE;

        $this->_model->setParameters(array('behavior' => $behavior));

        $secondRow = $firstRow = array(
            '_website' => 'website1',
            '_email' => 'test1@email.com',
            '_entity_id' => '1',
            'city' => 'Culver City',
            'company' => '',
            'country_id' => 'C1',
            'fax' => '',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'middlename' => '',
            'postcode' => '90232',
            'prefix' => '',
            'region' => 'region1',
            'region_id' => '1',
            'street' => '10441 Jefferson Blvd. Suite 200 Culver City',
            'suffix' => '',
            'telephone' => '12312313',
            'vat_id' => '',
            'vat_is_valid' => '',
            'vat_request_date' => '',
            'vat_request_id' => '',
            'vat_request_success' => '',
            '_address_default_billing_' => '1',
            '_address_default_shipping_' => '1'
        );
        $secondRow['postcode'] = '90210';

        $errors = array(
            \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address::ERROR_DUPLICATE_PK => array(array(2, null))
        );

        $this->assertTrue($this->_model->validateRow($firstRow, 0));
        $this->assertFalse($this->_model->validateRow($secondRow, 1));

        $this->assertAttributeEquals($errors, '_errors', $this->_model);
    }

    /**
     * Test \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address::validateRow() with delete action
     *
     * @covers \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address::validateRow
     * @dataProvider validateRowForDeleteDataProvider
     *
     * @param array $rowData
     * @param array $errors
     * @param boolean $isValid
     */
    public function testValidateRowForDelete(array $rowData, array $errors, $isValid = false)
    {
        $this->_model->setParameters(array('behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE));

        if ($isValid) {
            $this->assertTrue($this->_model->validateRow($rowData, 0));
        } else {
            $this->assertFalse($this->_model->validateRow($rowData, 0));
        }
        $this->assertAttributeEquals($errors, '_errors', $this->_model);
    }

    /**
     * Test entity type code getter
     */
    public function testGetEntityTypeCode()
    {
        $this->assertEquals('customer_address', $this->_model->getEntityTypeCode());
    }

    /**
     * Test default address attribute mapping array
     */
    public function testGetDefaultAddressAttributeMapping()
    {
        $attributeMapping = $this->_model->getDefaultAddressAttributeMapping();
        $this->assertInternalType('array', $attributeMapping, 'Default address attribute mapping must be an array.');
        $this->assertArrayHasKey(
            \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address::COLUMN_DEFAULT_BILLING,
            $attributeMapping,
            'Default address attribute mapping array must have a default billing column.'
        );
        $this->assertArrayHasKey(
            \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address::COLUMN_DEFAULT_SHIPPING,
            $attributeMapping,
            'Default address attribute mapping array must have a default shipping column.'
        );
    }

    /**
     * Test if correct methods are invoked according to different custom behaviours
     *
     * @covers \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address::_importData
     */
    public function testImportDataWithCustomBehaviour()
    {
        $this->_model = $this->_getModelMockForTestImportDataWithCustomBehaviour();
        $this->_model->setParameters(array('behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_CUSTOM));

        // validation in validateSaveAddressEntities and validateDeleteAddressEntities
        $this->_model->importData();
    }

    /**
     * Validation method for _saveAddressEntities (callback for _saveAddressEntities)
     *
     * @param array $addUpdateRows
     * @return \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    public function validateSaveAddressEntities(array $addUpdateRows)
    {
        $this->assertCount(1, $addUpdateRows);
        $this->assertContains($this->_customBehaviour['update_id'], $addUpdateRows);
        return $this->_model;
    }

    /**
     * Validation method for _deleteAddressEntities (callback for _deleteAddressEntities)
     *
     * @param array $deleteRowIds
     * @return \Magento\ImportExport\Model\Import\Entity\Eav\Customer\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    public function validateDeleteAddressEntities(array $deleteRowIds)
    {
        $this->assertCount(1, $deleteRowIds);
        $this->assertContains($this->_customBehaviour['delete_id'], $deleteRowIds);
        return $this->_model;
    }
}
