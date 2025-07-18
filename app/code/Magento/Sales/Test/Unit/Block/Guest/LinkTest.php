<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Guest;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Block\Guest\Link;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Sales\Block\Guest\Link
 */
class LinkTest extends TestCase
{
    public function testToHtml()
    {
        $objectManagerHelper = new ObjectManager($this);

        $context = $objectManagerHelper->getObject(Context::class);
        $httpContext = $this->getMockBuilder(\Magento\Framework\App\Http\Context::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValue'])
            ->getMock();
        $httpContext->expects($this->once())
            ->method('getValue')
            ->willReturn(true);

        /** @var Link $link */
        $link = $objectManagerHelper->getObject(
            Link::class,
            [
                'context' => $context,
                'httpContext' => $httpContext,
            ]
        );

        $this->assertEquals('', $link->toHtml());
    }
}
