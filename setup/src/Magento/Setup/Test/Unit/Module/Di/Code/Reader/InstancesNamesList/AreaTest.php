<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\Code\Reader\InstancesNamesList;

use Magento\Setup\Module\Di\Code\Reader\ClassesScanner;
use Magento\Setup\Module\Di\Code\Reader\ClassReaderDecorator;
use Magento\Setup\Module\Di\Code\Reader\Decorator\Area;
use Magento\Framework\Interception\PluginListGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AreaTest extends TestCase
{
    /**
     * @var ClassesScanner|MockObject
     */
    private $classesScannerMock;

    /**
     * @var ClassReaderDecorator|MockObject
     */
    private $classReaderDecoratorMock;

    /**
     * @var PluginListGenerator|MockObject
     */
    private $pluginListGeneratorMock;

    /**
     * @var Area
     */
    private $model;

    protected function setUp(): void
    {
        $this->classesScannerMock = $this->getMockBuilder(ClassesScanner::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getList'])
            ->getMock();

        $this->classReaderDecoratorMock = $this->getMockBuilder(
            ClassReaderDecorator::class
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['getConstructor'])
            ->getMock();

        $this->pluginListGeneratorMock = $this->getMockBuilder(PluginListGenerator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isOrphanedPlugin'])
            ->getMock();

        // Default: treat no classes as orphaned plugins (normal resolution)
        $this->pluginListGeneratorMock->method('isOrphanedPlugin')->willReturn(false);

        $this->model = new Area(
            $this->classesScannerMock,
            $this->classReaderDecoratorMock,
            $this->pluginListGeneratorMock
        );
    }

    public function testGetList()
    {
        $path = '/tmp/test';

        $classes = ['NameSpace1\ClassName1', 'NameSpace1\ClassName2'];

        $this->classesScannerMock->expects($this->once())
            ->method('getList')
            ->with($path)
            ->willReturn($classes);

        $constructors = [
            ['NameSpace1\ClassName1', ['arg1' => 'NameSpace1\class5', 'arg2' => 'NameSpace1\ClassName4']],
            ['NameSpace1\ClassName2', ['arg1' => 'NameSpace1\class5']]
        ];

        $this->classReaderDecoratorMock->expects($this->exactly(count($classes)))
            ->method('getConstructor')
            ->willReturnMap($constructors);

        $result = $this->model->getList($path);

        $expected = [
            $classes[0] => $constructors[0][1],
            $classes[1] => $constructors[1][1]
        ];

        $this->assertEquals($result, $expected);
    }

    public function testGetListSkipsCtorResolutionForOrphanedPlugins()
    {
        $path = '/tmp/test';
        $normalClass = 'NameSpace1\ClassName1';
        $orphanedPluginClass = 'Acme\Demo\Plugin\WebstorePlugin';
        $classes = [$normalClass, $orphanedPluginClass];

        $this->classesScannerMock->expects($this->once())
            ->method('getList')
            ->with($path)
            ->willReturn($classes);

        // Only the non-orphaned class should have its constructor inspected
        $this->classReaderDecoratorMock->expects($this->once())
            ->method('getConstructor')
            ->with($normalClass)
            ->willReturn(['arg1' => 'NameSpace1\class5']);

        // Configure the generator mock to report the plugin as orphaned
        $this->pluginListGeneratorMock = $this->getMockBuilder(PluginListGenerator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isOrphanedPlugin'])
            ->getMock();
        $this->pluginListGeneratorMock->method('isOrphanedPlugin')
            ->willReturnMap([
                [$normalClass, false],
                [$orphanedPluginClass, true],
            ]);

        // Re-instantiate with the specific mock for this test
        $model = new Area(
            $this->classesScannerMock,
            $this->classReaderDecoratorMock,
            $this->pluginListGeneratorMock
        );

        $result = $model->getList($path);

        $this->assertArrayHasKey($normalClass, $result);
        $this->assertArrayNotHasKey($orphanedPluginClass, $result);
    }

    public function testGetListPropagatesCtorResolutionErrorsForNonOrphanedClasses()
    {
        $path = '/tmp/test';
        $className = 'Vendor\\Module\\Service\\WithBrokenDep';
        $classes = [$className];

        $this->classesScannerMock->expects($this->once())
            ->method('getList')
            ->with($path)
            ->willReturn($classes);

        $this->classReaderDecoratorMock->expects($this->once())
            ->method('getConstructor')
            ->with($className)
            ->willThrowException(new \ReflectionException(
                'Impossible to process constructor argument for ' . $className
            ));

        $this->expectException(\ReflectionException::class);
        $this->model->getList($path);
    }
}
