<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\PreProcessor;

class ModuleNotationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Asset\PreProcessor\ModuleNotation
     */
    protected $moduleNotation;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cssResolverMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $assetMock;

    protected function setUp()
    {
        $this->assetMock = $this->getMock('Magento\Framework\View\Asset\File', [], [], '', false);
        $this->cssResolverMock = $this->getMock('Magento\Framework\View\Url\CssResolver', [], [], '', false);
        $notationResolver = $this->getMock(
            '\Magento\Framework\View\Asset\ModuleNotation\Resolver', [], [], '', false
        );
        $this->moduleNotation = new ModuleNotation(
            $this->cssResolverMock, $notationResolver
        );
    }

    public function testProcess()
    {
        $content = 'ol.favicon {background: url(Magento_Theme::favicon.ico)}';
        $chain = new \Magento\Framework\View\Asset\PreProcessor\Chain($this->assetMock, $content, 'css');
        $replacedContent = 'Foo_Bar/images/logo.gif';
        $this->cssResolverMock->expects($this->once())
            ->method('replaceRelativeUrls')
            ->with($content, $this->isInstanceOf('Closure'))
            ->will($this->returnValue($replacedContent));
        $this->assertSame($content, $chain->getContent());
        $this->moduleNotation->process($chain);
        $this->assertSame($replacedContent, $chain->getContent());
    }
}
