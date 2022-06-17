<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Test\Unit\Controller\Adminhtml\OAuth;

use Magento\AdobeIms\Controller\Adminhtml\OAuth\Callback;
use Magento\AdobeIms\Model\GetImage;
use Magento\AdobeImsApi\Api\Data\UserProfileInterface;
use Magento\AdobeImsApi\Api\Data\UserProfileInterfaceFactory;
use Magento\AdobeImsApi\Api\UserProfileRepositoryInterface;
use Magento\AdobeImsApi\Api\Data\TokenResponseInterface;
use Magento\AdobeImsApi\Api\GetTokenInterface;
use Magento\AdobeImsApi\Api\LogInInterface;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\User\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Authentication callback controller test
 */
class CallbackTest extends TestCase
{
    /**
     * @var MockObject|Context
     */
    private $context;

    /**
     * @var MockObject|GetTokenInterface
     */
    private $getToken;

    /**
     * @var Auth|MockObject
     */
    private $authMock;

    /**
     * @var User|MockObject
     */
    private $user;

    /**
     * @var ResultFactory|MockObject
     */
    private $resultFactory;

    /**
     * @var LogInInterface|MockObject
     */
    private $login;

    /**
     * @var Callback
     */
    private $callback;

    /**
     * Prepare test objects.
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->authMock = $this->createMock(Auth::class);
        $this->resultFactory = $this->createMock(ResultFactory::class);
        $this->context = $objectManager->getObject(
            Context::class,
            [
                'auth' => $this->authMock,
                'resultFactory' => $this->resultFactory
            ]
        );
        $this->user = $this->createMock(User::class);
        $this->getToken = $this->createMock(GetTokenInterface::class);
        $this->login = $this->createMock(LogInInterface::class);
        $this->callback = $objectManager->getObject(
            Callback::class,
            [
                'context' => $this->context,
                'getToken' => $this->getToken,
                'login' => $this->login
            ]
        );
    }

    /**
     * Authentication callback controller test
     */
    public function testExecute(): void
    {
        $userId = 55;
        $token = $this->createMock(TokenResponseInterface::class);

        $this->authMock->method('getUser')
            ->will($this->returnValue($this->user));
        $this->user->method('getId')
            ->willReturn($userId);

        $this->getToken->expects($this->once())
            ->method('execute')
            ->willReturn($token);
        $this->login->expects($this->once())
            ->method('execute')
            ->with($userId, $token);

        $result = $this->createMock(Raw::class);
        $result->expects($this->once())
            ->method('setContents')
            ->with('auth[code=success;message=Authorization was successful]')
            ->willReturnSelf();
        $this->resultFactory->expects($this->once())
            ->method('create')
            ->willReturn($result);

        $this->assertEquals($result, $this->callback->execute());
    }
}
