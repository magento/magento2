<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Guest;

/**
 * Test class for \Magento\Sales\Block\Guest\Link
 */
class LinkTest extends \PHPUnit_Framework_TestCase
{
    public function testToHtml()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $context = $objectManagerHelper->getObject('Magento\Framework\View\Element\Template\Context');
        $httpContext = $this->getMockBuilder('\Magento\Framework\App\Http\Context')
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();
        $httpContext->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue(true));

        /** @var \Magento\Sales\Block\Guest\Link $link */
        $link = $objectManagerHelper->getObject(
            'Magento\Sales\Block\Guest\Link',
            [
                'context' => $context,
                'httpContext' => $httpContext,
            ]
        );

        $this->assertEquals('', $link->toHtml());
    }
}
