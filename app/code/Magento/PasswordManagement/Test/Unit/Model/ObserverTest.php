<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PasswordManagement\Test\Unit\Model;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Encryption\Encryptor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $encryptorMock;

    /**
     * @var \Magento\Customer\Model\Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerMock;

    /**
     * @var \Magento\PasswordManagement\Model\Observer
     */
    protected $observer;

    protected function setUp()
    {
        $this->encryptorMock = $this->getMockBuilder(
            '\Magento\Framework\Encryption\Encryptor'
        )->disableOriginalConstructor()->getMock();
        $this->encryptorMock->expects($this->any())->method('isValidHashByVersion')->will(
            $this->returnCallback(
                function ($arg1, $arg2) {
                    return $arg1 == $arg2;
                }
            )
        );
        $this->observer = new \Magento\PasswordManagement\Model\Observer($this->encryptorMock);
        $this->customerMock = $this->getMockBuilder(
            '\Magento\Customer\Model\Customer'
        )->disableOriginalConstructor()->setMethods(
            ['getPasswordHash', 'changePassword', '__wakeup']
        )->getMock();
    }

    /**
     * Create Observer with custom data structure and fill password
     *
     * @param $password
     * @param $passwordHash
     * @return \Magento\Framework\DataObject
     */
    protected function getObserverMock($password, $passwordHash)
    {
        $this->customerMock->expects(
            $this->once()
        )->method(
            'getPasswordHash'
        )->will(
            $this->returnValue($passwordHash)
        );

        $event = new \Magento\Framework\DataObject();
        $event->setData(['password' => $password, 'model' => $this->customerMock]);

        $observerMock = new \Magento\Framework\DataObject();
        $observerMock->setData('event', $event);

        return $observerMock;
    }

    /**
     * Test successfully password change if new password doesn't match old one
     */
    public function testUpgradeCustomerPassword()
    {
        $this->customerMock->expects($this->once())->method('changePassword')->will($this->returnSelf());
        $this->observer->upgradeCustomerPassword($this->getObserverMock('different password', 'old password'));
    }

    /**
     * Test failure password change if new password matches old one
     */
    public function testUpgradeCustomerPasswordNotChanged()
    {
        $this->customerMock->expects($this->never())->method('changePassword');
        $this->observer->upgradeCustomerPassword($this->getObserverMock('same password', 'same password'));
    }
}
