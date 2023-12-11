<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\CustomerData;

use Magento\Catalog\Api\Data\ProductFrontendActionInterface;
use Magento\Catalog\CustomerData\ProductFrontendActionSection;
use Magento\Catalog\Model\Product\ProductFrontendAction\Synchronizer;
use Magento\Framework\App\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ProductFrontendActionSectionTest extends TestCase
{
    /** @var ProductFrontendActionSection */
    protected $model;

    /** @var MockObject */
    protected $synchronizerMock;

    /** @var Config|MockObject */
    private $appConfigMock;

    /** @var  LoggerInterface|MockObject */
    private $loggerMock;

    protected function setUp(): void
    {
        $this->synchronizerMock = $this
            ->getMockBuilder(Synchronizer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->appConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();

        $this->model = new ProductFrontendActionSection(
            $this->synchronizerMock,
            '1',
            $this->loggerMock,
            $this->appConfigMock
        );
    }

    public function testGetSectionData()
    {
        $this->appConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Synchronizer::ALLOW_SYNC_WITH_BACKEND_PATH)
            ->willReturn(1);
        $actionFirst = $this->getMockBuilder(ProductFrontendActionInterface::class)
            ->getMockForAbstractClass();
        $actionSecond = $this->getMockBuilder(ProductFrontendActionInterface::class)
            ->getMockForAbstractClass();
        $actions = [$actionFirst, $actionSecond];
        $actionFirst->expects($this->exactly(2))
            ->method('getProductId')
            ->willReturn(1);
        $actionFirst->expects($this->once())
            ->method('getAddedAt')
            ->willReturn(12);
        $actionSecond->expects($this->once())
            ->method('getAddedAt')
            ->willReturn(13);
        $actionSecond->expects($this->exactly(2))
            ->method('getProductId')
            ->willReturn(2);
        $this->synchronizerMock->expects($this->once())
            ->method('getActionsByType')
            ->willReturn($actions);

        $this->assertEquals(
            [
                'count' => 2,
                'items' => [
                    1 => [
                        'added_at' => 12,
                        'product_id' => 1
                    ],
                    2 => [
                        'added_at' => 13,
                        'product_id' => 2
                    ]
                ]
            ],
            $this->model->getSectionData()
        );
    }

    public function testGetSectionDataNoSync()
    {
        $this->appConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Synchronizer::ALLOW_SYNC_WITH_BACKEND_PATH)
            ->willReturn(0);

        $this->assertEquals(
            [
                'count' => 0,
                'items' => [
                ]
            ],
            $this->model->getSectionData()
        );
    }
}
