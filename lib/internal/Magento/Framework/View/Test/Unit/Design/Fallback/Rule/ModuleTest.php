<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Design\Fallback\Rule;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use \Magento\Framework\View\Design\Fallback\Rule\Module;
use Magento\Framework\View\Design\Fallback\Rule\RuleInterface;

class ModuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RuleInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $rule;

    /**
     * @var ComponentRegistrarInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $componentRegistrar;

    /**
     * @var Module
     */
    private $model;

    protected function setUp(): void
    {
        $this->rule = $this->getMockForAbstractClass(\Magento\Framework\View\Design\Fallback\Rule\RuleInterface::class);
        $this->componentRegistrar = $this->getMockForAbstractClass(
            \Magento\Framework\Component\ComponentRegistrarInterface::class
        );
        $this->model = new Module($this->rule, $this->componentRegistrar);
    }

    /**
     */
    public function testGetPatternDirsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Required parameter "module_name" is not specified');

        $this->model->getPatternDirs([]);
    }

    public function testGetPatternDirs()
    {
        $expectedResult = ['path1', 'path2'];
        $module = 'Some_Module';
        $modulePath = '/module/path';
        $this->componentRegistrar->expects($this->once())
            ->method('getPath')
            ->with(ComponentRegistrar::MODULE, $module)
            ->willReturn($modulePath);
        $this->rule->expects($this->once())
            ->method('getPatternDirs')
            ->with(['module_name' => $module, 'module_dir' => $modulePath])
            ->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $this->model->getPatternDirs(['module_name' => $module]));
    }
}
