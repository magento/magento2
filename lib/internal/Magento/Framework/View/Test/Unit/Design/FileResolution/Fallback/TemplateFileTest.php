<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Design\FileResolution\Fallback;

use Magento\Framework\App\State;
use Magento\Framework\View\Design\Fallback\RulePool;
use Magento\Framework\View\Design\FileResolution\Fallback\TemplateFile;
use Magento\Framework\View\Design\FileResolution\Fallback\ResolverInterface;

class TemplateFileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resolver;

    /**
     * @var \Magento\Framework\View\Template\Html\MinifierInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $minifier;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $state;

    /**
     * @var TemplateFile
     */
    protected $object;

    /**
     * @var \Magento\Framework\View\Asset\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $assetConfig;

    protected function setUp()
    {
        $this->resolver = $this->getMock(
            \Magento\Framework\View\Design\FileResolution\Fallback\ResolverInterface::class
        );
        $this->minifier = $this->getMock(\Magento\Framework\View\Template\Html\MinifierInterface::class);
        $this->state = $this->getMockBuilder(
            \Magento\Framework\App\State::class
        )->disableOriginalConstructor()->getMock();
        $this->assetConfig = $this->getMockForAbstractClass(
            \Magento\Framework\View\Asset\ConfigInterface::class,
            [],
            '',
            false
        );
        $this->object = new TemplateFile($this->resolver, $this->minifier, $this->state, $this->assetConfig);
    }

    /**
     * Cover getFile when mode is developer
     */
    public function testGetFileWhenStateDeveloper()
    {
        $this->assetConfig
            ->expects($this->once())
            ->method('isMinifyHtml')
            ->willReturn(true);

        $theme = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);
        $expected = 'some/file.ext';

        $this->state->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_DEVELOPER);
        $this->resolver->expects($this->once())
            ->method('resolve')
            ->with(RulePool::TYPE_TEMPLATE_FILE, 'file.ext', 'frontend', $theme, null, 'Magento_Module')
            ->will($this->returnValue($expected));

        $actual = $this->object->getFile('frontend', $theme, 'file.ext', 'Magento_Module');
        $this->assertSame($expected, $actual);
    }

    /**
     * Cover getFile when mode is default
     * @param string $mode
     * @param string $method
     * @dataProvider getMinifiedDataProvider
     */
    public function testGetFileWhenModifiedNeeded($mode, $method)
    {
        $this->assetConfig
            ->expects($this->once())
            ->method('isMinifyHtml')
            ->willReturn(true);

        $theme = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);
        $expected = 'some/file.ext';
        $expectedMinified = '/path/to/minified/some/file.ext';

        $this->state->expects($this->once())
            ->method('getMode')
            ->willReturn($mode);
        $this->resolver->expects($this->once())
            ->method('resolve')
            ->with(RulePool::TYPE_TEMPLATE_FILE, 'file.ext', 'frontend', $theme, null, 'Magento_Module')
            ->will($this->returnValue($expected));
        $this->minifier->expects($this->once())
            ->method($method)
            ->with($expected)
            ->willReturn($expectedMinified);

        $actual = $this->object->getFile('frontend', $theme, 'file.ext', 'Magento_Module');
        $this->assertSame($expectedMinified, $actual);
    }

    public function testGetFileIfMinificationIsDisabled()
    {
        $this->assetConfig
            ->expects($this->once())
            ->method('isMinifyHtml')
            ->willReturn(false);

        $theme = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);
        $expected = 'some/file.ext';

        $this->resolver->expects($this->once())
            ->method('resolve')
            ->with(RulePool::TYPE_TEMPLATE_FILE, 'file.ext', 'frontend', $theme, null, 'Magento_Module')
            ->will($this->returnValue($expected));

        $this->state->expects($this->never())->method('getMode');

        $actual = $this->object->getFile('frontend', $theme, 'file.ext', 'Magento_Module');
        $this->assertSame($expected, $actual);
    }

    /**
     * Contain different methods by mode for HTML minification
     *
     * @return array
     */
    public function getMinifiedDataProvider()
    {
        return [
            'default' => [State::MODE_DEFAULT, 'getMinified'],
            'production' => [State::MODE_PRODUCTION, 'getPathToMinified'],
        ];
    }
}
