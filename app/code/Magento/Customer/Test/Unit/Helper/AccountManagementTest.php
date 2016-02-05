<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class AccountManagementTest
 * @package Magento\Customer\Test\Unit\Helper
 */
class AccountManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var  \Magento\Framework\App\Helper\Context
     */
    protected $contextMock;

    /**
     * Backend configuration interface
     *
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $backendConfigMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTimeMock;

    /**
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    protected $customerRegistryMock;

    /**
     * @var \Magento\Customer\Model\Data\CustomerSecure
     */
    protected $customerSecure;

    /**
     * @var \Magento\Customer\Helper\AccountManagement
     */
    protected $helper;

    protected function setUp()
    {
        $this->contextMock = $this->getMock(
            'Magento\Framework\App\Helper\Context',
            [],
            [],
            '',
            false
        );
        $this->backendConfigMock = $this->getMockBuilder('Magento\Backend\App\ConfigInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMockForAbstractClass();
        $this->dateTimeMock = $this->getMock(
            'Magento\Framework\Stdlib\DateTime',
            [],
            [],
            '',
            false
        );
        $this->customerRegistryMock = $this->getMock(
            'Magento\Customer\Model\CustomerRegistry',
            ['retrieveSecureData'],
            [],
            '',
            false
        );
        $this->customerSecure = $this->getMock(
            'Magento\Customer\Model\Data\CustomerSecure',
            ['getFailuresNum', 'getFirstFailure', 'setFirstFailure', 'setFailuresNum', 'setLockExpires'],
            [],
            '',
            false
        );
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->helper = $objectManagerHelper->getObject(
            'Magento\Customer\Helper\AccountManagement',
            [
                'context' => $this->contextMock,
                'customerRegistry' => $this->customerRegistryMock,
                'backendConfig' => $this->backendConfigMock,
                'dateTime' => $this->dateTimeMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testLockingIsDisabled()
    {
        $customerId = 1;
        $this->backendConfigMock->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(
                [\Magento\Customer\Helper\AccountManagement::LOCKOUT_THRESHOLD_PATH],
                [\Magento\Customer\Helper\AccountManagement::MAX_FAILURES_PATH]
            )
            ->willReturnOnConsecutiveCalls(0, 0);
        $this->customerRegistryMock->expects($this->once())
            ->method('retrieveSecureData')
            ->with($customerId)
            ->willReturn($this->customerSecure);
        $this->helper->processCustomerLockoutData($customerId);
    }

    /**
     * @return void
     */
    public function testCustomerFailedFirstAttempt()
    {
        $customerId = 1;
        $this->backendConfigMock->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(
                [\Magento\Customer\Helper\AccountManagement::LOCKOUT_THRESHOLD_PATH],
                [\Magento\Customer\Helper\AccountManagement::MAX_FAILURES_PATH]
            )
            ->willReturnOnConsecutiveCalls(10, 5);

        $this->customerRegistryMock->expects($this->once())
            ->method('retrieveSecureData')
            ->with($customerId)
            ->willReturn($this->customerSecure);

        $this->customerSecure->expects($this->once())->method('getFailuresNum')->willReturn(0);
        $this->customerSecure->expects($this->once())->method('getFirstFailure')->willReturn(0);
        $this->customerSecure->expects($this->once())->method('setFirstFailure');
        $this->customerSecure->expects($this->once())->method('setFailuresNum');

        $this->helper->processCustomerLockoutData($customerId);
    }

    /**
     * @return void
     */
    public function testCustomerHasFailedMaxNumberOfAttempts()
    {
        $customerId = 1;
        $date = new \DateTime();
        $date->modify('-500 second');
        $formattedDate = $date->format('Y-m-d H:i:s');
        $this->backendConfigMock->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(
                [\Magento\Customer\Helper\AccountManagement::LOCKOUT_THRESHOLD_PATH],
                [\Magento\Customer\Helper\AccountManagement::MAX_FAILURES_PATH]
            )
            ->willReturnOnConsecutiveCalls(10, 5);

        $this->customerRegistryMock->expects($this->once())
            ->method('retrieveSecureData')
            ->with($customerId)
            ->willReturn($this->customerSecure);

        $this->customerSecure->expects($this->once())->method('getFailuresNum')->willReturn(5);
        $this->customerSecure->expects($this->once())
            ->method('getFirstFailure')
            ->willReturn($formattedDate);
        $this->customerSecure->expects($this->once())->method('setLockExpires');
        $this->customerSecure->expects($this->once())->method('setFailuresNum');

        $this->helper->processCustomerLockoutData($customerId);
    }

    /**
     * @return void
     */
    public function testProcessUnlockData()
    {
        $customerId = 1;
        $this->customerRegistryMock->expects($this->once())
            ->method('retrieveSecureData')
            ->with($customerId)
            ->willReturn($this->customerSecure);
        $this->customerSecure->expects($this->once())->method('setFailuresNum')->with(0);
        $this->customerSecure->expects($this->once())->method('setFirstFailure')->with(null);
        $this->customerSecure->expects($this->once())->method('setLockExpires')->with(null);
        $this->helper->processUnlockData($customerId);
    }
}
