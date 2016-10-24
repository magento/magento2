<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Code\Test\Unit\Validator;

class ConstructorArgumentTypesTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $argumentsReaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sourceArgumentsReaderMock;

    /**
     * @var \Magento\Framework\Code\Validator\ConstructorArgumentTypes
     */
    protected $model;

    protected function setUp()
    {
        $this->argumentsReaderMock = $this->getMock(
            \Magento\Framework\Code\Reader\ArgumentsReader::class,
            [],
            [],
            '',
            false
        );
        $this->sourceArgumentsReaderMock = $this->getMock(
            \Magento\Framework\Code\Reader\SourceArgumentsReader::class,
            [],
            [],
            '',
            false
        );
        $this->model = new \Magento\Framework\Code\Validator\ConstructorArgumentTypes(
            $this->argumentsReaderMock,
            $this->sourceArgumentsReaderMock
        );
    }

    public function testValidate()
    {
        $className = '\stdClass';
        $classMock = new \ReflectionClass($className);
        $this->argumentsReaderMock->expects($this->once())->method('getConstructorArguments')->with($classMock)
            ->willReturn([['name' => 'Name1', 'type' => '\Type'], ['name' => 'Name2', 'type' => '\Type2']]);
        $this->sourceArgumentsReaderMock->expects($this->once())->method('getConstructorArgumentTypes')
            ->with($classMock)->willReturn(['\Type', '\Type2']);
        $this->assertTrue($this->model->validate($className));
    }

    /**
     * @expectedException \Magento\Framework\Exception\ValidatorException
     * @expectedExceptionMessage Invalid constructor argument(s) in \stdClass
     */
    public function testValidateWithException()
    {
        $className = '\stdClass';
        $classMock = new \ReflectionClass($className);
        $this->argumentsReaderMock->expects($this->once())->method('getConstructorArguments')->with($classMock)
            ->willReturn([['name' => 'Name1', 'type' => '\FAIL']]);
        $this->sourceArgumentsReaderMock->expects($this->once())->method('getConstructorArgumentTypes')
            ->with($classMock)->willReturn(['\Type', '\Fail']);
        $this->assertTrue($this->model->validate($className));
    }
}
