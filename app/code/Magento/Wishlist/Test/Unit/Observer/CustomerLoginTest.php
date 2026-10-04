<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Observer\CustomerLogin as Observer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerLoginTest extends TestCase
{
    /**
     * @var Observer
     */
    protected $observer;

    /**
     * @var Data|MockObject
     */
    protected $helper;

    protected function setUp(): void
    {
        $this->helper = $this->createMock(Data::class);

        $this->observer = new Observer($this->helper);
    }

    public function testExecute()
    {
        $event = $this->createMock(EventObserver::class);
        /** @var EventObserver $event */

        $this->helper->expects($this->once())
            ->method('calculate');

        $this->observer->execute($event);
    }
}
