<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Helper;

use PHPUnit\Framework\TestCase;
use Magento\Framework\View\Helper\Js;

class JsTest extends TestCase
{
    /**
     * @covers \Magento\Framework\View\Helper\Js::getScript
     */
    public function testGetScript()
    {
        $helper = new Js();
        $this->assertEquals(
            "<script type=\"text/javascript\">//<![CDATA[\ntest\n//]]></script>",
            $helper->getScript('test')
        );
    }
}
