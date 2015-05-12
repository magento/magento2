<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Block\Transparent;

class IframeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testToHtml()
    {
        $xssString = '</script><script>alert("XSS")</script>';

        /** @var $block Iframe */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Payment\Block\Transparent\Iframe'
        );

        $block->setTemplate('transparent/iframe.phtml');
        $block->setData(
            'params',
            [
                'redirect' => $xssString,
                'redirect_parent' => $xssString,
                'error_msg' => $xssString
            ]
        );

        $content = $block->toHtml();

        $this->assertNotContains($xssString, $content, 'Params mast be escaped');
        $this->assertContains(htmlspecialchars($xssString), $content, 'Content must present');
    }
}
