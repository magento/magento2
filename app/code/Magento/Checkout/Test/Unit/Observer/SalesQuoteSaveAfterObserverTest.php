<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Observer;

use Magento\Checkout\Model\Session;
use Magento\Checkout\Observer\SalesQuoteSaveAfterObserver;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SalesQuoteSaveAfterObserverTest extends TestCase
{
    /** @var SalesQuoteSaveAfterObserver */
    protected $object;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var MockObject */
    protected $checkoutSession;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->checkoutSession = $this->createMock(Session::class);
        $this->object = $this->objectManager->getObject(
            SalesQuoteSaveAfterObserver::class,
            ['checkoutSession' => $this->checkoutSession]
        );
    }

    public function testSalesQuoteSaveAfter()
    {
        $quoteId = 7;
        $observer = $this->createMock(Observer::class);
        $observer->expects($this->once())->method('getEvent')->willReturn(
            new DataObject(
                ['quote' => new DataObject(['is_checkout_cart' => 1, 'id' => $quoteId])]
            )
        );
        $this->checkoutSession->expects($this->once())->method('setQuoteId')->with($quoteId);

        $this->object->execute($observer);
    }
}
