<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Design\Fallback;

use \Magento\Framework\View\Design\Fallback\RulePool;

use Magento\Framework\Filesystem;

class RulePoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RulePool
     */
    private $model;

    protected function setUp()
    {
        $filesystemMock = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $filesystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->will($this->returnCallback(function ($code) {
                $dirMock = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\ReadInterface');
                $dirMock->expects($this->any())
                    ->method('getAbsolutePath')
                    ->will($this->returnCallback(function ($path) use ($code) {
                        $path = empty($path) ? $path : '/' . $path;
                        return rtrim($code, '/') . $path;
                    }));
                return $dirMock;
            }));

        $simpleFactory = $this->getMock('Magento\Framework\View\Design\Fallback\Rule\SimpleFactory', [], [], '', false);
        $rule = $this->getMockForAbstractClass('\Magento\Framework\View\Design\Fallback\Rule\RuleInterface');
        $simpleFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($rule));

        $themeFactory = $this->getMock('Magento\Framework\View\Design\Fallback\Rule\ThemeFactory', [], [], '', false);
        $themeFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($rule));
        $moduleFactory = $this->getMock('Magento\Framework\View\Design\Fallback\Rule\ModuleFactory', [], [], '', false);
        $moduleFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($rule));
        $moduleSwitchFactory = $this->getMock(
            'Magento\Framework\View\Design\Fallback\Rule\ModularSwitchFactory',
            [],
            [],
            '',
            false
        );
        $moduleSwitchFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($rule));
        $this->model = new RulePool(
            $filesystemMock,
            $simpleFactory,
            $themeFactory,
            $moduleFactory,
            $moduleSwitchFactory
        );

        $parentTheme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $parentTheme->expects($this->any())->method('getThemePath')->will($this->returnValue('parent_theme_path'));

        $theme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $theme->expects($this->any())->method('getThemePath')->will($this->returnValue('current_theme_path'));
        $theme->expects($this->any())->method('getParentTheme')->will($this->returnValue($parentTheme));
    }

    protected function tearDown()
    {
        $this->model = null;
    }

    /**
     * @param string $type
     *
     * @dataProvider getRuleDataProvider
     */
    public function testGetRule($type)
    {
        $actualResult = $this->model->getRule($type);
        $this->assertInstanceOf('\Magento\Framework\View\Design\Fallback\Rule\RuleInterface', $actualResult);
        $this->assertSame($actualResult, $this->model->getRule($type));
    }

    /**
     * @return array
     */
    public function getRuleDataProvider()
    {
        return [
            [\Magento\Framework\View\Design\Fallback\RulePool::TYPE_LOCALE_FILE],
            [\Magento\Framework\View\Design\Fallback\RulePool::TYPE_FILE],
            [\Magento\Framework\View\Design\Fallback\RulePool::TYPE_TEMPLATE_FILE],
            [\Magento\Framework\View\Design\Fallback\RulePool::TYPE_STATIC_FILE],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedException Fallback rule 'unsupported_type' is not supported
     */
    public function testGetRuleUnsupportedType()
    {
        $this->model->getRule('unsupported_type');
    }
}
