<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Model;

use Magento\Framework\Exception\LocalizedException;

class CcConfigTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Payment\Model\CcConfig */
    protected $model;

    /** @var \Magento\Payment\Model\Config|\PHPUnit\Framework\MockObject\MockObject */
    protected $configMock;

    /** @var \Magento\Framework\View\Asset\Repository|\PHPUnit\Framework\MockObject\MockObject */
    protected $repositoryMock;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $requestMock;

    /** @var \Magento\Framework\UrlInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $urlMock;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $loggerMock;

    protected function setUp(): void
    {
        $this->configMock = $this->createMock(\Magento\Payment\Model\Config::class);
        $this->repositoryMock = $this->createMock(\Magento\Framework\View\Asset\Repository::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->urlMock = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);

        $this->model = new \Magento\Payment\Model\CcConfig(
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
        $this->assertTrue($this->model->hasVerification());
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
