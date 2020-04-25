<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\TestFramework\Test\Unit\Unit\Matcher;

use Magento\Framework\TestFramework\Unit\Matcher\MethodInvokedAtIndex;
use PHPUnit\Framework\MockObject\Invocation\ObjectInvocation;
use PHPUnit\Framework\TestCase;

class MethodInvokedAtIndexTest extends TestCase
{
    public function testMatches()
    {
        $invocationObject = new ObjectInvocation(
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
