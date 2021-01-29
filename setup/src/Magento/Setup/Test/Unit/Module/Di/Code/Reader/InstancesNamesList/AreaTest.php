<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\Di\Code\Reader\InstancesNamesList;

use \Magento\Setup\Module\Di\Code\Reader\Decorator\Area;

/**
 * Class AreaTest
 *
 * @package Magento\Setup\Module\Di\Code\Reader\Decorator
 */
class AreaTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Module\Di\Code\Reader\ClassesScanner | \PHPUnit\Framework\MockObject\MockObject
     */
    private $classesScannerMock;

    /**
     * @var \Magento\Setup\Module\Di\Code\Reader\ClassReaderDecorator | \PHPUnit\Framework\MockObject\MockObject
     */
    private $classReaderDecoratorMock;

    /**
     * @var Area
     */
    private $model;

    protected function setUp(): void
    {
        $this->classesScannerMock = $this->getMockBuilder(\Magento\Setup\Module\Di\Code\Reader\ClassesScanner::class)
            ->disableOriginalConstructor()
            ->setMethods(['getList'])
            ->getMock();

        $this->classReaderDecoratorMock = $this->getMockBuilder(
            \Magento\Setup\Module\Di\Code\Reader\ClassReaderDecorator::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['getConstructor'])
            ->getMock();

        $this->model = new \Magento\Setup\Module\Di\Code\Reader\Decorator\Area(
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
            ->willReturnMap(
                $constructors
            );

        $result = $this->model->getList($path);

        $expected = [
            $classes[0] => $constructors[0][1],
            $classes[1] => $constructors[1][1]
        ];

        $this->assertEquals($result, $expected);
    }
}
