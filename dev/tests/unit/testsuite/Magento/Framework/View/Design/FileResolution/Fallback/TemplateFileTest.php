<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Design\FileResolution\Fallback;

use Magento\Framework\App\State;
use Magento\Framework\View\Design\Fallback\RulePool;

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

    protected function setUp()
    {
        $this->resolver = $this->getMock('Magento\Framework\View\Design\FileResolution\Fallback\ResolverInterface');
        $this->minifier = $this->getMock('Magento\Framework\View\Template\Html\MinifierInterface');
        $this->state = $this->getMockBuilder('Magento\Framework\App\State')->disableOriginalConstructor()->getMock();
        $this->object = new TemplateFile($this->resolver, $this->minifier, $this->state);
    }

    /**
     * Cover getFile when mode is developer
     */
    public function testGetFileWhenStateDeveloper()
    {
        $theme = $this->getMockForAbstractClass('\Magento\Framework\View\Design\ThemeInterface');
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
     * @dataProvider getMinifiedDataProvider
     */
    public function testGetFileWhenModifiedNeeded($mode, $method)
    {
        $theme = $this->getMockForAbstractClass('\Magento\Framework\View\Design\ThemeInterface');
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
