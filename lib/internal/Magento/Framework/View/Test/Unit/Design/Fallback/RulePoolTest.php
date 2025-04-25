<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Design\Fallback;

use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\View\Design\Fallback\Rule\ModularSwitchFactory;
use Magento\Framework\View\Design\Fallback\Rule\ModuleFactory;
use Magento\Framework\View\Design\Fallback\Rule\RuleInterface;
use Magento\Framework\View\Design\Fallback\Rule\SimpleFactory;
use Magento\Framework\View\Design\Fallback\Rule\ThemeFactory;
use Magento\Framework\View\Design\Fallback\RulePool;
use Magento\Framework\View\Design\ThemeInterface;
use PHPUnit\Framework\TestCase;

class RulePoolTest extends TestCase
{
    /**
     * @var RulePool
     */
    private $model;

    protected function setUp(): void
    {
        $filesystemMock = $this->createMock(Filesystem::class);
        $filesystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->willReturnCallback(function ($code) {
                $dirMock = $this->getMockForAbstractClass(ReadInterface::class);
                $dirMock->expects($this->any())
                    ->method('getAbsolutePath')
                    ->willReturnCallback(function ($path) use ($code) {
                        $path = empty($path) ? $path : '/' . $path;
                        return rtrim($code, '/') . $path;
                    });
                return $dirMock;
            });

        $simpleFactory = $this->createMock(SimpleFactory::class);
        $rule = $this->getMockForAbstractClass(RuleInterface::class);
        $simpleFactory->expects($this->any())
            ->method('create')
            ->willReturn($rule);

        $themeFactory = $this->createMock(ThemeFactory::class);
        $themeFactory->expects($this->any())
            ->method('create')
            ->willReturn($rule);
        $moduleFactory = $this->createMock(ModuleFactory::class);
        $moduleFactory->expects($this->any())
            ->method('create')
            ->willReturn($rule);
        $moduleSwitchFactory =
            $this->createMock(ModularSwitchFactory::class);
        $moduleSwitchFactory->expects($this->any())
            ->method('create')
            ->willReturn($rule);
        $this->model = new RulePool(
            $filesystemMock,
            $simpleFactory,
            $themeFactory,
            $moduleFactory,
            $moduleSwitchFactory
        );

        $parentTheme = $this->getMockForAbstractClass(ThemeInterface::class);
        $parentTheme->expects($this->any())->method('getThemePath')->willReturn('parent_theme_path');

        $theme = $this->getMockForAbstractClass(ThemeInterface::class);
        $theme->expects($this->any())->method('getThemePath')->willReturn('current_theme_path');
        $theme->expects($this->any())->method('getParentTheme')->willReturn($parentTheme);
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
        $this->assertInstanceOf(RuleInterface::class, $actualResult);
        $this->assertSame($actualResult, $this->model->getRule($type));
    }

    /**
     * @return array
     */
    public static function getRuleDataProvider()
    {
        return [
            [RulePool::TYPE_LOCALE_FILE],
            [RulePool::TYPE_FILE],
            [RulePool::TYPE_TEMPLATE_FILE],
            [RulePool::TYPE_STATIC_FILE],
        ];
    }

    public function testGetRuleUnsupportedType()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Fallback rule \'unsupported_type\' is not supported');
        $this->model->getRule('unsupported_type');
    }
}
