<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Product;

/**
 * Class ContextTest
 */
class ContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistryMock;

    /**
     * @var \Magento\Catalog\Block\Product\Context
     */
    protected $context;

    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageBuilder;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->stockRegistryMock = $this->getMockForAbstractClass(
            \Magento\CatalogInventory\Api\StockRegistryInterface::class,
            [],
            '',
            false
        );

        $this->imageBuilder = $this->getMockBuilder(\Magento\Catalog\Block\Product\ImageBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $objectManager->getObject(
            \Magento\Catalog\Block\Product\Context::class,
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
        $this->assertInstanceOf(\Magento\Catalog\Block\Product\ImageBuilder::class, $this->context->getImageBuilder());
    }
}
