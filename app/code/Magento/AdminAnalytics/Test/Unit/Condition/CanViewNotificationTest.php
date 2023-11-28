<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAnalytics\Test\Unit\Condition;

use Magento\AdminAnalytics\Model\Condition\CanViewNotification;
use Magento\AdminAnalytics\Model\ResourceModel\Viewer\Logger;
use Magento\AdminAnalytics\Model\Viewer\Log;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CanViewNotificationTest extends TestCase
{
    /** @var CanViewNotification */
    private $canViewNotification;

    /** @var Logger|MockObject */
    private $viewerLoggerMock;

    /** @var CacheInterface|MockObject */
    private $cacheStorageMock;

    /** @var Log|MockObject */
    private $logMock;

    protected function setUp(): void
    {
        $this->viewerLoggerMock = $this->createMock(Logger::class);
        $this->cacheStorageMock = $this->getMockBuilder(CacheInterface::class)
            ->getMockForAbstractClass();

        $this->logMock = $this->createMock(Log::class);

        $objectManager = new ObjectManager($this);
        $this->canViewNotification = $objectManager->getObject(
            CanViewNotification::class,
            [
                'viewerLogger' => $this->viewerLoggerMock,
                'cacheStorage' => $this->cacheStorageMock,
            ]
        );
    }

    /**
     * @param $expected
     * @param $cacheResponse
     * @param $logExists
     *
     * @dataProvider isVisibleProvider
     */
    public function testIsVisibleLoadDataFromLog($expected, $cacheResponse, $logExists)
    {
        $this->cacheStorageMock->expects($this->once())
            ->method('load')
            ->with('admin-usage-notification-popup')
            ->willReturn($cacheResponse);
        $this->viewerLoggerMock
            ->method('checkLogExists')
            ->willReturn($logExists);
        $this->cacheStorageMock
            ->method('save')
            ->with('log-exists', 'admin-usage-notification-popup');
        $this->assertEquals($expected, $this->canViewNotification->isVisible([]));
    }

    public function testGetName()
    {
        $result = $this->canViewNotification->getName();

        $this->assertSame('can_view_admin_usage_notification', $result);
    }

    /**
     * @return array
     */
    public function isVisibleProvider()
    {
        return [
            [true, false, false],
            [false, 'log-exists', true],
            [false, false, true],
        ];
    }
}
