<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Guest;

/**
 * Test class for \Magento\Sales\Block\Guest\Link
 */
class LinkTest extends \PHPUnit\Framework\TestCase
{
    public function testToHtml()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $context = $objectManagerHelper->getObject(\Magento\Framework\View\Element\Template\Context::class);
        $httpContext = $this->getMockBuilder(\Magento\Framework\App\Http\Context::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();
        $httpContext->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue(true));

        /** @var \Magento\Sales\Block\Guest\Link $link */
        $link = $objectManagerHelper->getObject(
            \Magento\Sales\Block\Guest\Link::class,
            [
                'context' => $context,
                'httpContext' => $httpContext,
            ]
        );

        $this->assertEquals('', $link->toHtml());
    }
}
