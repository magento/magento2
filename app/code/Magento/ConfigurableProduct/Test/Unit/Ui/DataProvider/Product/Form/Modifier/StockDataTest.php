<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\StockData as StockDataModifier;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StockDataTest extends TestCase
{
    /**
     * @var StockDataModifier
     */
    private $stockDataModifier;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var LocatorInterface|MockObject
     */
    private $productLocatorMock;

    /**
     * @var ProductInterface|MockObject
     */
    private $productMock;

    protected function setUp(): void
    {
        $this->productLocatorMock = $this->getMockBuilder(LocatorInterface::class)
            ->getMockForAbstractClass();
        $this->productMock = $this->getMockBuilder(ProductInterface::class)
            ->getMockForAbstractClass();

        $this->productLocatorMock->expects(static::any())
            ->method('getProduct')
            ->willReturn($this->productMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->stockDataModifier = $this->objectManagerHelper->getObject(
            StockDataModifier::class,
            [
                'locator' => $this->productLocatorMock
            ]
        );
    }

    public function testModifyMeta()
    {
        $this->assertArrayHasKey('advanced_inventory_modal', $this->stockDataModifier->modifyMeta([]));
    }
}
