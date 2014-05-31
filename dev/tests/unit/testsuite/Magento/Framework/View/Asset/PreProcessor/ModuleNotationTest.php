<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $this->assetMock = $this->getMock('Magento\Framework\View\Asset\File', array(), array(), '', false);
        $this->cssResolverMock = $this->getMock('Magento\Framework\View\Url\CssResolver', array(), array(), '', false);
        $notationResolver = $this->getMock(
            '\Magento\Framework\View\Asset\ModuleNotation\Resolver', array(), array(), '', false
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
