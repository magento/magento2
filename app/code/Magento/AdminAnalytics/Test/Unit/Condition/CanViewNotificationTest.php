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
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CanViewNotificationTest extends TestCase
{
    /** @var CanViewNotification */
    private $canViewNotification;

    /** @var  Logger|MockObject */
    private $viewerLoggerMock;

    /** @var ProductMetadataInterface|MockObject */
    private $productMetadataMock;

    /** @var  Log|MockObject */
    private $logMock;

    /** @var MockObject|CacheInterface $cacheStorageMock */
    private $cacheStorageMock;

    protected function setUp(): void
    {
        $this->cacheStorageMock = $this->getMockBuilder(CacheInterface::class)
            ->getMockForAbstractClass();
        $this->logMock = $this->createMock(Log::class);
        $this->viewerLoggerMock = $this->createMock(Logger::class);
        $this->productMetadataMock = $this->getMockForAbstractClass(ProductMetadataInterface::class);
        $objectManager = new ObjectManager($this);
        $this->canViewNotification = $objectManager->getObject(
            CanViewNotification::class,
            [
                'viewerLogger' => $this->viewerLoggerMock,
                'productMetadata' => $this->productMetadataMock,
                'cacheStorage' => $this->cacheStorageMock,
            ]
        );
    }

    /**
     * @param $expected
     * @param $cacheResponse
     * @param $logExists
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
