<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\ViewModel\Product\Checker;

use Magento\Catalog\ViewModel\Product\Checker\AddToCompareAvailability;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Unit test for Magento\Catalog\ViewModel\Product\Checker\AddToCompareAvailability.
 */
class AddToCompareAvailabilityTest extends \PHPUnit\Framework\TestCase
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

        $this->stockConfigurationMock =
            $this->getMockBuilder(StockConfigurationInterface::class)
            ->getMock();

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
     * @return boolean
     * @dataProvider isAvailableForCompareDataProvider
     */
    public function testIsAvailableForCompare($status, $isSalable, $isInStock, $isShowOutOfStock): bool
    {
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->once())
            ->method('getStatus')
            ->willReturn($status);

        $productMock->expects($this->any())
            ->method('isSalable')
            ->willReturn($isSalable);

        $productMock->expects($this->any())
            ->method('getQuantityAndStockStatus')
            ->willReturn($isInStock);

        $this->stockConfigurationMock->expects($this->any())
            ->method('isShowOutOfStock')
            ->willReturn($isShowOutOfStock);

        return $this->viewModel->isAvailableForCompare($productMock);
    }

    /**
     * Data provider for isAvailableForCompare()
     *
     * @return array
     */
    public function isAvailableForCompareDataProvider(): array
    {
        return [
            [1, true, ['is_in_stock' => true], false],
            [1, true, ['is_in_stock' => false], true],
            [1, true, [], false],
            [1, false, [], false],
            [2, true, ['is_in_stock' => true], false]
        ];
    }
}
