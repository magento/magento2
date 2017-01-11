<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class SalesQuoteSaveAfterObserverTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Checkout\Observer\SalesQuoteSaveAfterObserver */
    protected $object;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    protected $objectManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $checkoutSession;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->checkoutSession = $this->getMock(\Magento\Checkout\Model\Session::class, [], [], '', false);
        $this->object = $this->objectManager->getObject(
            \Magento\Checkout\Observer\SalesQuoteSaveAfterObserver::class,
            ['checkoutSession' => $this->checkoutSession]
        );
    }

    public function testSalesQuoteSaveAfter()
    {
        $observer = $this->getMock(\Magento\Framework\Event\Observer::class, [], [], '', false);
        $observer->expects($this->once())->method('getEvent')->will(
            $this->returnValue(new \Magento\Framework\DataObject(
                ['quote' => new \Magento\Framework\DataObject(['is_checkout_cart' => 1, 'id' => 7])]
            ))
        );
        $this->checkoutSession->expects($this->once())->method('getQuoteId')->with(7);

        $this->object->execute($observer);
    }
}
