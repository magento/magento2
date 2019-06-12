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
    /** @var User */
    private $model;

    /** @var UserHelper|\PHPUnit_Framework_MockObject_MockObject */
    private $userDataMock;

    /**
     * Set required values
     * @return void
     */
    protected function setUp()
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
}
