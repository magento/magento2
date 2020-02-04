<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Email\Test\Unit\Model\Plugin;

use Magento\Email\Model\Plugin\WindowsSmtpConfig;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\OsInfo;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 *  WindowsSmtpConfigTest
 */
class WindowsSmtpConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var WindowsSmtpConfig
     */
    private $windowsSmtpConfig;

    /**
     * @var OsInfo|\PHPUnit_Framework_MockObject_MockObject
     */
    private $osInfoMock;

    /**
     * @var ReinitableConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var TransportInterface
     */
    private $transportMock;

    /**
     * setUp
     *
     * @return void
     */
    public function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->osInfoMock = $this->createMock(OsInfo::class);
        $this->configMock = $this->createMock(ReinitableConfigInterface::class);
        $this->transportMock = $this->createMock(TransportInterface::class);

        $this->windowsSmtpConfig = $objectManager->getObject(
            WindowsSmtpConfig::class,
            [
                'config' => $this->configMock,
                'osInfo' => $this->osInfoMock
            ]
        );
    }

    /**
     * Test if SMTP settings if windows server
     *
     * @return void
     */
    public function testBeforeSendMessageOsWindows(): void
    {
        $this->osInfoMock->expects($this->once())
            ->method('isWindows')
            ->willReturn(true);

        $this->configMock->expects($this->exactly(2))
            ->method('getValue')
            ->willReturnMap([
                [WindowsSmtpConfig::XML_SMTP_HOST, '127.0.0.1'],
                [WindowsSmtpConfig::XML_SMTP_PORT, '80']
            ]);

        $this->windowsSmtpConfig->beforeSendMessage($this->transportMock);
    }

    /**
     * Test if SMTP settings if not windows server
     *
     * @return void
     */
    public function testBeforeSendMessageOsIsWindows(): void
    {
        $this->osInfoMock->expects($this->once())
            ->method('isWindows')
            ->willReturn(false);

        $this->configMock->expects($this->never())
            ->method('getValue');

        $this->windowsSmtpConfig->beforeSendMessage($this->transportMock);
    }
}
