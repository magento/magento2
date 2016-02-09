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
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptorMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfigMock;

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
            ['retrieveSecureData', 'retrieve'],
            [],
            '',
            false
        );
        $this->encryptorMock = $this->getMock(
            'Magento\Framework\Encryption\EncryptorInterface',
            [],
            [],
            '',
            false
        );
        $this->eventManagerMock = $this->getMock(
            'Magento\Framework\Event\ManagerInterface',
            [],
            [],
            '',
            false
        );
        $this->scopeConfigMock =  $this->getMock(
            '\Magento\Framework\App\Config\ScopeConfigInterface',
            ['getValue', 'isSetFlag'],
            [],
            '',
            false
        );
        $this->customerSecure = $this->getMock(
            'Magento\Customer\Model\Data\CustomerSecure',
            [
                'getId',
                'getPasswordHash',
                'isCustomerLocked',
                'getFailuresNum',
                'getFirstFailure',
                'setFirstFailure',
                'setFailuresNum',
                'setLockExpires'
            ],
            [],
            '',
            false
        );
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->helper = $objectManagerHelper->getObject(
            'Magento\Customer\Helper\AccountManagement',
            [
                'customerRegistry' => $this->customerRegistryMock,
                'backendConfig' => $this->backendConfigMock,
                'dateTime' => $this->dateTimeMock,
                'encryptor' => $this->encryptorMock,
                'eventManager' => $this->eventManagerMock,
                'scopeConfig' => $this->scopeConfigMock
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

    /**
     * @param bool $result
     * @dataProvider validatePasswordAndLockStatusDataProvider
     */
    public function testValidatePasswordAndLockStatus($result)
    {
        $customerId = 7;
        $password = '1234567';
        $hash = '1b2af329dd0';
        $email = 'test@example.com';

        $customerMock = $this->getMock(
            'Magento\Customer\Api\Data\CustomerInterface',
            [],
            [],
            '',
            false
        );
        $customerMock->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);

        $this->customerSecure->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);

        $this->customerSecure->expects($this->once())
            ->method('getPasswordHash')
            ->willReturn($hash);

        $this->customerRegistryMock->expects($this->once())
            ->method('retrieveSecureData')
            ->with($customerId)
            ->willReturn($this->customerSecure);

        $this->encryptorMock->expects($this->once())
            ->method('validateHash')
            ->with($password, $hash)
            ->willReturn($result);

        if ($result) {
            $this->assertEquals($this->helper, $this->helper->validatePasswordAndLockStatus($customerMock, $password));
        } else {
            $customerMock->expects($this->once())
                ->method('getEmail')
                ->willReturn($email);

            $this->eventManagerMock->expects($this->once())
                ->method('dispatch')
                ->with(
                    'customer_password_invalid',
                    [
                        'username' => $email,
                        'password' => $password
                    ]
                );

            $this->customerSecure->expects($this->once())
                ->method('isCustomerLocked')
                ->willReturn(false);

            $this->customerRegistryMock->expects($this->once())
                ->method('retrieve')
                ->with($customerId)
                ->willReturn($this->customerSecure);

            $this->setExpectedException(
                '\Magento\Framework\Exception\InvalidEmailOrPasswordException',
                __('The password doesn\'t match this account.')
            );
            $this->helper->validatePasswordAndLockStatus($customerMock, $password);
        }
    }

    /**
     * @return array
     */
    public function validatePasswordAndLockStatusDataProvider()
    {
        return [[true], [false]];
    }

    /**
     * @return void
     */
    public function testCheckIfLocked()
    {
        $customerId = 7;
        $email = 'test@example.com';

        $customerMock = $this->getMock(
            'Magento\Customer\Api\Data\CustomerInterface',
            [],
            [],
            '',
            false
        );
        $customerMock->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);

        $this->customerSecure->expects($this->once())
            ->method('isCustomerLocked')
            ->willReturn(true);

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('contact/email/recipient_email')
            ->willReturn($email);

        $this->customerRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with($customerId)
            ->willReturn($this->customerSecure);

        $this->setExpectedException(
            '\Magento\Framework\Exception\State\UserLockedException',
            __('The account is locked. Please wait and try again or contact %1.', $email)
        );

        $this->helper->checkIfLocked($customerMock);
    }
}
