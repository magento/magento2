<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model;

use Magento\Backend\App\ConfigInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Authentication;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Data\CustomerSecure;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AuthenticationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\App\ConfigInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $backendConfigMock;

    /**
     * @var \Magento\Customer\Model\CustomerRegistry | \PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRegistryMock;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $encryptorMock;

    /**
     * @var CustomerRepositoryInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var \Magento\Customer\Model\Data\CustomerSecure | \PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSecureMock;

    /**
     * @var \Magento\Customer\Model\Authentication
     */
    private $authentication;

    /**
     * @var DateTime
     */
    private $dateTimeMock;

    /**
     * @var \Magento\Customer\Model\CustomerAuthUpdate | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerAuthUpdate;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManagerHelper($this);

        $this->backendConfigMock = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMockForAbstractClass();
        $this->customerRegistryMock = $this->getMock(
            CustomerRegistry::class,
            ['retrieveSecureData', 'retrieve'],
            [],
            '',
            false
        );
        $this->customerRepositoryMock = $this->getMockBuilder(CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->encryptorMock = $this->getMockBuilder(\Magento\Framework\Encryption\EncryptorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTimeMock = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTimeMock->expects($this->any())
            ->method('formatDate')
            ->willReturn('formattedDate');
        $this->customerSecureMock = $this->getMock(
            CustomerSecure::class,
            [
                'getId',
                'getPasswordHash',
                'isCustomerLocked',
                'getFailuresNum',
                'getFirstFailure',
                'getLockExpires',
                'setFirstFailure',
                'setFailuresNum',
                'setLockExpires'
            ],
            [],
            '',
            false
        );

        $this->customerAuthUpdate = $this->getMockBuilder(\Magento\Customer\Model\CustomerAuthUpdate::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->authentication = $this->objectManager->getObject(
            Authentication::class,
            [
                'customerRegistry' => $this->customerRegistryMock,
                'backendConfig' => $this->backendConfigMock,
                'customerRepository' => $this->customerRepositoryMock,
                'encryptor' => $this->encryptorMock,
                'dateTime' => $this->dateTimeMock,
            ]
        );

        $this->objectManager->setBackwardCompatibleProperty(
            $this->authentication,
            'customerAuthUpdate',
            $this->customerAuthUpdate
        );
    }

    public function testProcessAuthenticationFailureLockingIsDisabled()
    {
        $customerId = 1;
        $this->backendConfigMock->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(
                [\Magento\Customer\Model\Authentication::LOCKOUT_THRESHOLD_PATH],
                [\Magento\Customer\Model\Authentication::MAX_FAILURES_PATH]
            )
            ->willReturnOnConsecutiveCalls(0, 0);
        $this->customerRegistryMock->expects($this->once())
            ->method('retrieveSecureData')
            ->with($customerId)
            ->willReturn($this->customerSecureMock);
        $this->authentication->processAuthenticationFailure($customerId);
    }

    /**
     * @param int $failureNum
     * @param string $firstFailure
     * @param string $lockExpires
     * @param int $setFailureNumCallCtr
     * @param int $setFailureNumValue
     * @param int $setFirstFailureCallCtr
     * @param int $setFirstFailureValue
     * @param int $setLockExpiresCallCtr
     * @param int $setLockExpiresValue
     * @dataProvider processAuthenticationFailureDataProvider
     */
    public function testProcessAuthenticationFailureFirstAttempt(
        $failureNum,
        $firstFailure,
        $lockExpires,
        $setFailureNumCallCtr,
        $setFailureNumValue,
        $setFirstFailureCallCtr,
        $setLockExpiresCallCtr,
        $setLockExpiresValue
    ) {
        $customerId = 1;
        $this->backendConfigMock->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(
                [\Magento\Customer\Model\Authentication::LOCKOUT_THRESHOLD_PATH],
                [\Magento\Customer\Model\Authentication::MAX_FAILURES_PATH]
            )
            ->willReturnOnConsecutiveCalls(10, 5);

        $this->customerRegistryMock->expects($this->once())
            ->method('retrieveSecureData')
            ->with($customerId)
            ->willReturn($this->customerSecureMock);
        $this->customerAuthUpdate->expects($this->once())
            ->method('saveAuth')
            ->with($customerId)
            ->willReturnSelf();

        $this->customerSecureMock->expects($this->once())->method('getFailuresNum')->willReturn($failureNum);
        $this->customerSecureMock->expects($this->once())
            ->method('getFirstFailure')
            ->willReturn($firstFailure ? (new \DateTime())->modify($firstFailure)->format('Y-m-d H:i:s') : null);
        $this->customerSecureMock->expects($this->once())
            ->method('getLockExpires')
            ->willReturn($lockExpires ? (new \DateTime())->modify($lockExpires)->format('Y-m-d H:i:s') : null);
        $this->customerSecureMock->expects($this->exactly($setFirstFailureCallCtr))->method('setFirstFailure');
        $this->customerSecureMock->expects($this->exactly($setFailureNumCallCtr))
            ->method('setFailuresNum')
            ->with($setFailureNumValue);
        $this->customerSecureMock->expects($this->exactly($setLockExpiresCallCtr))
            ->method('setLockExpires')
            ->with($setLockExpiresValue);

        $this->authentication->processAuthenticationFailure($customerId);
    }

    public function processAuthenticationFailureDataProvider()
    {
        return [
            'first attempt' => [0, null, null, 1, 1, 1, 1, null],
            'not locked' => [3, '-400 second', null, 1, 4, 0, 0, null],
            'lock expired' => [5, '-400 second', '-100 second', 1, 1, 1, 1, null],
            'max attempt' => [4, '-400 second', null, 1, 5, 0, 1, 'formattedDate'],
        ];
    }

    public function testUnlock()
    {
        $customerId = 1;
        $this->customerRegistryMock->expects($this->once())
            ->method('retrieveSecureData')
            ->with($customerId)
            ->willReturn($this->customerSecureMock);
        $this->customerAuthUpdate->expects($this->once())
            ->method('saveAuth')
            ->with($customerId)
            ->willReturnSelf();
        $this->customerSecureMock->expects($this->once())->method('setFailuresNum')->with(0);
        $this->customerSecureMock->expects($this->once())->method('setFirstFailure')->with(null);
        $this->customerSecureMock->expects($this->once())->method('setLockExpires')->with(null);
        $this->authentication->unlock($customerId);
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
    public function testIsLocked()
    {
        $customerId = 7;

        $customerModelMock = $this->getMockBuilder(\Magento\Customer\Model\Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerModelMock->expects($this->once())
            ->method('isCustomerLocked');
        $this->customerRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with($customerId)
            ->willReturn($customerModelMock);

        $this->authentication->isLocked($customerId);
    }

    /**
     * @param bool $result
     * @dataProvider validateCustomerPassword
     */
    public function testAuthenticate($result)
    {
        $customerId = 7;
        $password = '1234567';
        $hash = '1b2af329dd0';

        $customerMock = $this->getMock(
            \Magento\Customer\Api\Data\CustomerInterface::class,
            [],
            [],
            '',
            false
        );
        $this->customerRepositoryMock->expects($this->any())
            ->method('getById')
            ->willReturn($customerMock);

        $this->customerSecureMock->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);

        $this->customerSecureMock->expects($this->once())
            ->method('getPasswordHash')
            ->willReturn($hash);

        $this->customerRegistryMock->expects($this->any())
            ->method('retrieveSecureData')
            ->with($customerId)
            ->willReturn($this->customerSecureMock);

        $this->encryptorMock->expects($this->once())
            ->method('validateHash')
            ->with($password, $hash)
            ->willReturn($result);

        if ($result) {
            $this->assertTrue($this->authentication->authenticate($customerId, $password));
        } else {
            $this->backendConfigMock->expects($this->exactly(2))
                ->method('getValue')
                ->withConsecutive(
                    [\Magento\Customer\Model\Authentication::LOCKOUT_THRESHOLD_PATH],
                    [\Magento\Customer\Model\Authentication::MAX_FAILURES_PATH]
                )
                ->willReturnOnConsecutiveCalls(1, 1);
            $this->customerSecureMock->expects($this->once())
                ->method('isCustomerLocked')
                ->willReturn(false);

            $this->customerRegistryMock->expects($this->once())
                ->method('retrieve')
                ->with($customerId)
                ->willReturn($this->customerSecureMock);

            $this->customerAuthUpdate->expects($this->once())
                ->method('saveAuth')
                ->with($customerId)
                ->willReturnSelf();

            $this->setExpectedException(\Magento\Framework\Exception\InvalidEmailOrPasswordException::class);
            $this->authentication->authenticate($customerId, $password);
        }
    }

    /**
     * @return array
     */
    public function validateCustomerPassword()
    {
        return [
            [true],
            [false],
        ];
    }
}
