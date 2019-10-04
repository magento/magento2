<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Model\PasswordManagement;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PasswordManagementTest extends \PHPUnit\Framework\TestCase
{
    /** @var PasswordManagement */
    private $passwordManagement;

    /** @var ObjectManagerHelper */
    private $objectManagerHelper;

    /** @var \Magento\Customer\Model\Customer|\PHPUnit_Framework_MockObject_MockObject */
    private $customer;

    /** @var \Magento\Customer\Model\CustomerRegistry|\PHPUnit_Framework_MockObject_MockObject */
    private $customerRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Customer\Model\Data\CustomerSecure
     */
    private $customerSecure;

    /**
     * @var DateTimeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeFactory;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->customer = $this->createMock(\Magento\Customer\Model\Customer::class);
        $this->customerRegistry = $this->createMock(\Magento\Customer\Model\CustomerRegistry::class);
        $this->customerSecure = $this->getMockBuilder(\Magento\Customer\Model\Data\CustomerSecure::class)
            ->setMethods(['setRpToken', 'addData', 'setRpTokenCreatedAt', 'setData'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTimeFactory = $this->createMock(DateTimeFactory::class);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->passwordManagement = $this->objectManagerHelper->getObject(
            \Magento\Customer\Model\PasswordManagement::class,
            [
                'customerRegistry' => $this->customerRegistry,
                'customerModel' => $this->customer,
                'dateTimeFactory' => $this->dateTimeFactory
            ]
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage "resetPasswordLinkToken" is required. Enter and try again.
     */
    public function testValidateResetPasswordByTokenEmptyResetPasswordLinkToken()
    {
        $this->passwordManagement->validateResetPasswordLinkByToken('');
    }

    /**
     * @expectedException \Magento\Framework\Exception\State\InputMismatchException
     * @expectedExceptionMessage The password token is mismatched. Reset and try again.
     */
    public function testValidateResetPasswordByTokenTokenMismatch()
    {
        $this->customerRegistry->expects($this->atLeastOnce())
            ->method('retrieveSecureData')
            ->willReturn($this->customerSecure);

        $this->passwordManagement->validateResetPasswordLinkByToken('newStringToken');
    }

    /**
     * @expectedException \Magento\Framework\Exception\State\ExpiredException
     * @expectedExceptionMessage The password token is expired. Reset and try again.
     */
    public function testValidateResetPasswordByTokenTokenExpired()
    {
        $this->reInitModel();
        $this->customerRegistry->expects($this->atLeastOnce())
            ->method('retrieveSecureData')
            ->willReturn($this->customerSecure);

        $this->passwordManagement->validateResetPasswordLinkByToken('newStringToken');
    }

    /**
     * return bool
     */
    public function testValidateResetPasswordByToken()
    {
        $this->reInitModel();

        $this->customer
            ->expects($this->once())
            ->method('getResetPasswordLinkExpirationPeriod')
            ->willReturn(100000);

        $this->customerRegistry->expects($this->atLeastOnce())
            ->method('retrieveSecureData')
            ->willReturn($this->customerSecure);

        $this->assertTrue($this->passwordManagement->validateResetPasswordLinkByToken('newStringToken'));
    }

    /**
     * reInit $this->passwordManagement object
     */
    private function reInitModel()
    {
        $this->customerSecure = $this->getMockBuilder(\Magento\Customer\Model\Data\CustomerSecure::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getRpToken',
                    'getRpTokenCreatedAt',
                ]
            )
            ->getMock();
        $this->customerSecure->expects($this->any())
            ->method('getRpToken')
            ->willReturn('newStringToken');
        $pastDateTime = '2016-10-25 00:00:00';
        $this->customerSecure->expects($this->any())
            ->method('getRpTokenCreatedAt')
            ->willReturn($pastDateTime);

        $this->prepareDateTimeFactory();

        $dateTime = '2017-10-25 18:57:08';
        $timestamp = '1508983028';
        $dateTimeMock = $this->getMockBuilder(\DateTime::class)
            ->disableOriginalConstructor()
            ->setMethods(['format', 'getTimestamp', 'setTimestamp'])
            ->getMock();

        $dateTimeMock->expects($this->any())
            ->method('format')
            ->with(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
            ->willReturn($dateTime);
        $dateTimeMock->expects($this->any())
            ->method('getTimestamp')
            ->willReturn($timestamp);
        $dateTimeMock->expects($this->any())
            ->method('setTimestamp')
            ->willReturnSelf();
        $dateTimeFactory = $this->getMockBuilder(DateTimeFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $dateTimeFactory->expects($this->any())->method('create')->willReturn($dateTimeMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->passwordManagement = $this->objectManagerHelper->getObject(
            PasswordManagement::class,
            [
                'customerRegistry' => $this->customerRegistry,
                'customerModel' => $this->customer,
                'dateTimeFactory' => $dateTimeFactory
            ]
        );
    }

    /**
     * @return string
     */
    private function prepareDateTimeFactory()
    {
        $dateTime = '2017-10-25 18:57:08';
        $timestamp = '1508983028';
        $dateTimeMock = $this->createMock(\DateTime::class);
        $dateTimeMock->expects($this->any())
            ->method('format')
            ->with(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
            ->willReturn($dateTime);

        $dateTimeMock
            ->expects($this->any())
            ->method('getTimestamp')
            ->willReturn($timestamp);

        $this->dateTimeFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($dateTimeMock);

        return $dateTime;
    }
}
