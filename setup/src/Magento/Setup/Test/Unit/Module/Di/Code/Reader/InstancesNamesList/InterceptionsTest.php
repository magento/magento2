<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\Di\Code\Reader\InstancesNamesList;

use Magento\Setup\Module\Di\Compiler\Log\Log;

/**
 * Class InterceptionsTest
 *
 * @package Magento\Setup\Module\Di\Code\Reader\Decorator
 */
class InterceptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Module\Di\Code\Reader\ClassesScanner | \PHPUnit_Framework_MockObject_MockObject
     */
    private $classesScanner;

    /**
     * @var \Magento\Framework\Code\Reader\ClassReader | \PHPUnit_Framework_MockObject_MockObject
     */
    private $classReaderMock;

    /**
     * @var \Magento\Setup\Module\Di\Code\Reader\Decorator\Directory
     */
    private $model;

    /**
     * @var \Magento\Framework\Code\Validator | \PHPUnit_Framework_MockObject_MockObject
     */
    private $validatorMock;

    /**
     * @var \Magento\Setup\Module\Di\Compiler\Log\Log | \PHPUnit_Framework_MockObject_MockObject
     */
    private $logMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->logMock = $this->getMockBuilder(\Magento\Setup\Module\Di\Compiler\Log\Log::class)
            ->disableOriginalConstructor()
            ->setMethods(['add', 'report'])
            ->getMock();

        $this->classesScanner = $this->getMockBuilder(\Magento\Setup\Module\Di\Code\Reader\ClassesScanner::class)
            ->disableOriginalConstructor()
            ->setMethods(['getList'])
            ->getMock();

        $this->classReaderMock = $this->getMockBuilder(\Magento\Framework\Code\Reader\ClassReader::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParents'])
            ->getMock();

        $this->validatorMock = $this->getMockBuilder(\Magento\Framework\Code\Validator::class)
            ->disableOriginalConstructor()
            ->setMethods(['validate', 'add'])
            ->getMock();

        $this->model = new \Magento\Setup\Module\Di\Code\Reader\Decorator\Interceptions(
            $this->classesScanner,
            $this->classReaderMock,
            $this->validatorMock,
            new \Magento\Framework\Code\Validator\ConstructorIntegrity(),
            new \Magento\Framework\Code\Validator\ContextAggregation(),
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
            ->will(
                $this->throwException($exception)
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
    public function getListExceptionDataProvider()
    {
        return [
            [new \Magento\Framework\Exception\ValidatorException(new \Magento\Framework\Phrase('Not Valid!'))],
            [new \ReflectionException('Not Valid!')]
        ];
    }
}
