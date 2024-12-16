<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\CustomerAuthUpdate;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Data\CustomerSecure;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResourceModel;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerAuthUpdateTest extends TestCase
{
    /**
     * @var CustomerAuthUpdate
     */
    protected $model;

    /**
     * @var CustomerRegistry|MockObject
     */
    protected $customerRegistry;

    /**
     * @var CustomerResourceModel|MockObject
     */
    protected $customerResourceModel;

    /**
     * @var CustomerFactory|MockObject
     */
    protected $customerFactory;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Setup the test
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->customerRegistry =
            $this->createMock(CustomerRegistry::class);
        $this->customerResourceModel =
            $this->createMock(CustomerResourceModel::class);
        $this->customerFactory =
            $this->createMock(CustomerFactory::class);

        $this->model = $this->objectManager->getObject(
            CustomerAuthUpdate::class,
            [
                'customerRegistry' => $this->customerRegistry,
                'customerResourceModel' => $this->customerResourceModel,
                'customerFactory' => $this->customerFactory
            ]
        );
    }

    /**
     * test SaveAuth
     * @throws NoSuchEntityException
     */
    public function testSaveAuth()
    {
        $customerId = 1;

        $customerSecureMock = $this->createMock(CustomerSecure::class);

        $dbAdapter = $this->getMockForAbstractClass(AdapterInterface::class);

        $this->customerRegistry->expects($this->once())
            ->method('retrieveSecureData')
            ->willReturn($customerSecureMock);

        $customerSecureMock->expects($this->exactly(3))
            ->method('getData')
            ->willReturn(1);

        $this->customerResourceModel->expects($this->any())
            ->method('getConnection')
            ->willReturn($dbAdapter);

        $this->customerResourceModel->expects($this->any())
            ->method('getTable')
            ->willReturn('customer_entity');

        $dbAdapter->expects($this->any())
            ->method('update')
            ->with(
                'customer_entity',
                [
                    'failures_num' => 1,
                    'first_failure' => 1,
                    'lock_expires' => 1
                ]
            );

        $dbAdapter->expects($this->any())
            ->method('quoteInto')
            ->with(
                'entity_id = ?',
                $customerId
            );

        $customerModel = $this->createMock(CustomerModel::class);
        $customerModel->expects($this->once())
            ->method('reindex');

        $this->customerFactory->expects($this->once())
            ->method('create')
            ->willReturn($customerModel);

        $this->model->saveAuth($customerId);
    }
}
