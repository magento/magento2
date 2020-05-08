<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\Code\Reader\InstancesNamesList;

use Magento\Setup\Module\Di\Code\Reader\ClassesScanner;
use Magento\Setup\Module\Di\Code\Reader\ClassReaderDecorator;
use Magento\Setup\Module\Di\Code\Reader\Decorator\Area;
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
     * @var Area
     */
    private $model;

    protected function setUp(): void
    {
        $this->classesScannerMock = $this->getMockBuilder(ClassesScanner::class)
            ->disableOriginalConstructor()
            ->setMethods(['getList'])
            ->getMock();

        $this->classReaderDecoratorMock = $this->getMockBuilder(
            ClassReaderDecorator::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['getConstructor'])
            ->getMock();

        $this->model = new Area(
            $this->classesScannerMock,
            $this->classReaderDecoratorMock
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
}
