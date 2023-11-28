<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAnalytics\Test\Unit\Controller\Adminhtml\Config;

use Magento\AdminAnalytics\Controller\Adminhtml\Config\DisableAdminUsage;
use Magento\AdminAnalytics\Model\ResourceModel\Viewer\Logger as NotificationLogger;
use Magento\Backend\App\Action\Context;
use Magento\Config\Model\Config;
use Magento\Config\Model\Config\Factory;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DisableAdminUsageTest extends TestCase
{
    /**
     * @var DisableAdminUsage
     */
    private $disableAdminUsage;

    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var ProductMetadataInterface|MockObject
     */
    private $productMetadata;

    /**
     * @var NotificationLogger|MockObject
     */
    private $notificationLogger;

    /**
     * @var Factory|MockObject
     */
    private $configFactory;

    /**
     * @var ResultFactory|MockObject
     */
    private $resultFactory;

    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->productMetadata = $this->createMock(ProductMetadataInterface::class);
        $this->notificationLogger = $this->createMock(NotificationLogger::class);
        $this->configFactory = $this->createMock(Factory::class);

        $this->resultFactory = $this->createMock(ResultFactory::class);
        $this->context
            ->method('getResultFactory')
            ->willReturn($this->resultFactory);

        $objectManager = new ObjectManager($this);
        $this->disableAdminUsage = $objectManager->getObject(
            DisableAdminUsage::class,
            [
                'context' => $this->context,
                'productMetadata' => $this->productMetadata,
                'notificationLogger' => $this->notificationLogger,
                'configFactory' => $this->configFactory,
            ]
        );
    }

    public function testExecute()
    {
        $configModel = $this->createMock(Config::class);
        $this->configFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($configModel);
        $configModel
            ->expects($this->once())
            ->method('setDataByPath')
            ->with('admin/usage/enabled', 0)
            ->willReturnSelf();
        $configModel
            ->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $version = '1.1.1';
        $this->productMetadata
            ->expects($this->once())
            ->method('getVersion')
            ->willReturn($version);
        $this->notificationLogger
            ->expects($this->once())
            ->method('log')
            ->with($version)
            ->willReturn(true);

        $resultJson = $this->createMock(Json::class);
        $this->resultFactory
            ->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($resultJson);

        $responseContent = [
            'success' => true,
            'error_message' => ''
        ];

        $resultJson
            ->expects($this->once())
            ->method('setData')
            ->with($responseContent)
            ->willReturnSelf();

        $this->disableAdminUsage->execute();
    }
}
