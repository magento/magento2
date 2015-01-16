<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Matcher;

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
        $matcher = new MethodInvokedAtIndex(0);
        $this->assertTrue($matcher->matches($invocationObject));

        $matcher = new MethodInvokedAtIndex(1);
        $matcher->matches($invocationObject);
        $this->assertTrue($matcher->matches($invocationObject));
    }
}
