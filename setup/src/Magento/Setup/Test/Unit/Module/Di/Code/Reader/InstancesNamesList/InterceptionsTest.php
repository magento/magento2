<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\Code\Reader\InstancesNamesList;

use Magento\Framework\Code\Reader\ClassReader;
use Magento\Framework\Code\Validator;
use Magento\Framework\Code\Validator\ConstructorIntegrity;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Phrase;
use Magento\Setup\Module\Di\Code\Reader\ClassesScanner;
use Magento\Setup\Module\Di\Code\Reader\Decorator\Directory;
use Magento\Setup\Module\Di\Code\Reader\Decorator\Interceptions;
use Magento\Setup\Module\Di\Compiler\Log\Log;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InterceptionsTest extends TestCase
{
    /**
     * @var ClassesScanner|MockObject
     */
    private $classesScanner;

    /**
     * @var ClassReader|MockObject
     */
    private $classReaderMock;

    /**
     * @var Directory
     */
    private $model;

    /**
     * @var Validator|MockObject
     */
    private $validatorMock;

    /**
     * @var Log|MockObject
     */
    private $logMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->logMock = $this->getMockBuilder(Log::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['add', 'report'])
            ->getMock();

        $this->classesScanner = $this->getMockBuilder(ClassesScanner::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getList'])
            ->getMock();

        $this->classReaderMock = $this->getMockBuilder(ClassReader::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getParents'])
            ->getMock();

        $this->validatorMock = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['validate', 'add'])
            ->getMock();

        $this->model = new Interceptions(
            $this->classesScanner,
            $this->classReaderMock,
            $this->validatorMock,
            new ConstructorIntegrity(),
            $this->logMock
        );
    }

    public function testGetList()
    {
        $path = '/tmp/test';

        $classes = ['NameSpace1\ClassName1', 'NameSpace1\ClassName2'];

        $this->classesScanner->expects($this->once())
            ->method('getList')
            ->with($path)
            ->willReturn($classes);

        $this->logMock->expects($this->never())
            ->method('add');

        $this->logMock->expects($this->once())->method('report');

        $this->validatorMock->expects($this->exactly(count($classes)))
            ->method('validate');

        $result = $this->model->getList($path);

        $this->assertEquals($result, $classes);
    }

    public function testGetListNoValidation()
    {
        $path = '/generated/code';

        $classes = ['NameSpace1\ClassName1', 'NameSpace1\ClassName2'];

        $this->classesScanner->expects($this->once())
            ->method('getList')
            ->with($path)
            ->willReturn($classes);

        $this->logMock->expects($this->never())
            ->method('add');

        $this->validatorMock->expects($this->never())
            ->method('validate');

        $this->logMock->expects($this->once())->method('report');

        $result = $this->model->getList($path);

        $this->assertEquals($result, $classes);
    }

    /**
     * @dataProvider getListExceptionDataProvider
     *
     * @param $exception
     */
    public function testGetListException(\Exception $exception)
    {
        $path = '/tmp/test';

        $classes = ['NameSpace1\ClassName1'];

        $this->classesScanner->expects($this->once())
            ->method('getList')
            ->with($path)
            ->willReturn($classes);

        $this->logMock->expects($this->once())
            ->method('add')
            ->with(Log::COMPILATION_ERROR, $classes[0], $exception->getMessage());

        $this->validatorMock->expects($this->exactly(count($classes)))
            ->method('validate')
            ->willThrowException(
                $exception
            );

        $this->logMock->expects($this->once())->method('report');

        $result = $this->model->getList($path);

        $this->assertEquals($result, []);
    }

    /**
     * DataProvider for test testGetListException
     *
     * @return array
     */
    public static function getListExceptionDataProvider()
    {
        return [
            [new ValidatorException(new Phrase('Not Valid!'))],
            [new \ReflectionException('Not Valid!')]
        ];
    }
}
