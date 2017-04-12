<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Page\Config\Reader;

class HtmlTest extends \PHPUnit_Framework_TestCase
{
    public function testInterpret()
    {
        /** @var \Magento\Framework\View\Layout\Reader\Context $readerContext */
        $readerContext = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\View\Layout\Reader\Context::class
        );
        $pageXml = new \Magento\Framework\View\Layout\Element(__DIR__ . '/_files/_layout_update.xml', 0, true);
        $parentElement = new \Magento\Framework\View\Layout\Element('<page></page>');

        $html = new Html();
        foreach ($pageXml->xpath('html') as $htmlElement) {
            $html->interpret($readerContext, $htmlElement, $parentElement);
        }

        $structure = $readerContext->getPageConfigStructure();
        $this->assertEquals(['html' => ['test-name' => 'test-value']], $structure->getElementAttributes());
    }
}
