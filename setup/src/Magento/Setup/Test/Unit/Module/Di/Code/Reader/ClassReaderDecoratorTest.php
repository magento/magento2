<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\Code\Reader;

use Magento\Framework\Code\Reader\ClassReader;
use Magento\Setup\Module\Di\Code\Reader\ClassReaderDecorator;
use Magento\Setup\Module\Di\Compiler\ConstructorArgument;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ClassReaderDecoratorTest extends TestCase
{
    /**
     * @var ClassReaderDecorator
     */
    private $model;

    /**
     * @var ClassReader|MockObject
     */
    private $classReaderMock;

    protected function setUp(): void
    {
        $this->classReaderMock = $this->getMockBuilder(ClassReader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new ClassReaderDecorator($this->classReaderMock);
    }

    /**
     * @param $expectation
     * @param $className
     * @param $willReturn
     * @dataProvider getConstructorDataProvider
     */
    public function testGetConstructor($expectation, $className, $willReturn)
    {
        $this->classReaderMock->expects($this->once())
            ->method('getConstructor')
            ->with($className)
            ->willReturn($willReturn);
        $this->assertEquals(
            $expectation,
            $this->model->getConstructor($className)
        );
    }

    /**
     * @return array
     */
    public static function getConstructorDataProvider()
    {
        return [
            [null, 'null', null],
            [
                [new ConstructorArgument(['name', 'type', 'isRequired', 'defaultValue'])],
                'array',
                [['name', 'type', 'isRequired', 'defaultValue']]
            ]
        ];
    }

    public function testGetParents()
    {
        $stringArray = ['Parent_Class_Name1', 'Interface_1'];
        $this->classReaderMock->expects($this->once())
            ->method('getParents')
            ->with('Child_Class_Name')
            ->willReturn($stringArray);
        $this->assertEquals($stringArray, $this->model->getParents('Child_Class_Name'));
    }
}
