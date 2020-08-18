<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Product;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ContextTest extends TestCase
{
    /**
     * @var StockRegistryInterface|MockObject
     */
    protected $stockRegistryMock;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ImageBuilder|MockObject
     */
    protected $imageBuilder;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->stockRegistryMock = $this->getMockForAbstractClass(
            StockRegistryInterface::class,
            [],
            '',
            false
        );

        $this->imageBuilder = $this->getMockBuilder(ImageBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $objectManager->getObject(
            Context::class,
            [
                'stockRegistry' => $this->stockRegistryMock,
                'imageBuilder' => $this->imageBuilder,
            ]
        );
    }

    /**
     * Run test getStockRegistry method
     *
     * @return void
     */
    public function testGetStockRegistry()
    {
        $this->assertEquals($this->stockRegistryMock, $this->context->getStockRegistry());
    }

    public function testGetImageBuilder()
    {
        $this->assertInstanceOf(ImageBuilder::class, $this->context->getImageBuilder());
    }
}
