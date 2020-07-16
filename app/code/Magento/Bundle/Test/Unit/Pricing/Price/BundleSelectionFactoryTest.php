<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Pricing\Price;

use Magento\Bundle\Pricing\Price\BundleSelectionFactory;
use Magento\Bundle\Pricing\Price\BundleSelectionPrice;
use Magento\Catalog\Model\Product;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;

use PHPUnit\Framework\TestCase;

class BundleSelectionFactoryTest extends TestCase
{
    /** @var BundleSelectionFactory */
    protected $bundleSelectionFactory;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var ObjectManagerInterface|MockObject */
    protected $objectManagerMock;

    /** @var SaleableInterface|MockObject */
    protected $bundleMock;

    /** @var SaleableInterface|MockObject */
    protected $selectionMock;

    protected function setUp(): void
    {
        $this->bundleMock = $this->createMock(Product::class);
        $this->selectionMock = $this->createMock(Product::class);

        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->bundleSelectionFactory = $this->objectManagerHelper->getObject(
            BundleSelectionFactory::class,
            [
                'objectManager' => $this->objectManagerMock
            ]
        );
    }

    public function testCreate()
    {
        $result = $this->createMock(BundleSelectionPrice::class);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(
                BundleSelectionFactory::SELECTION_CLASS_DEFAULT,
                [
                    'test' => 'some value',
                    'bundleProduct' => $this->bundleMock,
                    'saleableItem' => $this->selectionMock,
                    'quantity' => 2.,
                ]
            )
            ->willReturn($result);
        $this->assertSame(
            $result,
            $this->bundleSelectionFactory
                ->create($this->bundleMock, $this->selectionMock, 2., ['test' => 'some value'])
        );
    }
}
