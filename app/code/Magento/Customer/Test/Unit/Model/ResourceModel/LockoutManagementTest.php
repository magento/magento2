<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

class LockoutManagementTest extends \PHPUnit_Framework_TestCase
{

    /** @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject */
    protected $resourceMock;

    /** @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $dbAdapterMock;

    /** @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject */
    protected $resource;

    /** @var \Magento\Framework\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject */
    protected $dateTimeMock;

    /** @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $eavConfigMock;

    /** @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $backendConfigMock;

    /** @var \Magento\Customer\Model\ResourceModel\LockoutManagement */
    protected $resourceModel;

    /**
     * Init mocks for tests
     * @return void
     */
    protected function setUp()
    {
        $this->resourceMock = $this->getMock(
            '\Magento\Framework\App\ResourceConnection',
            [],
            [],
            '',
            false
        );

        $this->dbAdapterMock = $this->getMock(
            '\Magento\Framework\DB\Adapter\AdapterInterface',
            [],
            [],
            '',
            false
        );

        $this->dateTimeMock = $this->getMock(
            '\Magento\Framework\Stdlib\DateTime',
            [],
            [],
            '',
            false
        );

        $this->eavConfigMock = $this->getMock(
            '\Magento\Eav\Model\Config',
            [],
            [],
            '',
            false
        );

        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->dbAdapterMock);

        $entityTypeMock = $this->getMock(
            '\Magento\Framework\DataObject',
            [],
            [],
            '',
            false
        );
        $this->eavConfigMock->expects($this->any())
            ->method('getEntityType')
            ->willReturn($entityTypeMock);

        $this->backendConfigMock = $this->getMock(
            '\Magento\Backend\App\ConfigInterface',
            [],
            [],
            '',
            false
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->resourceModel = $objectManager->getObject(
            '\Magento\Customer\Model\ResourceModel\LockoutManagement',
            [
                'resource' => $this->resourceMock,
                'dateTime' => $this->dateTimeMock,
                'eavConfig' => $this->eavConfigMock,
                'backendConfig' => $this->backendConfigMock
            ]
        );
        $this->resourceModel->setType('customer');
    }

    /**
     * @return void
     */
    public function testProcessLockout()
    {
        $customerId = 7;
        $failuresNum = 5;
        $firstFailureDate = '2016-01-01';
        $lockExpiresDate = '2016-01-01';

        $customerMock = $this->getMock(
            '\Magento\Customer\Model\Customer',
            ['getId', 'getFailuresNum', 'getFirstFailure'],
            [],
            '',
            false
        );

        $customerMock->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);
        $customerMock->expects($this->once())
            ->method('getFailuresNum')
            ->willReturn($failuresNum);
        $customerMock->expects($this->once())
            ->method('getFirstFailure')
            ->willReturn($firstFailureDate);

        $this->backendConfigMock->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(['customer/password/lockout_threshold'], ['customer/password/lockout_failures'])
            ->willReturn(1);

        $this->dbAdapterMock->expects($this->once())
            ->method('quoteInto')
            ->with($this->resourceModel->getIdFieldName() . ' = ?')
            ->willReturn($this->resourceModel->getIdFieldName() . ' = ' . $customerId);

        $this->dateTimeMock->expects($this->once())
            ->method('formatDate')
            ->willReturn($lockExpiresDate);

        $update = ['failures_num' => new \Zend_Db_Expr('failures_num + 1')];
        $update['lock_expires'] = $lockExpiresDate;

        $this->dbAdapterMock->expects($this->once())
            ->method('update')
            ->with(
                $this->resourceModel->getTable('customer_entity'),
                $update,
                $this->resourceModel->getIdFieldName() . ' = ' . $customerId
            )
            ->willReturnSelf();

        $this->resourceModel->processLockout($customerMock);
    }

    /**
     * @return void
     */
    public function testUnlock()
    {
        $customerId = 7;

        $this->dbAdapterMock->expects($this->once())
            ->method('quote')
            ->with($customerId)
            ->willReturn($customerId);

        $this->dbAdapterMock->expects($this->once())
            ->method('update')
            ->with(
                $this->resourceModel->getTable('customer_entity'),
                ['failures_num' => 0, 'first_failure' => null, 'lock_expires' => null],
                $this->resourceModel->getIdFieldName() . ' = (' . $customerId . ')'
            )
            ->willReturnSelf();

        $this->assertEquals($this->resourceModel, $this->resourceModel->unlock($customerId));
    }
}
