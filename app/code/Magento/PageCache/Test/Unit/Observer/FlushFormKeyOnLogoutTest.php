<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Test\Unit\Observer;

use Magento\Framework\App\PageCache\FormKey;
use Magento\PageCache\Observer\FlushFormKeyOnLogout;

class FlushFormKeyOnLogoutTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        /** @var FormKey | \PHPUnit_Framework_MockObject_MockObject $cookieFormKey */
        $cookieFormKey = $this->getMockBuilder(
            \Magento\Framework\App\PageCache\FormKey::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $observerObject = $this->getMock(\Magento\Framework\Event\Observer::class);

        $observer = new FlushFormKeyOnLogout($cookieFormKey);

        $cookieFormKey->expects(static::once())
            ->method('delete');
        $observer->execute($observerObject);
    }
}
