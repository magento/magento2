<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Design\Fallback;

use \Magento\Framework\View\Design\Fallback\RulePool;
use Magento\Framework\Filesystem;

class RulePoolTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RulePool
     */
    private $model;

    protected function setUp(): void
    {
        $filesystemMock = $this->createMock(\Magento\Framework\Filesystem::class);
        $filesystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->will($this->returnCallback(function ($code) {
                $dirMock = $this->getMockForAbstractClass(\Magento\Framework\Filesystem\Directory\ReadInterface::class);
                $dirMock->expects($this->any())
                    ->method('getAbsolutePath')
                    ->will($this->returnCallback(function ($path) use ($code) {
                        $path = empty($path) ? $path : '/' . $path;
                        return rtrim($code, '/') . $path;
                    }));
                return $dirMock;
            }));

        $simpleFactory = $this->createMock(\Magento\Framework\View\Design\Fallback\Rule\SimpleFactory::class);
        $rule = $this->getMockForAbstractClass(\Magento\Framework\View\Design\Fallback\Rule\RuleInterface::class);
        $simpleFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($rule));

        $themeFactory = $this->createMock(\Magento\Framework\View\Design\Fallback\Rule\ThemeFactory::class);
        $themeFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($rule));
        $moduleFactory = $this->createMock(\Magento\Framework\View\Design\Fallback\Rule\ModuleFactory::class);
        $moduleFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($rule));
        $moduleSwitchFactory =
            $this->createMock(\Magento\Framework\View\Design\Fallback\Rule\ModularSwitchFactory::class);
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

        $parentTheme = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);
        $parentTheme->expects($this->any())->method('getThemePath')->will($this->returnValue('parent_theme_path'));

        $theme = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);
        $theme->expects($this->any())->method('getThemePath')->will($this->returnValue('current_theme_path'));
        $theme->expects($this->any())->method('getParentTheme')->will($this->returnValue($parentTheme));
    }

    protected function tearDown(): void
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
        $this->assertInstanceOf(\Magento\Framework\View\Design\Fallback\Rule\RuleInterface::class, $actualResult);
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

    public function testGetRuleUnsupportedType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Fallback rule \'unsupported_type\' is not supported');
        $this->model->getRule('unsupported_type');
    }
}
