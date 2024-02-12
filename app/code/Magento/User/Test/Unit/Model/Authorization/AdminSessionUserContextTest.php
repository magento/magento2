<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\User\Test\Unit\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\User\Model\Authorization\AdminSessionUserContext;
use PHPUnit\Framework\TestCase;

/**
 * Tests Magento\User\Model\Authorization\AdminSessionUserContext
 */
class AdminSessionUserContextTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var AdminSessionUserContext
     */
    protected $adminSessionUserContext;

    /**
     * @var Session
     */
    protected $adminSession;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->adminSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->addMethods(['hasUser', 'getUser', 'getId'])
            ->getMock();

        $this->adminSessionUserContext = $this->objectManager->getObject(
            AdminSessionUserContext::class,
            ['adminSession' => $this->adminSession]
        );
    }

    public function testGetUserIdExist()
    {
        $userId = 1;

        $this->setupUserId($userId);

        $this->assertEquals($userId, $this->adminSessionUserContext->getUserId());
    }

    public function testGetUserIdDoesNotExist()
    {
        $userId = null;

        $this->setupUserId($userId);

        $this->assertEquals($userId, $this->adminSessionUserContext->getUserId());
    }

    public function testGetUserType()
    {
        $this->assertEquals(UserContextInterface::USER_TYPE_ADMIN, $this->adminSessionUserContext->getUserType());
    }

    /**
     * @param int|null $userId
     * @return void
     */
    public function setupUserId($userId)
    {
        $this->adminSession->expects($this->once())
            ->method('hasUser')
            ->willReturn($userId);

        if ($userId) {
            $this->adminSession->expects($this->once())
                ->method('getUser')
                ->willReturnSelf();

            $this->adminSession->expects($this->once())
                ->method('getId')
                ->willReturn($userId);
        }
    }
}
