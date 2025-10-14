<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Plugin\Catalog\Model\Product\Attribute\Backend\TierPrice;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Backend\TierPrice\UpdateHandler;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Plugin\Catalog\Model\Product\Attribute\Backend\TierPrice\UpdateHandlerPlugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Plugin for handling tier prices during product attribute backend update.
 */
class UpdateHandlerPluginTest extends TestCase
{
    /**
     * @var UpdateHandlerPlugin|MockObject
     */
    private $updateHandlerPlugin;

    /**
     * @var ProductAttributeRepositoryInterface|MockObject
     */
    private $attributeRepositoryMock;

    /**
     * @var UpdateHandler|MockObject
     */
    private $updateHandlerMock;

    /**
     * @var Product|MockObject
     */
    private $entityMock;

    /**
     * @var ProductAttributeInterface|MockObject
     */
    private $attributeMock;

    protected function setUp(): void
    {
        $this->attributeRepositoryMock = $this->createMock(ProductAttributeRepositoryInterface::class);
        $this->entityMock = $this->createMock(Product::class);
        $this->updateHandlerMock = $this->createMock(UpdateHandler::class);

        $this->attributeMock = $this->getMockBuilder(ProductAttributeInterface::class)
            ->addMethods(['getName'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with('tier_price')
            ->willReturn($this->attributeMock);
        $this->attributeMock->expects($this->any())
            ->method('getName')
            ->willReturn('tier_price');

        $this->updateHandlerPlugin = new UpdateHandlerPlugin($this->attributeRepositoryMock);
    }

    /**
     * Test for the 'beforeExecute' method when tier prices exist for configurable products.
     *
     * @return void
     */
    public function testBeforeExecute()
    {
        $origPrices = [
            [
                'price_id' => 28,
                'website_id' => 0,
                'all_groups' => 1,
                'cust_group' => 32000,
                'price' => 50.000000,
                'price_qty' => 2.0000,
                'percentage_value' => null,
                'product_id' => 29,
                'website_price' => 50.000000
            ]
        ];

        $this->entityMock->expects($this->once())
            ->method('getOrigData')
            ->with('tier_price')
            ->willReturn($origPrices);

        $this->entityMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $this->entityMock->expects($this->once())
            ->method('setData')
            ->with($this->equalTo('tier_price'), $this->equalTo([]));

        $result =  $this->updateHandlerPlugin->beforeExecute($this->updateHandlerMock, $this->entityMock);

        $this->assertEquals(
            [$this->entityMock, []],
            $result
        );
    }

    /**
     * Test case to verify the behavior of beforeExecute method for a simple product.
     *
     * @return void
     */
    public function testBeforeExecuteSimpleProduct()
    {
        $origPrices = [
            [
                'price_id' => 12,
                'website_id' => 0,
                'all_groups' => 1,
                'cust_group' => 4200,
                'price' => 100.000000,
                'price_qty' => 5.0000,
                'percentage_value' => null,
                'product_id' => 30,
                'website_price' => 50.000000
            ]
        ];

        $this->entityMock->expects($this->once())
            ->method('getOrigData')
            ->with('tier_price')
            ->willReturn($origPrices);

        $this->entityMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Product\Type::TYPE_SIMPLE);

        $this->entityMock->expects($this->never())
            ->method('setData')
            ->with($this->equalTo('tier_price'), $this->equalTo([]));

        $result = $this->updateHandlerPlugin->beforeExecute($this->updateHandlerMock, $this->entityMock, []);

        $this->assertEquals(
            [$this->entityMock, []],
            $result
        );
    }

    /**
     * Test case to verify the behavior when original prices data is empty.
     *
     * @return void
     */
    public function testOriginalPrices()
    {
        $origPrices = [];
        $this->entityMock->expects($this->once())
            ->method('getOrigData')
            ->with('tier_price')
            ->willReturn($origPrices);

        $this->entityMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $this->entityMock->expects($this->once())
            ->method('getOrigData')
            ->with('tier_price')
            ->willReturn($origPrices);

        $this->entityMock->expects($this->once())
            ->method('setData')
            ->with($this->equalTo('tier_price'), $this->equalTo([]));

        $result = $this->updateHandlerPlugin->beforeExecute($this->updateHandlerMock, $this->entityMock, []);

        $this->assertEquals(
            [$this->entityMock, []],
            $result
        );
    }
}
