<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\ViewModel\Product\Checker;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\ViewModel\Product\Checker\AddToCompareAvailability;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\Catalog\ViewModel\Product\Checker\AddToCompareAvailability.
 */
class AddToCompareAvailabilityTest extends TestCase
{

    /**
     * @var AddToCompareAvailability
     */
    private $viewModel;

    /**
     * @var StockConfigurationInterface|MockObject
     */
    protected $stockConfigurationMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->stockConfigurationMock = $this->createMock(StockConfigurationInterface::class);

        $this->viewModel = $objectManager->getObject(
            AddToCompareAvailability::class,
            [
                'stockConfiguration' => $this->stockConfigurationMock
            ]
        );
    }

    /**
     * Test IsAvailableForCompare() with data provider
     *
     * @param bool $status
     * @param bool $isSalable
     * @param array $isInStock
     * @param bool $isShowOutOfStock
     * @param bool $expectedBool
     * @return void
     */
    #[DataProvider('isAvailableForCompareDataProvider')]
    public function testIsAvailableForCompare($status, $isSalable, $isInStock, $isShowOutOfStock, $expectedBool): void
    {
        $productMock = $this->createMock(Product::class);

        $productMock->expects($this->once())
            ->method('getStatus')
            ->willReturn($status);

        $productMock->method('isSalable')->willReturn($isSalable);

        $productMock->method('getQuantityAndStockStatus')->willReturn($isInStock);

        $this->stockConfigurationMock->method('isShowOutOfStock')->willReturn($isShowOutOfStock);

        $this->assertEquals($expectedBool, $this->viewModel->isAvailableForCompare($productMock));
    }

    /**
     * Data provider for isAvailableForCompare()
     *
     * @return array
     */
    public static function isAvailableForCompareDataProvider(): array
    {
        return [
            [Status::STATUS_ENABLED, true, ['is_in_stock' => true], false, true],
            [Status::STATUS_ENABLED, true, ['is_in_stock' => false], true, true],
            [Status::STATUS_ENABLED, true, [], false, true],
            [Status::STATUS_ENABLED, false, [], false, false],
            [Status::STATUS_DISABLED, true, ['is_in_stock' => true], false, false]
        ];
    }
}
