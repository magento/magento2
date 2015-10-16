<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Unit\Observer\Backend;

/**
 * Test class for \Magento\User\Observer\Backend\CheckAdminPasswordChangeObserver
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckAdminPasswordChangeObserverTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\User\Model\ResourceModel\User|\PHPUnit_Framework_MockObject_MockObject */
    protected $userMock;

    /** @var \Magento\Framework\Encryption\EncryptorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $encryptorMock;

    /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $eventManagerMock;

    /** @var \Magento\User\Observer\Backend\CheckAdminPasswordChangeObserver */
    protected $model;

    public function setUp()
    {
        $this->userMock = $this->getMockBuilder('Magento\User\Model\ResourceModel\User')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->encryptorMock = $this->getMockBuilder('\Magento\Framework\Encryption\EncryptorInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->eventManagerMock = $this->getMockBuilder('Magento\Framework\Event\ManagerInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->model = $helper->getObject(
            '\Magento\User\Observer\Backend\CheckAdminPasswordChangeObserver',
            [
                'userResource' => $this->userMock,
                'encryptor' => $this->encryptorMock,
            ]
        );
    }

    public function testCheckAdminPasswordChange()
    {
        $newPW = "mYn3wpassw0rd";
        $uid = 123;
        /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject $eventObserverMock */
        $eventObserverMock = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        /** @var \Magento\Framework\Event|\PHPUnit_Framework_MockObject_MockObject */
        $eventMock = $this->getMockBuilder('Magento\Framework\Event')
            ->disableOriginalConstructor()
            ->setMethods(['getObject'])
            ->getMock();

        /** @var \Magento\User\Model\User|\PHPUnit_Framework_MockObject_MockObject $userMock */
        $userMock = $this->getMockBuilder('Magento\User\Model\User')
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getNewPassword', 'getForceNewPassword'])
            ->getMock();

        $eventObserverMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getObject')->willReturn($userMock);
        $userMock->expects($this->atLeastOnce())->method('getNewPassword')->willReturn($newPW);
        $userMock->expects($this->once())->method('getForceNewPassword')->willReturn(false);
        $userMock->expects($this->once())->method('getId')->willReturn($uid);
        $this->encryptorMock->expects($this->once())->method('isValidHash')->willReturn(false);
        $this->encryptorMock->expects($this->once())->method('getHash')->willReturn(md5($newPW));
        $this->userMock->method('getOldPasswords')->willReturn([md5('pw1'), md5('pw2')]);

        $this->model->execute($eventObserverMock);
    }

    public function testCheckAdminPasswordChangeThrowsLocalizedExp()
    {
        $newPW = "mYn3wpassw0rd";
        $uid = 123;
        /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject $eventObserverMock */
        $eventObserverMock = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        /** @var \Magento\Framework\Event|\PHPUnit_Framework_MockObject_MockObject */
        $eventMock = $this->getMockBuilder('Magento\Framework\Event')
            ->disableOriginalConstructor()
            ->setMethods(['getObject'])
            ->getMock();

        /** @var \Magento\User\Model\User|\PHPUnit_Framework_MockObject_MockObject $userMock */
        $userMock = $this->getMockBuilder('Magento\User\Model\User')
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getNewPassword', 'getForceNewPassword'])
            ->getMock();

        $eventObserverMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getObject')->willReturn($userMock);
        $userMock->expects($this->atLeastOnce())->method('getNewPassword')->willReturn($newPW);
        $userMock->expects($this->once())->method('getForceNewPassword')->willReturn(false);
        $userMock->expects($this->once())->method('getId')->willReturn($uid);
        $this->encryptorMock->expects($this->once())->method('isValidHash')->willReturn(true);
        $this->userMock->method('getOldPasswords')->willReturn([md5('pw1'), md5('pw2')]);

        try {
            $this->model->execute($eventObserverMock);
        } catch (\Magento\Framework\Exception\LocalizedException $expected) {
            return;
        }
        $this->fail('An expected exception has not been raised.');
    }
}
