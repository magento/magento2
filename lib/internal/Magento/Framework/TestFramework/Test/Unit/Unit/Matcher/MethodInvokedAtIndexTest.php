<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\TestFramework\Test\Unit\Unit\Matcher;

use Magento\Framework\TestFramework\Unit\Matcher\MethodInvokedAtIndex;

class MethodInvokedAtIndexTest extends \PHPUnit\Framework\TestCase
{
    public function testMatches()
    {
        $invocationObject = new \PHPUnit\Framework\MockObject\Invocation\ObjectInvocation(
            'ClassName',
            'ValidMethodName',
            [],
            'void',
            new \StdClass()
        );
        $matcher = new MethodInvokedAtIndex(0);
        $this->assertTrue($matcher->matches($invocationObject));

        $matcher = new MethodInvokedAtIndex(1);
        $matcher->matches($invocationObject);
        $this->assertTrue($matcher->matches($invocationObject));
    }
}
