<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerImportExport\Test\Unit\Model\ResourceModel\Import\Customer;

use Magento\CustomerImportExport\Model\ResourceModel\Import\Customer\Storage;

class StorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Storage
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_entityTable = 'test';

    /**
     * @var array
     */
    protected $_expectedFields = ['entity_id', 'website_id', 'email'];

    protected function setUp()
    {
        $this->_model = new \Magento\CustomerImportExport\Model\ResourceModel\Import\Customer\Storage(
            $this->getMockBuilder('Magento\Customer\Model\ResourceModel\Customer\CollectionFactory')
                ->disableOriginalConstructor()
                ->getMock(),
            $this->getMockBuilder('Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory')
                ->disableOriginalConstructor()
                ->getMock(),
            $this->_getModelDependencies()
        );
        $this->_model->load();
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    /**
     * Retrieve all necessary objects mocks which used inside customer storage
     *
     * @return array
     */
    protected function _getModelDependencies()
    {
        $select = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->setMethods(['from'])
            ->getMock();
        $select->expects($this->any())->method('from')->will($this->returnCallback([$this, 'validateFrom']));
        $customerCollection = $this->getMockBuilder('Magento\Customer\Model\ResourceModel\Customer\Collection')
            ->disableOriginalConstructor()
            ->setMethods(['load', 'removeAttributeToSelect', 'getResource', 'getSelect'])
            ->getMock();

        $resourceStub = new \Magento\Framework\DataObject();
        $resourceStub->setEntityTable($this->_entityTable);
        $customerCollection->expects($this->once())->method('getResource')->will($this->returnValue($resourceStub));

        $customerCollection->expects($this->once())->method('getSelect')->will($this->returnValue($select));

        $byPagesIterator = $this->getMock('stdClass', ['iterate']);
        $byPagesIterator->expects($this->once())
            ->method('iterate')
            ->will($this->returnCallback([$this, 'iterate']));

        return [
            'customer_collection' => $customerCollection,
            'collection_by_pages_iterator' => $byPagesIterator,
            'page_size' => 10
        ];
    }

    /**
     * Iterate stub
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @param int $pageSize
     * @param array $callbacks
     */
    public function iterate(\Magento\Framework\Data\Collection $collection, $pageSize, array $callbacks)
    {
        foreach ($collection as $customer) {
            foreach ($callbacks as $callback) {
                call_user_func($callback, $customer);
            }
        }
    }

    /**
     * @param string $tableName
     * @param array $fields
     */
    public function validateFrom($tableName, $fields)
    {
        $this->assertEquals($this->_entityTable, $tableName);
        $this->assertEquals($this->_expectedFields, $fields);
    }

    public function testLoad()
    {
        $this->assertAttributeEquals(true, '_isCollectionLoaded', $this->_model);
    }

    public function testAddCustomer()
    {
        $propertyName = '_customerIds';
        $customer = $this->_addCustomerToStorage();

        $this->assertAttributeCount(1, $propertyName, $this->_model);

        $expectedCustomerData = [$customer->getWebsiteId() => $customer->getId()];
        $this->assertAttributeContains($expectedCustomerData, $propertyName, $this->_model);
    }

    public function testGetCustomerId()
    {
        $customer = $this->_addCustomerToStorage();

        $this->assertEquals(
            $customer->getId(),
            $this->_model->getCustomerId($customer->getEmail(), $customer->getWebsiteId())
        );
        $this->assertFalse($this->_model->getCustomerId('new@test.com', $customer->getWebsiteId()));
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    protected function _addCustomerToStorage()
    {
        $customer = new \Magento\Framework\DataObject(['id' => 1, 'website_id' => 1, 'email' => 'test@test.com']);
        $this->_model->addCustomer($customer);

        return $customer;
    }
}
