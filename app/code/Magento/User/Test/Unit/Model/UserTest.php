<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Unit\Model;

use Magento\User\Helper\Data as UserHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\User\Model\User;

/**
 * Test class for \Magento\User\Model\User testing
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class UserTest extends \PHPUnit\Framework\TestCase
{
<<<<<<< HEAD
    /** @var User */
    private $model;

    /** @var UserHelper|\PHPUnit_Framework_MockObject_MockObject */
=======
    /** @var \Magento\User\Model\User */
    private $model;

    /** @var \Magento\User\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
>>>>>>> upstream/2.2-develop
    private $userDataMock;

    /**
     * Set required values
     * @return void
     */
    protected function setUp()
    {
<<<<<<< HEAD
        $this->userDataMock = $this->getMockBuilder(UserHelper::class)
=======
        $this->userDataMock = $this->getMockBuilder(\Magento\User\Helper\Data::class)
>>>>>>> upstream/2.2-develop
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

<<<<<<< HEAD
        $objectManagerHelper = new ObjectManager($this);
=======
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
>>>>>>> upstream/2.2-develop
        $this->model = $objectManagerHelper->getObject(
            User::class,
            [
                'userData' => $this->userDataMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testSleep()
    {
        $excludedProperties = [
            '_eventManager',
            '_cacheManager',
            '_registry',
            '_appState',
            '_userData',
            '_config',
            '_validatorObject',
            '_roleFactory',
            '_encryptor',
            '_transportBuilder',
            '_storeManager',
            '_validatorBeforeSave'
        ];
        $actualResult = $this->model->__sleep();
        $this->assertNotEmpty($actualResult);
        $expectedResult = array_intersect($actualResult, $excludedProperties);
        $this->assertEmpty($expectedResult);
    }

    /**
     * @return void
     */
    public function testChangeResetPasswordLinkToken()
    {
        $token = '1';
        $this->assertInstanceOf(
            User::class,
            $this->model->changeResetPasswordLinkToken($token)
        );
        $this->assertEquals($token, $this->model->getRpToken());
        $this->assertInternalType(
            'string',
            $this->model->getRpTokenCreatedAt()
        );
    }

    /**
     * @return void
     */
    public function testIsResetPasswordLinkTokenExpiredEmptyToken()
    {
        $this->assertTrue($this->model->isResetPasswordLinkTokenExpired());
    }

    /**
     * @return void
     */
    public function testIsResetPasswordLinkTokenExpiredIsExpiredToken()
    {
        $this->model->setRpToken('1');
        $this->model->setRpTokenCreatedAt(
            (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
        );
        $this->userDataMock->expects($this->once())->method('getResetPasswordLinkExpirationPeriod')->willReturn(0);
        $this->assertTrue($this->model->isResetPasswordLinkTokenExpired());
    }
<<<<<<< HEAD
=======

    /**
     * @return void
     */
    public function testIsResetPasswordLinkTokenExpiredIsNotExpiredToken()
    {
        $this->model->setRpToken('1');
        $this->model->setRpTokenCreatedAt(
            (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
        );
        $this->userDataMock->expects($this->once())->method('getResetPasswordLinkExpirationPeriod')->willReturn(1);
        $this->assertFalse($this->model->isResetPasswordLinkTokenExpired());
    }
>>>>>>> upstream/2.2-develop
}
