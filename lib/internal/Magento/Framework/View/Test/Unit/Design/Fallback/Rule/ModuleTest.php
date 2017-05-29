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

class ModuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RuleInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $rule;

    /**
     * @var ComponentRegistrarInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $componentRegistrar;

    /**
     * @var Module
     */
    private $model;

    protected function setUp()
    {
        $this->rule = $this->getMockForAbstractClass(\Magento\Framework\View\Design\Fallback\Rule\RuleInterface::class);
        $this->componentRegistrar = $this->getMockForAbstractClass(
            \Magento\Framework\Component\ComponentRegistrarInterface::class
        );
        $this->model = new Module($this->rule, $this->componentRegistrar);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Required parameter "module_name" is not specified
     */
    public function testGetPatternDirsException()
    {
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
            ->will($this->returnValue($modulePath));
        $this->rule->expects($this->once())
            ->method('getPatternDirs')
            ->with(['module_name' => $module, 'module_dir' => $modulePath])
            ->will($this->returnValue($expectedResult));
        $this->assertEquals($expectedResult, $this->model->getPatternDirs(['module_name' => $module]));
    }
}
