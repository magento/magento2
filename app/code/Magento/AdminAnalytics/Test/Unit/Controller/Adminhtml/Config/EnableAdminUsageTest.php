<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAnalytics\Test\Unit\Controller\Adminhtml\Config;

use Magento\AdminAnalytics\Controller\Adminhtml\Config\EnableAdminUsage;
use Magento\AdminAnalytics\Model\ResourceModel\Viewer\Logger as NotificationLogger;
use Magento\Config\Model\Config;
use Magento\Config\Model\Config\Factory as ConfigFactory;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Magento\AdminAnalytics\Controller\Adminhtml\Config\EnableAdminUsage
 */
class EnableAdminUsageTest extends \PHPUnit\Framework\TestCase
{
    private const STUB_PRODUCT_VERSION = 'Product Version';

    /** @var EnableAdminUsage */
    private $controller;

    /** @var MockObject|Config */
    private $configMock;

    /** @var MockObject|ProductMetadataInterface */
    private $productMetadataMock;

    /** @var MockObject|NotificationLogger */
    private $notificationLoggerMock;

    /** @var MockObject|ResultFactory */
    private $resultFactoryMock;

    /** @var JsonResult|MockObject */
    private $resultMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setDataByPath', 'save'])
            ->getMock();

        $configFactory = $this->getMockBuilder(ConfigFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $configFactory->method('create')
            ->willReturn($this->configMock);

        $this->productMetadataMock = $this->getMockBuilder(ProductMetadataInterface::class)
            ->onlyMethods(['getVersion'])
            ->getMockForAbstractClass();

        $this->productMetadataMock->method('getVersion')
            ->willReturn(self::STUB_PRODUCT_VERSION);

        $this->notificationLoggerMock = $this->getMockBuilder(NotificationLogger::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['log'])
            ->getMock();

        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->resultMock = $this->getMockBuilder(JsonResult::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setData'])
            ->getMock();

        $this->resultFactoryMock->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($this->resultMock);

        $this->controller = $objectManager->getObject(EnableAdminUsage::class, [
            'configFactory' => $configFactory,
            'productMetadata' => $this->productMetadataMock,
            'notificationLogger' => $this->notificationLoggerMock,
            'resultFactory' => $this->resultFactoryMock
        ]);
    }

    /**
     * If Controller returns `null`, no data is passed to the browser
     */
    public function testResponseAfterAdminUsageChange()
    {
        // Given
        $this->resultMock->method('setData')->willReturnSelf();

        // When
        $response = $this->controller->execute();

        // Then
        $this->assertInstanceOf(ResultInterface::class, $response);
    }

    public function testResponseWhenExceptionThrown()
    {
        $this->markTestSkipped('magento/magento2#31393 Lack of exception handling');

        $this->configMock->method('setDataByPath')
            ->willThrowException(
                new \Exception('System Exception')
            );

        // When
        $response = $this->controller->execute();

        // Then
        $this->assertInstanceOf(ResultInterface::class, $response);
    }
}
