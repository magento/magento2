<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\Di\Code\Reader;

use Magento\Setup\Module\Di\Compiler\ConstructorArgument;

class ClassReaderDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Module\Di\Code\Reader\ClassReaderDecorator
     */
    private $model;

    /**
     * @var \Magento\Framework\Code\Reader\ClassReader | \PHPUnit_Framework_MockObject_MockObject
     */
    private $classReaderMock;

    protected function setUp()
    {
        $this->classReaderMock = $this->getMockBuilder(\Magento\Framework\Code\Reader\ClassReader::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->model = new \Magento\Setup\Module\Di\Code\Reader\ClassReaderDecorator($this->classReaderMock);
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

    public function getConstructorDataProvider()
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
