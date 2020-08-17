<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Price\Validation;

use Magento\Catalog\Api\Data\PriceUpdateResultInterface;
use Magento\Catalog\Api\Data\PriceUpdateResultInterfaceFactory;
use Magento\Catalog\Model\Product\Price\Validation\Result;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ResultTest extends TestCase
{
    /**
     * @var Result
     */
    private $model;

    /**
     * @var PriceUpdateResultInterfaceFactory|MockObject
     */
    private $priceUpdateResultFactory;

    /**
     * @var ObjectManagerHelper|MockObject
     */
    private $objectManager;

    /**
     * Setup environment for test
     */
    protected function setUp(): void
    {
        $this->priceUpdateResultFactory = $this->getMockBuilder(PriceUpdateResultInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->objectManager = new ObjectManagerHelper($this);
        $this->model = $this->objectManager->getObject(
            Result::class,
            [
                'priceUpdateResultFactory' => $this->priceUpdateResultFactory
            ]
        );

        $this->model->addFailedItem(1, 'Invalid attribute color = 1', ['SKU' => 'ABC', 'storeId' => 1]);
        $this->model->addFailedItem(2, 'Invalid attribute size = M', ['SKU' => 'DEF', 'storeId' => 1]);
    }

    /**
     * Test getFailedRowIds() function
     */
    public function testGetFailedRowIds()
    {
        $this->assertEquals([1, 2], $this->model->getFailedRowIds());
    }

    /**
     * Test getFailedItems() function
     */
    public function testGetFailedItems()
    {
        $priceUpdateResult1 = $this->getMockForAbstractClass(PriceUpdateResultInterface::class);
        $priceUpdateResult2 = $this->getMockForAbstractClass(PriceUpdateResultInterface::class);

        $this->priceUpdateResultFactory->expects($this->at(0))
            ->method('create')
            ->willReturn($priceUpdateResult1);
        $this->priceUpdateResultFactory->expects($this->at(1))
            ->method('create')
            ->willReturn($priceUpdateResult2);

        $priceUpdateResult1->expects($this->once())->method('setMessage')
            ->with('Invalid attribute color = 1');
        $priceUpdateResult1->expects($this->once())->method('setParameters')
            ->with(['SKU' => 'ABC', 'storeId' => 1]);

        $priceUpdateResult2->expects($this->once())->method('setMessage')
            ->with('Invalid attribute size = M');
        $priceUpdateResult2->expects($this->once())->method('setParameters')
            ->with(['SKU' => 'DEF', 'storeId' => 1]);

        $this->assertEquals([$priceUpdateResult1, $priceUpdateResult2], $this->model->getFailedItems());
    }
}
