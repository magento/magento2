<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Helper;

class JsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Magento\Framework\View\Helper\Js::getScript
     */
    public function testGetScript()
    {
        $helper = new \Magento\Framework\View\Helper\Js();
        $this->assertEquals(
            "<script type=\"text/javascript\">//<![CDATA[\ntest\n//]]></script>",
            $helper->getScript('test')
        );
    }
}
