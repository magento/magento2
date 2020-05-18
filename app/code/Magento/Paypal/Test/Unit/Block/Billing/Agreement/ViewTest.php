<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Block\Billing\Agreement;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Paypal\Block\Billing\Agreement\View;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    /**
     * @var CollectionFactory|MockObject
     */
    protected $orderCollectionFactory;

    /**
     * @var Config|MockObject
     */
    protected $orderConfig;

    /**
     * @var View
     */
    protected $block;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->orderCollectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->orderConfig = $this->createMock(Config::class);

        $this->block = $objectManager->getObject(
            View::class,
            [
                'orderCollectionFactory' => $this->orderCollectionFactory,
                'orderConfig' => $this->orderConfig,
            ]
        );
    }

    public function testGetRelatedOrders()
    {
        $visibleStatuses = [];

        $orderCollection = $this->createPartialMock(
            Collection::class,
            ['addFieldToSelect', 'addFieldToFilter', 'setOrder']
        );
        $orderCollection->expects($this->at(0))
            ->method('addFieldToSelect')
            ->willReturn($orderCollection);
        $orderCollection->expects($this->at(1))
            ->method('addFieldToFilter')
            ->willReturn($orderCollection);
        $orderCollection->expects($this->at(2))
            ->method('addFieldToFilter')
            ->with('status', ['in' => $visibleStatuses])
            ->willReturn($orderCollection);
        $orderCollection->expects($this->at(3))
            ->method('setOrder')
            ->willReturn($orderCollection);

        $this->orderCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($orderCollection);
        $this->orderConfig->expects($this->once())
            ->method('getVisibleOnFrontStatuses')
            ->willReturn($visibleStatuses);

        $this->block->getRelatedOrders();
    }
}
