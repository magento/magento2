<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\TestFramework\Test\Unit\Unit\Matcher;

use Magento\Framework\TestFramework\Unit\Matcher\MethodInvokedAtIndex;

class MethodInvokedAtIndexTest extends \PHPUnit_Framework_TestCase
{
    public function testMatches()
    {
        $invocationObject = new \PHPUnit_Framework_MockObject_Invocation_Object(
            'ClassName',
            'ValidMethodName',
            [],
            new \StdClass()
        );
        $matcher = new \Magento\Framework\TestFramework\Unit\Matcher\MethodInvokedAtIndex(0);
        $this->assertTrue($matcher->matches($invocationObject));

        $matcher = new MethodInvokedAtIndex(1);
        $matcher->matches($invocationObject);
        $this->assertTrue($matcher->matches($invocationObject));
    }
}
