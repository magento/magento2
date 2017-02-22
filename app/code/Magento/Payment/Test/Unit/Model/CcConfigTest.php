<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Model;

use Magento\Framework\Exception\LocalizedException;

class CcConfigTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Payment\Model\CcConfig */
    protected $model;

    /** @var \Magento\Payment\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $configMock;

    /** @var \Magento\Framework\View\Asset\Repository|\PHPUnit_Framework_MockObject_MockObject */
    protected $repositoryMock;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $requestMock;

    /** @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlMock;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $loggerMock;

    protected function setUp()
    {
        $this->configMock = $this->getMock('Magento\Payment\Model\Config', [], [], '', false);
        $this->repositoryMock = $this->getMock('Magento\Framework\View\Asset\Repository', [], [], '', false);
        $this->requestMock = $this->getMock('Magento\Framework\App\RequestInterface');
        $this->urlMock = $this->getMock('Magento\Framework\UrlInterface');
        $this->loggerMock = $this->getMock('Psr\Log\LoggerInterface');

        $this->model = new \Magento\Payment\Model\CcConfig(
            $this->configMock,
            $this->repositoryMock,
            $this->requestMock,
            $this->urlMock,
            $this->loggerMock
        );
    }

    public function testGetSsStartYears()
    {
        $this->assertCount(6, $this->model->getSsStartYears());
        $this->assertEquals(date("Y"), $this->model->getSsStartYears()[date("Y")]);
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

    public function testHasSsCardType()
    {
        $this->assertEquals(false, $this->model->hasSsCardType());
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
