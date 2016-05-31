<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Unit\Observer;

use \Magento\Wishlist\Observer\CustomerLogin as Observer;

class CustomerLoginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Observer
     */
    protected $observer;

    /**
     * @var \Magento\Wishlist\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    protected function setUp()
    {
        $this->helper = $this->getMockBuilder('Magento\Wishlist\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();

        $this->observer = new Observer($this->helper);
    }

    public function testExecute()
    {
        $event = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->getMock();
        /** @var $event \Magento\Framework\Event\Observer */

        $this->helper->expects($this->once())
            ->method('calculate');

        $this->observer->execute($event);
    }
}
