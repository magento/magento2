<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\User\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\User\Helper\Data as UserHelper;
use Magento\User\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\User\Model\User testing
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class UserTest extends TestCase
{
    /** @var User */
    private $model;

    /** @var UserHelper|MockObject */
    private $userDataMock;

    /**
     * Set required values
     * @return void
     */
    protected function setUp(): void
    {
        $this->userDataMock = $this->getMockBuilder(UserHelper::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $objectManagerHelper = new ObjectManager($this);
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
        $this->assertIsString($this->model->getRpTokenCreatedAt());
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
}
