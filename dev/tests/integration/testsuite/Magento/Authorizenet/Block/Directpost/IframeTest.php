<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Authorizenet\Block\Directpost;

class IframeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testToHtml()
    {
        $xssString = '</script><script>alert("XSS")</script>';
        /** @var $block \Magento\Authorizenet\Block\Directpost\Iframe */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Authorizenet\Block\Directpost\Iframe'
        );
        $block->setTemplate('directpost/iframe.phtml');
        $block->setParams(['redirect' => $xssString, 'redirect_parent' => $xssString, 'error_msg' => $xssString]);
        $content = $block->toHtml();
        $this->assertNotContains($xssString, $content, 'Params mast be escaped');
        $this->assertContains(htmlspecialchars($xssString), $content, 'Content must present');
    }
}
