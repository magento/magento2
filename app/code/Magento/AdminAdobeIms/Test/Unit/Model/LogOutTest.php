<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Test\Unit\Model;

use Magento\AdminAdobeIms\Model\Auth;
use Magento\AdminAdobeIms\Model\ImsConnection;
use Magento\AdminAdobeIms\Model\LogOut;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\AdobeImsApi\Api\ConfigInterface;
use Magento\AdobeImsApi\Api\FlushUserTokensInterface;
use Magento\AdobeImsApi\Api\GetAccessTokenInterface;
use Magento\Backend\Model\Auth\StorageInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test the Adobe Ims log out service
 */
class LogOutTest extends TestCase
{
    private const HTTP_FOUND = 200;
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
     * @var LogOut|MockObject $model
     */
    private $model;
    /**
     * @var ImsConnection|mixed|MockObject
     */
    private $imsConnection;
    /**
     * @var Auth|mixed|MockObject
     */
    private $auth;

    /**
     * @var StorageInterface|mixed|MockObject
     */
    private $session;

    /**
     * @var ImsConfig|mixed|MockObject
     */
    private $config;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->curlFactoryMock = $this->createMock(CurlFactory::class);
        $this->config = $this->createMock(ImsConfig::class);
        $this->loggerInterfaceMock = $this->createMock(LoggerInterface::class);
        $this->imsConnection = $this->createMock(ImsConnection::class);
        $this->auth = $this->createMock(Auth::class);
        $this->session = $this->getMockBuilder(StorageInterface::class)
            ->addMethods(['getAdobeAccessToken'])
            ->getMockForAbstractClass();
        $this->model = new LogOut(
            $this->loggerInterfaceMock,
            $this->config,
            $this->curlFactoryMock,
            $this->imsConnection,
            $this->auth
        );
    }

    /**
     * Test LogOut.
     * @return void
     */
    public function testExecute(): void
    {
        $this->session->expects($this->any())
            ->method('getAdobeAccessToken')
            ->willReturn('access_token');
        $this->auth->expects($this->any())
            ->method('getAuthStorage')
            ->willReturn($this->session);
        $this->imsConnection->expects($this->exactly(2))
            ->method('getProfile')
            ->willReturnOnConsecutiveCalls(['email' => 'test@email.com'], []);

        $curl = $this->createMock(Curl::class);

        $this->curlFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($curl);
        $curl->expects($this->exactly(2))
            ->method('addHeader')
            ->willReturn(null);
        $curl->expects($this->once())
            ->method('post')
            ->willReturn(null);
        $curl->expects($this->any())
            ->method('getStatus')
            ->willReturn(self::HTTP_FOUND);

        $this->assertEquals(true, $this->model->execute('access_token'));
    }

    /**
     * Test LogOut with Error.
     * @return void
     */
    public function testExecuteWithError(): void
    {
        $this->session->expects($this->any())
            ->method('getAdobeAccessToken')
            ->willReturn('access_token');
        $this->auth->expects($this->any())
            ->method('getAuthStorage')
            ->willReturn($this->session);
        $this->imsConnection->expects($this->exactly(1))
            ->method('getProfile')
            ->willReturnOnConsecutiveCalls(['email' => 'test@email.com'], []);

        $curl = $this->createMock(Curl::class);

        $this->curlFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($curl);
        $curl->expects($this->exactly(2))
            ->method('addHeader')
            ->willReturn(null);
        $curl->expects($this->once())
            ->method('post')
            ->willReturn(null);
        $curl->expects($this->any())
            ->method('getStatus')
            ->willReturn(self::HTTP_ERROR);

        $this->assertEquals(false, $this->model->execute('access_token'));
    }
}
