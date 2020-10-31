<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReleaseNotification\Test\Unit\Model\Condition;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ReleaseNotification\Model\Condition\CanViewNotification;
use Magento\ReleaseNotification\Model\ResourceModel\Viewer\Logger;
use Magento\ReleaseNotification\Model\Viewer\Log;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CanViewNotificationTest extends TestCase
{
    /** @var CanViewNotification */
    private $canViewNotification;

    /** @var Logger|MockObject */
    private $viewerLoggerMock;

    /** @var ProductMetadataInterface|MockObject */
    private $productMetadataMock;

    /** @var Session|MockObject */
    private $sessionMock;

    /** @var Log|MockObject */
    private $logMock;

    /** @var MockObject|CacheInterface */
    private $cacheStorageMock;

    protected function setUp(): void
    {
        $this->cacheStorageMock = $this->getMockBuilder(CacheInterface::class)
            ->getMockForAbstractClass();
        $this->logMock = $this->getMockBuilder(Log::class)
            ->getMock();
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUser', 'getId'])
            ->getMock();
        $this->viewerLoggerMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productMetadataMock = $this->getMockBuilder(ProductMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $objectManager = new ObjectManager($this);
        $this->canViewNotification = $objectManager->getObject(
            CanViewNotification::class,
            [
                'viewerLogger' => $this->viewerLoggerMock,
                'session' => $this->sessionMock,
                'productMetadata' => $this->productMetadataMock,
                'cacheStorage' => $this->cacheStorageMock,
            ]
        );
    }

    public function testIsVisibleLoadDataFromCache()
    {
        $this->sessionMock->expects($this->once())
            ->method('getUser')
            ->willReturn($this->sessionMock);
        $this->sessionMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->cacheStorageMock->expects($this->once())
            ->method('load')
            ->with('release-notification-popup-1')
            ->willReturn("0");
        $this->assertFalse($this->canViewNotification->isVisible([]));
    }

    /**
     * @param bool $expected
     * @param string $version
     * @param string|null $lastViewVersion
     * @dataProvider isVisibleProvider
     */
    public function testIsVisible($expected, $version, $lastViewVersion)
    {
        $this->cacheStorageMock->expects($this->once())
            ->method('load')
            ->with('release-notification-popup-1')
            ->willReturn(false);
        $this->sessionMock->expects($this->once())
            ->method('getUser')
            ->willReturn($this->sessionMock);
        $this->sessionMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->productMetadataMock->expects($this->once())
            ->method('getVersion')
            ->willReturn($version);
        $this->logMock->expects($this->once())
            ->method('getLastViewVersion')
            ->willReturn($lastViewVersion);
        $this->viewerLoggerMock->expects($this->once())
            ->method('get')
            ->with(1)
            ->willReturn($this->logMock);
        $this->cacheStorageMock->expects($this->once())
            ->method('save')
            ->with(false, 'release-notification-popup-1');
        $this->assertEquals($expected, $this->canViewNotification->isVisible([]));
    }

    /**
     * @return array
     */
    public function isVisibleProvider()
    {
        return [
            [false, '2.2.1-dev', '999.999.999-alpha'],
            [true, '2.2.1-dev', '2.0.0'],
            [true, '2.2.1-dev', null],
            [false, '2.2.1-dev', '2.2.1'],
            [true, '2.2.1-dev', '2.2.0'],
            [true, '2.3.0', '2.2.0'],
            [false, '2.2.2', '2.2.2'],
        ];
    }
}
