<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Test\Unit\Model;

use Exception;
use Magento\AdobeIms\Model\GetProfile;
use Magento\AdobeIms\Model\LogOut;
use Magento\Backend\Model\Auth\StorageInterface;
use Magento\AdobeImsApi\Api\ConfigInterface;
use Magento\AdobeImsApi\Api\FlushUserTokensInterface;
use Magento\AdobeImsApi\Api\GetAccessTokenInterface;
use Magento\Backend\Model\Auth;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test the Adobe Stock log out service
 */
class LogOutTest extends TestCase
{
    private const HTTP_FOUND = 302;
    private const HTTP_ERROR = 500;

    /**
     * @var CurlFactory|MockObject
     */
    private $curlFactoryMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerInterfaceMock;

    /**
     * @var ConfigInterface|MockObject
     */
    private $configInterfaceMock;

    /**
     * @var GetAccessTokenInterface|MockObject
     */
    private $getToken;

    /**
     * @var FlushUserTokensInterface|MockObject
     */
    private $flushTokens;

    /**
     * @var LogOut|MockObject $model
     */
    private $model;

    /**
     * @var Auth|MockObject
     */
    private $auth;

    /**
     * @var GetProfile|MockObject
     */
    private $profile;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->curlFactoryMock = $this->createMock(CurlFactory::class);
        $this->configInterfaceMock = $this->createMock(ConfigInterface::class);
        $this->loggerInterfaceMock = $this->createMock(LoggerInterface::class);
        $this->getToken = $this->createMock(GetAccessTokenInterface::class);
        $this->flushTokens = $this->createMock(FlushUserTokensInterface::class);
        $this->profile = $this->createMock(GetProfile::class);
        $this->auth = $this->createMock(Auth::class);
        $this->model = new LogOut(
            $this->loggerInterfaceMock,
            $this->configInterfaceMock,
            $this->curlFactoryMock,
            $this->getToken,
            $this->flushTokens,
            $this->profile,
            $this->auth
        );
    }

    /**
     * Test LogOut.
     */
    public function testExecute(): void
    {
        $this->getToken->expects($this->once())
            ->method('execute')
            ->willReturn('token');

        $curl = $this->createMock(Curl::class);
        $this->curlFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($curl);
        $curl->expects($this->exactly(2))
            ->method('addHeader')
            ->willReturn(null);
        $curl->expects($this->once())
            ->method('get')
            ->willReturnSelf();
        $curl->expects($this->once())
            ->method('getStatus')
            ->willReturn(self::HTTP_FOUND);

        $this->flushTokens->expects($this->once())
            ->method('execute');
        $session = $this->getMockBuilder(StorageInterface::class)
            ->addMethods(['getAdobeAccessToken'])
            ->getMockForAbstractClass();
        $session->expects($this->once())
            ->method('getAdobeAccessToken')
            ->willReturn(null);
        $this->auth->expects($this->once())
            ->method('getAuthStorage')
            ->willReturn($session);
        $this->assertEquals(true, $this->model->execute());
    }

    /**
     * Test LogOut with Error.
     */
    public function testExecuteWithError(): void
    {
        $this->getToken->expects($this->once())
            ->method('execute')
            ->willReturn('token');

        $curl = $this->createMock(Curl::class);
        $this->curlFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($curl);
        $curl->expects($this->exactly(2))
            ->method('addHeader')
            ->willReturn(null);
        $curl->expects($this->once())
            ->method('get')
            ->willReturnSelf();
        $curl->expects($this->once())
            ->method('getStatus')
            ->willReturn(self::HTTP_ERROR);
        $this->loggerInterfaceMock->expects($this->once())
             ->method('critical');

        $this->flushTokens->expects($this->never())
            ->method('execute');
        $session = $this->getMockBuilder(StorageInterface::class)
            ->addMethods(['getAdobeAccessToken'])
            ->getMockForAbstractClass();
        $session->expects($this->once())
            ->method('getAdobeAccessToken')
            ->willReturn(null);
        $this->auth->expects($this->once())
            ->method('getAuthStorage')
            ->willReturn($session);
        $this->assertEquals(false, $this->model->execute());
    }

    /**
     * Test LogOut with Exception.
     */
    public function testExecuteWithException(): void
    {
        $this->getToken->expects($this->once())
            ->method('execute')
            ->willReturn('token');

        $curl = $this->createMock(Curl::class);
        $this->curlFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($curl);
        $curl->expects($this->exactly(2))
            ->method('addHeader')
            ->willReturn(null);
        $curl->expects($this->once())
            ->method('get')
            ->willReturnSelf();
        $curl->expects($this->once())
            ->method('getStatus')
            ->willReturn(self::HTTP_FOUND);
        $session = $this->getMockBuilder(StorageInterface::class)
            ->addMethods(['getAdobeAccessToken'])
            ->getMockForAbstractClass();
        $session->expects($this->once())
            ->method('getAdobeAccessToken')
            ->willReturn(null);
        $this->auth->expects($this->once())
            ->method('getAuthStorage')
            ->willReturn($session);
        $this->flushTokens->expects($this->once())
            ->method('execute')
            ->willThrowException(new Exception('Could not save user profile.'));
        $this->loggerInterfaceMock->expects($this->once())
            ->method('critical');
        $this->assertFalse($this->model->execute());
    }
}
