<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Model\CustomerAuthUpdate;

/**
 * Class CustomerAuthUpdateTest
 */
class CustomerAuthUpdateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CustomerAuthUpdate
     */
    protected $model;

    /**
     * @var \Magento\Customer\Model\CustomerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRegistry;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerResourceModel;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * Setup the test
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->customerRegistry =
            $this->getMock(\Magento\Customer\Model\CustomerRegistry::class, [], [], '', false);
        $this->customerResourceModel =
            $this->getMock(\Magento\Customer\Model\ResourceModel\Customer::class, [], [], '', false);

        $this->model = $this->objectManager->getObject(
            \Magento\Customer\Model\CustomerAuthUpdate::class,
            [
                'customerRegistry' => $this->customerRegistry,
                'customerResourceModel' => $this->customerResourceModel,
            ]
        );
    }

    /**
     * test SaveAuth
     */
    public function testSaveAuth()
    {
        $customerId = 1;

        $customerSecureMock = $this->getMock(
            \Magento\Customer\Model\Data\CustomerSecure::class,
            [],
            [],
            '',
            false
        );

        $dbAdapter = $this->getMock(
            \Magento\Framework\DB\Adapter\AdapterInterface::class,
            [],
            [],
            '',
            false
        );

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

        $this->model->saveAuth($customerId);
    }
}
