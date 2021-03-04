<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Code\Test\Unit\Validator;

class ConstructorArgumentTypesTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $argumentsReaderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $sourceArgumentsReaderMock;

    /**
     * @var \Magento\Framework\Code\Validator\ConstructorArgumentTypes
     */
    protected $model;

    protected function setUp(): void
    {
        $this->argumentsReaderMock = $this->createMock(\Magento\Framework\Code\Reader\ArgumentsReader::class);
        $this->sourceArgumentsReaderMock =
            $this->createMock(\Magento\Framework\Code\Reader\SourceArgumentsReader::class);
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
     */
    public function testValidateWithException()
    {
        $this->expectException(\Magento\Framework\Exception\ValidatorException::class);
        $this->expectExceptionMessage('Invalid constructor argument(s) in \\stdClass');

        $className = '\stdClass';
        $classMock = new \ReflectionClass($className);
        $this->argumentsReaderMock->expects($this->once())->method('getConstructorArguments')->with($classMock)
            ->willReturn([['name' => 'Name1', 'type' => '\FAIL']]);
        $this->sourceArgumentsReaderMock->expects($this->once())->method('getConstructorArgumentTypes')
            ->with($classMock)->willReturn(['\Type', '\Fail']);
        $this->assertTrue($this->model->validate($className));
    }
}
