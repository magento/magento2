<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Design\Fallback\Rule;

use \Magento\Framework\View\Design\Fallback\Rule\Simple;

/**
 * Simple Test
 *
 */
class SimpleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Module\Dir\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduelReader;

    /**
     *
     * @var \Magento\Framework\Component\ComponentRegistrarInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $componentRegistrarMock;

    public function setup()
    {
        $this->moduelReader = $this->getMockBuilder('Magento\Framework\Module\Dir\Reader')
            ->disableOriginalConstructor()->getMock();
        $this->componentRegistrarMock = $this->getMockBuilder('Magento\Framework\Component\ComponentRegistrarInterface')
            ->disableOriginalConstructor()->getMock();
    }
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Required parameter 'required_parameter' was not passed
     */
    public function testGetPatternDirsException()
    {
        $model = new Simple('<required_parameter> other text', $this->componentRegistrarMock, $this->moduelReader);
        $model->getPatternDirs([]);
    }

    /**
     * @dataProvider getPatternDirsDataProvider
     */
    public function testGetPatternDirs($pattern, $optionalParameter = null, $expectedResult = null)
    {
        $params = ['optional_parameter' => $optionalParameter, 'required_parameter' => 'required_parameter'];
        $model = new Simple($pattern, $this->componentRegistrarMock, $this->moduelReader, ['optional_parameter']);

        $this->assertEquals($expectedResult, $model->getPatternDirs($params));
    }

    /**
     * @return array
     */
    public function getPatternDirsDataProvider()
    {
        $patternOptional = '<optional_parameter> <required_parameter> other text';
        $patternNoOptional = '<required_parameter> other text';

        return [
            'no optional param passed' => [$patternOptional, null, []],
            'no optional param in pattern' => [
                $patternNoOptional,
                'optional_parameter',
                ['required_parameter other text'],
            ],
            'optional params in pattern and passed' => [
                $patternOptional,
                'optional_parameter',
                ['optional_parameter required_parameter other text'],
            ]
        ];
    }

    public function testGetPatternDirsForModule()
    {
        $pattern = '<module_dir>/<namespace>/<module>';
        $params = [
            'optional_parameter' => 'optional_parameter',
            'required_parameter' => 'required_parameter',
            'namespace' => 'sample_namespace',
            'module' => 'sample_module',

        ];
        $this->moduelReader->expects($this->once())->method('getModuleDir')->will($this->returnValue(
            'path/to/sample/module'
        ));
        $model = new Simple($pattern, $this->componentRegistrarMock, $this->moduelReader);

        $this->assertEquals(['path/to/sample/module'], $model->getPatternDirs($params));
    }

    public function testGetPatternDirsForTheme()
    {
        $pattern = '<theme_dir>/<area>/<theme_path>';
        $params = [
            'optional_parameter' => 'optional_parameter',
            'required_parameter' => 'required_parameter',
            'area' => 'sample_area',
            'theme_path' => 'sample_theme_path',

        ];
        $this->componentRegistrarMock->expects($this->once())->method('getPath')->will($this->returnValue(
            'path/to/sample/theme'
        ));
        $model = new Simple($pattern, $this->componentRegistrarMock, $this->moduelReader);

        $this->assertEquals(['path/to/sample/theme'], $model->getPatternDirs($params));
    }
}
