<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Model;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Payment\Model\CcConfig;
use Magento\Payment\Model\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CcConfigTest extends TestCase
{
    /**
     * @var CcConfig
     */
    private $model;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var Repository|MockObject
     */
    private $repositoryMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    protected function setUp()
    {
        $this->configMock = $this->createMock(Config::class);
        $this->repositoryMock = $this->createMock(Repository::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->urlMock = $this->createMock(UrlInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->model = new CcConfig(
            $this->configMock,
            $this->repositoryMock,
            $this->requestMock,
            $this->urlMock,
            $this->loggerMock
        );
    }

    public function testGetCcAvailableTypes()
    {
        $data = [1, 2, 3];
        $this->configMock->expects($this->once())
            ->method('getCcTypes')
            ->willReturn($data);

        $this->assertEquals($data, $this->model->getCcAvailableTypes());
    }

    public function testGetCcMonths()
    {
        $data = [1, 2, 3];
        $this->configMock->expects($this->once())
            ->method('getMonths')
            ->willReturn($data);

        $this->assertEquals($data, $this->model->getCcMonths());
    }

    public function testGetCcYears()
    {
        $data = [1, 2, 3];
        $this->configMock->expects($this->once())
            ->method('getYears')
            ->willReturn($data);

        $this->assertEquals($data, $this->model->getCcYears());
    }

    public function testHasVerification()
    {
        $this->assertEquals(true, $this->model->hasVerification());
    }

    public function testGetCvvImageUrl()
    {
        $params = ['_secure' => true];
        $fileId = 'Magento_Checkout::cvv.png';
        $fileUrl = 'file url';

        $this->requestMock->expects($this->once())
            ->method('isSecure')
            ->willReturn(true);

        $this->repositoryMock->expects($this->once())
            ->method('getUrlWithParams')
            ->with($fileId, $params)
            ->willReturn($fileUrl);

        $this->assertEquals($fileUrl, $this->model->getCvvImageUrl());
    }

    public function getViewFileUrlWithException()
    {
        $params = ['a' => 'b'];
        $paramsSecure = ['a' => 'b', '_secure' => false];
        $fileId = 'file id';
        $fileUrl = 'exception url';

        $this->requestMock->expects($this->once())
            ->method('isSecure')
            ->willReturn(false);

        $exception = new LocalizedException('message');

        $this->repositoryMock->expects($this->once())
            ->method('getUrlWithParams')
            ->with($fileId, $paramsSecure)
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception);

        $this->urlMock->expects($this->once())
            ->method('getUrl')
            ->with('', ['_direct' => 'core/index/notFound'])
            ->willReturn($fileUrl);

        $this->assertEquals($fileUrl, $this->model->getViewFileUrl($fileId, $params));
    }
}
