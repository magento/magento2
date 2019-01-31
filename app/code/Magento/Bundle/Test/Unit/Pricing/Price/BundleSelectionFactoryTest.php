<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Unit\Pricing\Price;

use \Magento\Bundle\Pricing\Price\BundleSelectionFactory;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class BundleSelectionFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Bundle\Pricing\Price\BundleSelectionFactory */
    protected $bundleSelectionFactory;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $objectManagerMock;

    /** @var \Magento\Framework\Pricing\SaleableInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $bundleMock;

    /** @var \Magento\Framework\Pricing\SaleableInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $selectionMock;

    protected function setUp()
    {
        $this->bundleMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->selectionMock = $this->createMock(\Magento\Catalog\Model\Product::class);

        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->bundleSelectionFactory = $this->objectManagerHelper->getObject(
            \Magento\Bundle\Pricing\Price\BundleSelectionFactory::class,
            [
                'objectManager' => $this->objectManagerMock
            ]
        );
    }

    public function testCreate()
    {
        $result = $this->createMock(\Magento\Bundle\Pricing\Price\BundleSelectionPrice::class);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo(BundleSelectionFactory::SELECTION_CLASS_DEFAULT),
                $this->equalTo(
                    [
                        'test' => 'some value',
                        'bundleProduct' => $this->bundleMock,
                        'saleableItem' => $this->selectionMock,
                        'quantity' => 2.,
                    ]
                )
            )
        ->will($this->returnValue($result));
        $this->assertSame(
            $result,
            $this->bundleSelectionFactory
                ->create($this->bundleMock, $this->selectionMock, 2., ['test' => 'some value'])
        );
    }
}
