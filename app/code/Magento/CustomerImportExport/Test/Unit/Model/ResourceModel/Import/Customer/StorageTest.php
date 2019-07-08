<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\CustomerImportExport\Model\ResourceModel\Import\Customer\Storage
 */
namespace Magento\CustomerImportExport\Test\Unit\Model\ResourceModel\Import\Customer;

use Magento\CustomerImportExport\Model\ResourceModel\Import\Customer\Storage;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Select;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIterator;

class StorageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Storage
     */
    private $model;

    /**
     * @var CollectionByPagesIterator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $iteratorMock;

    /**
     * @var Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionMock;

    protected function setUp()
    {
        $this->iteratorMock = $this->createMock(
            CollectionByPagesIterator::class
        );
        /** @var \PHPUnit_Framework_MockObject_MockObject|CollectionByPagesIteratorFactory $iteratorFactoryMock */
        $iteratorFactoryMock = $this->createMock(
            CollectionByPagesIteratorFactory::class
        );
        $iteratorFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->iteratorMock);
        $this->collectionMock = $this->createMock(Collection::class);
        /** @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject $collectionFactoryMock */
        $collectionFactoryMock = $this->createMock(
            CollectionFactory::class
        );
        $collectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->collectionMock);
        /** @var \PHPUnit_Framework_MockObject_MockObject $selectMock */
        $selectMock = $this->createMock(Select::class);
        $selectMock->expects($this->any())
            ->method('getPart')
            ->with(Select::FROM)
            ->willReturn(['e' => []]);
        $this->collectionMock->expects($this->any())
            ->method('getSelect')
            ->willReturn($selectMock);

        $this->model = new Storage(
            $collectionFactoryMock,
            $iteratorFactoryMock,
            []
        );
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    public function testAddCustomerByArray()
    {
        $propertyName = '_customerIds';
        $customer = $this->_addCustomerToStorage();

        $this->assertAttributeCount(1, $propertyName, $this->model);
        $expectedCustomerData = [$customer['website_id'] => $customer['entity_id']];
        $this->assertAttributeContains($expectedCustomerData, $propertyName, $this->model);
    }

    public function testGetCustomerId()
    {
        $existingEmail = 'test@magento.com';
        $existingWebsiteId = 0;
        $existingId = 1;
        $nonExistingEmail = 'test1@magento.com';
        $nonExistingWebsiteId = 2;

        $this->iteratorMock->expects($this->at(0))
            ->method('iterate')
            ->willReturnCallback(
                function (...$args) use (
                    $existingId,
                    $existingEmail,
                    $existingWebsiteId
                ) {
                    /** @var callable $callable */
                    foreach ($args[2] as $callable) {
                        $callable(
                            new DataObject([
                                'id' => $existingId,
                                'email' => $existingEmail,
                                'website_id' => $existingWebsiteId,
                            ])
                        );
                    }
                }
            );
        $this->assertEquals(
            $existingId,
            $this->model->getCustomerId($existingEmail, $existingWebsiteId)
        );
        $this->assertFalse(
            $this->model->getCustomerId(
                $nonExistingEmail,
                $nonExistingWebsiteId
            )
        );
    }

    /**
     * @return array
     */
    protected function _addCustomerToStorage()
    {
        $customer = ['entity_id' => 1, 'website_id' => 1, 'email' => 'test@test.com'];
        $this->model->addCustomerByArray($customer);

        return $customer;
    }
}
