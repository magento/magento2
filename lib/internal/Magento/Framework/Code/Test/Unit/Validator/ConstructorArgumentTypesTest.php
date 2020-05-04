<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Code\Test\Unit\Validator;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\Code\Validator\ConstructorArgumentTypes;
use Magento\Framework\Code\Reader\ArgumentsReader;
use Magento\Framework\Code\Reader\SourceArgumentsReader;

class ConstructorArgumentTypesTest extends TestCase
{

    /**
     * @var MockObject
     */
    protected $argumentsReaderMock;

    /**
     * @var MockObject
     */
    protected $sourceArgumentsReaderMock;

    /**
     * @var ConstructorArgumentTypes
     */
    protected $model;

    protected function setUp(): void
    {
        $this->argumentsReaderMock = $this->createMock(ArgumentsReader::class);
        $this->sourceArgumentsReaderMock =
            $this->createMock(SourceArgumentsReader::class);
        $this->model = new ConstructorArgumentTypes(
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

    public function testValidateWithException()
    {
        $this->expectException('Magento\Framework\Exception\ValidatorException');
        $this->expectExceptionMessage('Invalid constructor argument(s) in \stdClass');
        $className = '\stdClass';
        $classMock = new \ReflectionClass($className);
        $this->argumentsReaderMock->expects($this->once())->method('getConstructorArguments')->with($classMock)
            ->willReturn([['name' => 'Name1', 'type' => '\FAIL']]);
        $this->sourceArgumentsReaderMock->expects($this->once())->method('getConstructorArgumentTypes')
            ->with($classMock)->willReturn(['\Type', '\Fail']);
        $this->assertTrue($this->model->validate($className));
    }
}
