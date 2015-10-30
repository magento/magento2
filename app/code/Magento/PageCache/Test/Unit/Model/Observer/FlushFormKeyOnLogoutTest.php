<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Test\Unit\Model\Observer;

use Magento\Framework\App\PageCache\FormKey;
use Magento\PageCache\Model\Observer\FlushFormKeyOnLogout;

class FlushFormKeyOnLogoutTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        /** @var FormKey | \PHPUnit_Framework_MockObject_MockObject $cookieFormKey */
        $cookieFormKey = $this->getMockBuilder(
            'Magento\Framework\App\PageCache\FormKey'
        )
            ->disableOriginalConstructor()
            ->getMock();

        $observer = new FlushFormKeyOnLogout($cookieFormKey);

        $cookieFormKey->expects(static::once())
            ->method('delete');
        $observer->execute();
    }
}
