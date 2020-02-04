<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminAnalytics\Test\Unit\Condition;

use Magento\AdminAnalytics\Model\Condition\CanViewNotification;
use Magento\AdminAnalytics\Model\ResourceModel\Viewer\Logger;
use Magento\AdminAnalytics\Model\Viewer\Log;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\CacheInterface;

/**
 * Class CanViewNotificationTest
 */
class CanViewNotificationTest extends \PHPUnit\Framework\TestCase
{
    /** @var CanViewNotification */
    private $canViewNotification;

    /** @var  Logger|\PHPUnit_Framework_MockObject_MockObject */
    private $viewerLoggerMock;

    /** @var ProductMetadataInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $productMetadataMock;

    /** @var  Log|\PHPUnit_Framework_MockObject_MockObject */
    private $logMock;

    /** @var  $cacheStorageMock \PHPUnit_Framework_MockObject_MockObject|CacheInterface */
    private $cacheStorageMock;

    public function setUp()
    {
        $this->cacheStorageMock = $this->getMockBuilder(CacheInterface::class)
            ->getMockForAbstractClass();
        $this->logMock = $this->getMockBuilder(Log::class)
            ->getMock();
        $this->viewerLoggerMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productMetadataMock = $this->getMockBuilder(ProductMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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
