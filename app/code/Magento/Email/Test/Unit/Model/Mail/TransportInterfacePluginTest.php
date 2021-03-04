<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Test\Unit\Model\Mail;

use Magento\Email\Model\Mail\TransportInterfacePlugin;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\TransportInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Covers \Magento\Email\Model\Transport
 */
class TransportInterfacePluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TransportInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $transportMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \Callable|\PHPUnit\Framework\MockObject\MockObject
     */
    private $proceedMock;

    /**
     * @var bool
     */
    private $isProceedMockCalled = false;

    /**
     * @var TransportInterfacePlugin
     */
    private $model;

    protected function setUp(): void
    {
        $this->transportMock = $this->getMockForAbstractClass(TransportInterface::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->proceedMock = function () {
            $this->isProceedMockCalled = true;
        };

        $this->model = new TransportInterfacePlugin($this->scopeConfigMock);
    }

    /**
     * @dataProvider sendMessageDataProvider
     * @param bool $isDisabled
     * @param bool $shouldProceedRun
     */
    public function testAroundSendMessage(bool $isDisabled, bool $shouldProceedRun)
    {
        $this->isProceedMockCalled = false;

        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with('system/smtp/disable', ScopeInterface::SCOPE_STORE)
            ->willReturn($isDisabled);
        $this->model->aroundSendMessage($this->transportMock, $this->proceedMock);
        $this->assertEquals($shouldProceedRun, $this->isProceedMockCalled);
    }

    /**
     * Data provider for testAroundSendMessage
     * @return array
     */
    public function sendMessageDataProvider()
    {
        return [
            [false, true],
            [true, false],
        ];
    }
}
