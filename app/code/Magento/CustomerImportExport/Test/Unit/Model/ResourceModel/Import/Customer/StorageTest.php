<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerImportExport\Test\Unit\Model\ResourceModel\Import\Customer;

use Magento\CustomerImportExport\Model\ResourceModel\Import\Customer\Storage;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;

class StorageTest extends \PHPUnit\Framework\TestCase
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
        /** @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject $selectMock */
        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->setMethods(['from'])
            ->getMock();
        $selectMock->expects($this->any())->method('from')->will($this->returnSelf());

        /** @var $connectionMock AdapterInterface|\PHPUnit_Framework_MockObject_MockObject */
        $connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\Pdo\Mysql::class)
            ->disableOriginalConstructor()
            ->setMethods(['select', 'fetchAll'])
            ->getMock();
        $connectionMock->expects($this->any())
            ->method('select')
            ->will($this->returnValue($selectMock));
        $connectionMock->expects($this->any())
            ->method('fetchAll')
            ->will($this->returnValue([]));

        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $customerCollection */
        $customerCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConnection','getMainTable'])
            ->getMock();
        $customerCollection->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($connectionMock));

        $customerCollection->expects($this->any())
            ->method('getMainTable')
            ->willReturn('customer_entity');

        /** @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject $collectionFactory */
        $collectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $collectionFactory->expects($this->any())
            ->method('create')
            ->willReturn($customerCollection);

        /** @var CollectionByPagesIteratorFactory|\PHPUnit_Framework_MockObject_MockObject $byPagesIteratorFactory */
        $byPagesIteratorFactory = $this->getMockBuilder(CollectionByPagesIteratorFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->_model = new Storage(
            $collectionFactory,
            $byPagesIteratorFactory
        );
        $this->_model->load();
    }

    protected function tearDown()
    {
        unset($this->_model);
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
        $customer = new \Magento\Framework\DataObject(['id' => 1, 'website_id' => 1, 'email' => 'test@test.com']);
        $this->_model->addCustomer($customer);

        $propertyName = '_customerIds';
        $this->assertAttributeCount(1, $propertyName, $this->_model);
        $this->assertAttributeContains([$customer->getWebsiteId() => $customer->getId()], $propertyName, $this->_model);
        $this->assertEquals(
            $customer->getId(),
            $this->_model->getCustomerId($customer->getEmail(), $customer->getWebsiteId())
        );
    }

    public function testAddCustomerByArray()
    {
        $propertyName = '_customerIds';
        $customer = $this->_addCustomerToStorage();

        $this->assertAttributeCount(1, $propertyName, $this->_model);
        $expectedCustomerData = [$customer['website_id'] => $customer['entity_id']];
        $this->assertAttributeContains($expectedCustomerData, $propertyName, $this->_model);
    }

    public function testGetCustomerId()
    {
        $customer = $this->_addCustomerToStorage();

        $this->assertEquals(
            $customer['entity_id'],
            $this->_model->getCustomerId($customer['email'], $customer['website_id'])
        );
        $this->assertFalse($this->_model->getCustomerId('new@test.com', $customer['website_id']));
    }

    /**
     * @return array
     */
    protected function _addCustomerToStorage()
    {
        $customer = ['entity_id' => 1, 'website_id' => 1, 'email' => 'test@test.com'];
        $this->_model->addCustomerByArray($customer);

        return $customer;
    }
}
