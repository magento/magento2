<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerImportExport\Test\Unit\Model\ResourceModel\Import\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\CustomerImportExport\Model\ResourceModel\Import\Customer\Storage;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;

class StorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Storage
     */
    private $_model;

    /**
     * @var CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryMock;

    protected function setUp()
    {
        $this->_model = new Storage(
            $this->getMockBuilder(CollectionFactory::class)
                ->disableOriginalConstructor()
                ->getMock(),
            $this->getMockBuilder(CollectionByPagesIteratorFactory::class)
                ->disableOriginalConstructor()
                ->getMock(),
            [],
            $this->customerRepositoryMock = $this->getMockBuilder(CustomerRepositoryInterface::class)
                ->disableOriginalConstructor()
                ->getMock()
        );
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    public function testGetCustomerId()
    {
        $existingEmail = 'test@magento.com';
        $existingWebsiteId = 0;
        $existingId = 1;
        $nonExistingEmail = 'test1@magento.com';
        $nonExistingWebsiteId = 2;

        /** @var \PHPUnit_Framework_MockObject_MockObject $customerMock */
        $customerMock = new DataObject([
            'id' => $existingId,
            'email' => $existingEmail,
            'website_id' => $existingWebsiteId
        ]);
        $this->customerRepositoryMock->expects($this->at(0))
            ->method('get')
            ->with($existingEmail, $existingWebsiteId)
            ->willReturn($customerMock);
        $this->customerRepositoryMock->expects($this->at(1))
            ->method('get')
            ->with($nonExistingEmail, $nonExistingWebsiteId)
            ->willThrowException(new NoSuchEntityException());

        $this->assertEquals(
            $existingId,
            $this->_model->getCustomerId($existingEmail, $existingWebsiteId)
        );
        $this->assertFalse(
            $this->_model->getCustomerId(
                $nonExistingEmail,
                $nonExistingWebsiteId
            )
        );
        //Checking one more time to see whether the repo will be used once
        //again.
        $this->assertEquals(
            $existingId,
            $this->_model->getCustomerId($existingEmail, $existingWebsiteId)
        );
        $this->assertFalse(
            $this->_model->getCustomerId(
                $nonExistingEmail,
                $nonExistingWebsiteId
            )
        );
    }
}
