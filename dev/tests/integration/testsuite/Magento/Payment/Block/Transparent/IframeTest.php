<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Block\Transparent;

/**
 * Class IframeTest
 * @package Magento\Payment\Block\Transparent
 */
class IframeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @dataProvider xssDataProvider
     */
    public function testToHtml($xssString)
    {
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

        $this->assertNotContains($xssString, $content, 'Params must be escaped');
        $this->assertContains($block->escapeXssInUrl($xssString), $content, 'Content must be present');
    }

    /**
     * @return array
     */
    public function xssDataProvider()
    {
        return [
            ['</script><script>alert("XSS")</script>'],
            ['javascript%3Aalert%28String.fromCharCode%280x78%29%2BString.fromCharCode%280x73%29%2BString.'
                . 'fromCharCode%280x73%29%29'],
            ['javascript:alert(String.fromCharCode(0x78)+String.fromCharCode(0x73)+String.fromCharCode(0x73))']
        ];
    }
}
